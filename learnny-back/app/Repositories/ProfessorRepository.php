<?php 

namespace App\Repositories;

use App\Models\Professor;

class ProfessorRepository
{
    public function getAll()
    {
        return Professor::select('id', 'name', 'email', 'contact')
                        ->with('subjects')
                        ->withTrashed()
                        ->paginate(15);
    }

    public function findById($id)
    {
        return Professor::with('subjects')->find($id);
    }

    public function findByEmail($email)
    {
        return Professor::where('email', $email)->first();
    }

    public function create(array $data)
    {
        return Professor::create($data);
    }

    public function update(Professor $professor, array $data)
    {
        return $professor->update($data);
    }

    public function delete(Professor $professor)
    {
        return $professor->delete();
    }
}
