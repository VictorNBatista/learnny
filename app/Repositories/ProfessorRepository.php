<?php

namespace App\Repositories;

use App\Models\Professor;

class ProfessorRepository
{
    public function getAll()
    {
        return Professor::select('id', 'name', 'photo_url', 'contact', 'biography', 'subject', 'price')
                    ->paginate(15);
    }

    public function findById($id)
    {
        return Professor::find($id);
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
