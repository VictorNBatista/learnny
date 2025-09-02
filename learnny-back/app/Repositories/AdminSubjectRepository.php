<?php

namespace App\Repositories;

use App\Models\Subject;

class AdminSubjectRepository
{
    public function getAll()
    {
        return Subject::all();
    }

    public function findById($id)
    {
        return Subject::findOrFail($id);
    }

    public function create(array $data)
    {
        return Subject::create($data);
    }

    public function update(Subject $subject, array $data)
    {
        $subject->update($data);
        return $subject;
    }

    public function delete(Subject $subject)
    {
        return $subject->delete();
    }
}
