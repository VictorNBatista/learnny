<?php

namespace App\Services;

use App\Models\User;
use App\Models\Professor;
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
     * 3. NOVO MÉTODO: Provisiona um PROFESSOR com permissão de criar cursos
     *
     * @param Teacher $teacher Seu model de Professor
     * @param string $plainTextPassword A senha em texto plano
     * @return bool Retorna true em sucesso, false em falha
     */
    public function provisionTeacher(Professor $teacher, string $plainTextPassword): bool
    {
        // ----- PASSO 1: CRIAR O USUÁRIO (Igual ao provisionUser) -----
        
        $nameParts = explode(' ', $teacher->name, 2);
        $firstName = $nameParts[0];
        $lastName = $nameParts[1] ?? 'Professor'; // Sobrenome padrão para professor

        $usersToCreate = [
            [
                'username' => $teacher->username, // Assumindo que seu model Teacher tem 'username'
                'password' => $plainTextPassword,
                'firstname' => $firstName,
                'lastname' => $lastName,
                'email' => $teacher->email,
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

        // Se a criação falhar, logue o erro e pare
        if ($responseCreate->failed() || $responseCreate->json('exception')) {
            Log::error('Falha ao CRIAR professor no Moodle.', [
                'teacher_id' => $teacher->id,
                'response' => $responseCreate->body()
            ]);
            return false;
        }

        $moodleUser = $responseCreate->json()[0];
        $moodleUserId = $moodleUser['id'];

        // ----- PASSO 2: ATRIBUIR A PERMISSÃO DE CRIADOR DE CURSO -----

        // !! IMPORTANTE: Confirme este ID no seu Moodle!
        // Vá em: Administração do site > Usuários > Permissões > Definir papéis
        // Procure "Criador de curso" (Course creator). O ID padrão é 3.
        $courseCreatorRoleId = config('services.moodle.professor_role_id');

        // O 'contextid' para permissões globais (nível do sistema) é sempre 1.
        $systemContextId = 1;

        $responseAssign = Http::asForm()->post($this->restEndpoint, [
            'wstoken' => $this->token,
            'wsfunction' => 'core_role_assign_roles',
            'moodlewsrestformat' => 'json',
            'assignments' => [[
                'roleid' => $courseCreatorRoleId,
                'userid' => $moodleUserId,
                'contextid' => $systemContextId
            ]],
        ]);

        // Se a atribuição de permissão falhar, logue o erro
        if ($responseAssign->failed() || $responseAssign->json('exception')) {
            Log::error('Falha ao ATRIBUIR PERMISSÃO ao professor no Moodle.', [
                'teacher_id' => $teacher->id,
                'moodle_id' => $moodleUserId,
                'response' => $responseAssign->body()
            ]);
            // O usuário foi criado, mas está sem permissão!
            return false;
        }

        // ----- PASSO 3: SUCESSO TOTAL -----
        
        // Salva o ID do Moodle no seu banco local
        $teacher->moodle_id = $moodleUserId; // Assumindo que seu model Teacher tem 'moodle_id'
        $teacher->save();
        
        Log::info('Professor provisionado no Moodle com sucesso (com permissão).', [
            'teacher_id' => $teacher->id,
            'moodle_id' => $moodleUserId
        ]);

        return true;
    }
}