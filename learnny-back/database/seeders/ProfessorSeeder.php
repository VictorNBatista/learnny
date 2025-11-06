<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Professor;
use App\Models\Subject;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProfessorSeeder extends Seeder
{
    public function run(): void
    {
        $subjectIds = Subject::pluck('id');
        if ($subjectIds->isEmpty()) {
            $this->command->warn('Nenhuma matéria (Subject) encontrada. Professores serão criados sem matérias.');
            // Continua a execução para criar os professores mesmo assim
        }

        // Professor 1 (Aprovado)
        $p1 = Professor::firstOrCreate(
            ['email' => 'professor@email.com'],
            [
                'name' => 'Teste Professor',
                'username' => 'professorteste',
                'password' => Hash::make('Professor@123'),
                'photo_url' => 'https://educacao.sme.prefeitura.sp.gov.br/wp-content/uploads/2024/11/Professora-e-estudante-a-sala-de-aula-1-825x470.png',
                'contact' => '11987654321',
                'biography' => 'Especialista em matemática avançada e preparação para vestibulares. Mais de 10 anos de experiência.',
                'price' => 150.00,
                'status' => 'approved', // Status aprovado para aparecer na busca
            ]
        );
        
        if ($subjectIds->isNotEmpty()) {
            $p1->subjects()->sync([1, 2]);
        }

        // Professor 2 (Aprovado)
        $p2 = Professor::firstOrCreate(
            ['email' => 'ana.bio@learnny.com'],
            [
                'name' => 'Ana Beatriz',
                'username' => 'anabreatiz',
                'password' => Hash::make('senha123'),
                'photo_url' => 'https://media.tutormundi.com/wp-content/uploads/2020/10/01194433/papel-do-professor-no-ensino-hibrido-min.png',
                'contact' => '21987654322',
                'biography' => 'Bióloga com mestrado em genética. Aulas dinâmicas de biologia e química para ensino médio.',
                'price' => 120.50,
                'status' => 'approved',
            ]
        );
        
        if ($subjectIds->isNotEmpty()) {
            $p2->subjects()->sync([5, 4]);
        }

        // Professor 3 (Aprovado)
        $p3 = Professor::firstOrCreate(
            ['email' => 'lucia.port@learnny.com'],
            [
                'name' => 'Lúcia Pereira',
                'username' => 'luciapereira',
                'password' => Hash::make('senha123'),
                'photo_url' => 'https://media.istockphoto.com/id/1495037929/photo/happy-elementary-school-teacher-giving-high-five-to-her-student-during-class-in-the-classroom.jpg?s=612x612&w=0&k=20&c=Gn5Kqzd58Tr4sCu-9LKbvQSszJ6b9VmyFB21FOCCO98=',
                'contact' => '31987654323',
                'biography' => 'Apaixonada por literatura e gramática. Foco total em redação e preparação para o ENEM.',
                'price' => 130.00,
                'status' => 'approved',
            ]
        );
        
        if ($subjectIds->isNotEmpty()) {
            $p3->subjects()->sync([2, 9]);
        }

        // Professor 4 (Pendente - para testes de aprovação no admin)
        $p4 = Professor::firstOrCreate(
            ['email' => 'rafael.fis@learnny.com'],
            [
                'name' => 'Rafael Gomes',
                'username' => 'rafaelgomes',
                'password' => Hash::make('senha123'),
                'photo_url' => 'https://media.istockphoto.com/id/2160473960/pt/foto/happy-satisfied-math-teacher-in-elementary-class.jpg?s=612x612&w=0&k=20&c=E3-r7jBoiYKOWdu1DcUJrpN_R9jG6AXBf0zbF_IndL8=',
                'contact' => '41987654324',
                'biography' => 'Físico com experiência em mecânica quântica e astronomia. Ajuda com física de nível superior.',
                'price' => 180.00,
                'status' => 'pending', // Status pendente para o admin aprovar
            ]
        );
        
        if ($subjectIds->isNotEmpty()) {
            $p4->subjects()->sync([3]);
        }
    }
}