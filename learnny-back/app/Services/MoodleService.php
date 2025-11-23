<?php

namespace App\Services;

use App\Models\User;
use App\Models\Professor;
use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Serviço de Integração com Moodle
 * 
 * Gerencia a provisão de contas e cursos no Moodle via API REST.
 * Implementa compensação (rollback manual) para manter consistência
 * entre Learnny e Moodle em caso de falha.
 */
class MoodleService
{
    protected $moodleUrl;
    protected $token;
    protected $restEndpoint;

    public function __construct()
    {
        $this->moodleUrl = config('services.moodle.url');
        $this->token = config('services.moodle.token');
        $this->restEndpoint = "{$this->moodleUrl}/webservice/rest/server.php";
    }

    /**
     * Provisiona uma conta de aluno no Moodle.
     * 
     * Cria um novo usuário no Moodle com os dados do aluno.
     * Se bem-sucedido, armazena o ID do Moodle no registro local.
     * Se falhar, registra o erro mas não bloqueia o fluxo.
     * 
     * @param User $user Aluno a provisionar
     * @param string $plainTextPassword Senha em texto plano
     * @return void
     */
    public function provisionUser(User $user, string $plainTextPassword): void
    {
        // Separa nome e sobrenome
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'Aluno'; 

        $usersToCreate = [[
            'username' => $user->username,
            'password' => $plainTextPassword,
            'firstname' => $firstName,
            'lastname' => $lastName,
            'email' => $user->email,
            'auth' => 'manual',
            'lang' => 'pt_br',
        ]];

        // Faz requisição para criar usuário no Moodle
        $response = Http::asForm()->post($this->restEndpoint, [
            'wstoken' => $this->token,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => $usersToCreate,
        ]);

        // Verifica sucesso
        if ($response->successful() && !$response->json('exception')) {
            $moodleUser = $response->json()[0];
            $user->moodle_id = $moodleUser['id'];
            $user->save();
            Log::info('Aluno provisionado no Moodle.', ['user_id' => $user->id]);
        } else {
            Log::error('Falha ao provisionar ALUNO no Moodle.', ['response' => $response->body()]);
        }
    }

    /**
     * Provisiona uma conta de professor no Moodle com lógica de compensação.
     * 
     * Processo transacional que:
     * 1. Cria conta do professor no Moodle
     * 2. Cria cursos para cada matéria
     * 3. Inscreve o professor como docente em cada curso
     * 
     * Se qualquer etapa falhar:
     * - Deleta os cursos criados
     * - Deleta a conta do professor
     * - Retorna false para que o banco local reverta a transação
     * 
     * @param Professor $professor Professor a provisionar
     * @param string $plainTextPassword Senha em texto plano
     * @param array $learnnySubjectIds IDs das matérias do professor no Learnny
     * @return bool True se bem-sucedido, false se houve erro
     */
    public function provisionTeacher(Professor $professor, string $plainTextPassword, array $learnnySubjectIds = []): bool
    {
        $moodleUserId = null;
        $createdCourseIds = [];

        try {
            // ----- PASSO 1: CRIAR A CONTA DO PROFESSOR -----
            
            $nameParts = explode(' ', $professor->name, 2);
            $firstName = $nameParts[0];
            $lastName = $nameParts[1] ?? 'Professor';

            $usersToCreate = [[
                'username' => $professor->username,
                'password' => $plainTextPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $professor->email,
                'auth' => 'manual',
                'lang' => 'pt_br',
            ]];

            $responseCreate = Http::asForm()->post($this->restEndpoint, [
                'wstoken' => $this->token,
                'wsfunction' => 'core_user_create_users',
                'moodlewsrestformat' => 'json',
                'users' => $usersToCreate,
            ]);

            // Se falhar na criação do usuário, lança exceção
            if ($responseCreate->failed() || $responseCreate->json('exception')) {
                throw new \Exception('Falha ao criar usuário: ' . $responseCreate->body());
            }

            $moodleUserId = $responseCreate->json()[0]['id'];

            // ----- PASSO 2: CRIAR CURSOS E INSCREVER PROFESSOR -----

            $defaultCategoryId = config('services.moodle.default_category_id');
            $teacherRoleId = config('services.moodle.teacher_role_id');

            // Itera sobre cada matéria do professor
            foreach ($learnnySubjectIds as $subjectId) {
                $subject = Subject::find($subjectId);
                if (!$subject) continue;

                // Cria nomes únicos para o curso
                $courseFullName = "{$subject->name} - {$professor->name}";
                $courseShortName = "learnny_{$subject->id}_{$professor->id}_" . time();

                // A. Criar o Curso
                $responseCourse = Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_course_create_courses',
                    'moodlewsrestformat' => 'json',
                    'courses' => [[
                        'fullname' => $courseFullName,
                        'shortname' => $courseShortName,
                        'categoryid' => $defaultCategoryId,
                        'visible' => 1,
                    ]],
                ]);

                if ($responseCourse->failed() || $responseCourse->json('exception')) {
                    throw new \Exception('Falha ao criar curso: ' . $responseCourse->body());
                }
                
                $moodleCourseId = $responseCourse->json()[0]['id'];
                $createdCourseIds[] = $moodleCourseId; // Armazena para limpeza se necessário

                // B. Inscrever professor como docente no curso
                $responseEnrol = Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'enrol_manual_enrol_users',
                    'moodlewsrestformat' => 'json',
                    'enrolments' => [[
                        'roleid' => $teacherRoleId,
                        'userid' => $moodleUserId,
                        'courseid' => $moodleCourseId,
                    ]],
                ]);

                if ($responseEnrol->failed() || $responseEnrol->json('exception')) {
                    throw new \Exception('Falha ao inscrever: ' . $responseEnrol->body());
                }
            }

            // SUCESSO: Salva o ID do Moodle no professor
            $professor->moodle_id = $moodleUserId;
            $professor->save();
            
            return true;

        } catch (\Exception $e) {
            // Erro capturado: iniciando limpeza (compensação)
            Log::error('Erro no provisionamento Moodle. Iniciando limpeza...', ['error' => $e->getMessage()]);

            // --- LÓGICA DE ROLLBACK MANUAL ---
            
            // 1. Deletar cursos criados (se houver)
            if (!empty($createdCourseIds)) {
                Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_course_delete_courses',
                    'moodlewsrestformat' => 'json',
                    'courseids' => $createdCourseIds,
                ]);
                Log::info('Cursos do Moodle revertidos/deletados.', ['ids' => $createdCourseIds]);
            }

            // 2. Deletar usuário criado (se houver)
            if ($moodleUserId) {
                Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_user_delete_users',
                    'moodlewsrestformat' => 'json',
                    'userids' => [$moodleUserId],
                ]);
                Log::info('Usuário do Moodle revertido/deletado.', ['id' => $moodleUserId]);
            }

            // Retorna false para acionar rollback no banco local
            return false;
        }
    }
}