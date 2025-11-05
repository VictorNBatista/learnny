<?php

namespace App\Services;

use App\Models\User;
use App\Models\Professor;
use App\Models\Subject;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoodleService
{
    // 2. REFATORAÇÃO: Mover config para o construtor
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
     * Provisiona um ALUNO (seu método original, ajustado para usar o construtor)
     */
    public function provisionUser(User $user, string $plainTextPassword): void
    {
        $nameParts = explode(' ', $user->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'Aluno'; // Sobrenome padrão para aluno

        $usersToCreate = [
            [
                'username' => $user->username,
                'password' => $plainTextPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $user->email,
                'auth' => 'manual',
                'lang' => 'pt_br',
            ]
        ];

        $response = Http::asForm()->post($this->restEndpoint, [
            'wstoken' => $this->token,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => $usersToCreate,
        ]);

        if ($response->successful() && !$response->json('exception')) {
            $moodleUser = $response->json()[0];
            $user->moodle_id = $moodleUser['id'];
            $user->save();
            Log::info('Aluno provisionado no Moodle com sucesso.', ['user_id' => $user->id, 'moodle_id' => $moodleUser['id']]);
        } else {
            Log::error('Falha ao provisionar ALUNO no Moodle.', ['user_id' => $user->id, 'response' => $response->body()]);
        }
    }

    /**
     * 3. MÉTODO ATUALIZADO: Provisiona um PROFESSOR, CRIA CURSOS e o INSCREVE.
     *
     * @param Professor $professor Seu model de Professor
     * @param string $plainTextPassword A senha em texto plano
     * @param array $learnnySubjectIds Array de IDs das matérias do Learnny (ex: [1, 2])
     * @return bool Retorna true em sucesso, false em falha
     */
    public function provisionTeacher(Professor $professor, string $plainTextPassword, array $learnnySubjectIds = []): bool
    {
        // ----- PASSO 1: CRIAR O USUÁRIO (Uma vez) -----
        
        $nameParts = explode(' ', $professor->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'Professor';

        $usersToCreate = [
            [
                'username' => $professor->username,
                'password' => $plainTextPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $professor->email,
                'auth' => 'manual',
                'lang' => 'pt_br',
            ]
        ];

        $responseCreate = Http::asForm()->post($this->restEndpoint, [
            'wstoken' => $this->token,
            'wsfunction' => 'core_user_create_users',
            'moodlewsrestformat' => 'json',
            'users' => $usersToCreate,
        ]);

        if ($responseCreate->failed() || $responseCreate->json('exception')) {
            Log::error('Falha ao CRIAR professor no Moodle.', [
                'teacher_id' => $professor->id,
                'response' => $responseCreate->body()
            ]);
            return false;
        }

        $moodleUser = $responseCreate->json()[0];
        $moodleUserId = $moodleUser['id'];

        
        // ----- PASSO 2: LOOP PARA CRIAR CURSOS E INSCREVER -----

        // Pega os IDs dos config
        $defaultCategoryId = config('services.moodle.default_category_id');
        $teacherRoleId = config('services.moodle.teacher_role_id'); // Ex: ID 3 (Professor)

        try {
            foreach ($learnnySubjectIds as $subjectId) {
                // A. Buscar o nome da matéria no banco do Learnny
                $subject = Subject::find($subjectId);
                if (!$subject) {
                    Log::warning('Matéria (Subject) não encontrada no Learnny, pulando criação de curso', ['subject_id' => $subjectId]);
                    continue; // Pula para a próxima matéria
                }

                // B. Criar o curso no Moodle
                $courseFullName = "{$subject->name} - {$professor->name}";
                // O nome curto (shortname) precisa ser único
                $courseShortName = "learnny_{$subject->id}_{$professor->id}_{$professor->username}";

                $responseCourse = Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'core_course_create_courses',
                    'moodlewsrestformat' => 'json',
                    'courses' => [[
                        'fullname' => $courseFullName,
                        'shortname' => $courseShortName,
                        'categoryid' => $defaultCategoryId,
                        'visible' => 1, // 1 = Visível, 0 = Oculto
                    ]],
                ]);

                if ($responseCourse->failed() || !empty($responseCourse->json('exception'))) {
                    Log::error('Falha ao CRIAR CURSO no Moodle.', [
                        'teacher_id' => $professor->id, 'moodle_id' => $moodleUserId, 'subject_id' => $subjectId,
                        'response' => $responseCourse->body()
                    ]);
                    throw new \Exception('Falha ao criar curso no Moodle.'); // Força o rollback
                }
                
                $moodleCourseId = $responseCourse->json()[0]['id'];

                // C. Inscrever o professor no curso que acabamos de criar
                $responseEnrol = Http::asForm()->post($this->restEndpoint, [
                    'wstoken' => $this->token,
                    'wsfunction' => 'enrol_manual_enrol_users',
                    'moodlewsrestformat' => 'json',
                    'enrolments' => [[
                        'roleid' => $teacherRoleId, // O ID do papel "Professor" (ex: 3)
                        'userid' => $moodleUserId,
                        'courseid' => $moodleCourseId,
                    ]],
                ]);

                if ($responseEnrol->failed() || !empty($responseEnrol->json('exception'))) {
                    Log::error('Falha ao INSCREVER PROFESSOR no curso.', [
                        'teacher_id' => $professor->id, 'moodle_id' => $moodleUserId, 'course_id' => $moodleCourseId,
                        'response' => $responseEnrol->body()
                    ]);
                    throw new \Exception('Falha ao inscrever professor no curso.'); // Força o rollback
                }
            } // Fim do foreach

        } catch (\Exception $e) {
            // Se qualquer etapa do loop falhar, o catch() pega
            // e retorna false, o que vai acionar o rollback no ProfessorService
            Log::error('Erro no loop de criação de cursos Moodle: ' . $e->getMessage());
            return false;
        }

        // ----- PASSO 3: SUCESSO TOTAL -----
        $professor->moodle_id = $moodleUserId;
        $professor->save();
        
        Log::info('Professor provisionado e inscrito nos cursos do Moodle com sucesso.', [
            'teacher_id' => $professor->id,
            'moodle_id' => $moodleUserId
        ]);

        return true;
    }
}