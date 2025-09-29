<?php

namespace App\Repositories;

use App\Models\Professor;

class ProfessorRepository
{
    protected $model;

    public function __construct(Professor $model)
    {
        $this->model = $model;
    }

    public function getAll()
    {
        return $this->model
        ->with('subjects:id,name')
        ->where('status', 'approved') // filtra apenas professores aprovados
        ->get();
    }

    public function findById($id)
    {
        return $this->model->with('subjects:id,name')->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    public function update($id, array $data)
    {
        $professor = $this->model->findOrFail($id);
        $professor->update($data);
        return $professor;
    }

    public function delete($id)
    {
        $professor = $this->model->findOrFail($id);
        $professor->delete();
        return $professor;
    }

    public function getPendingProfessors()
    {
        return $this->model
        ->with('subjects:id,name')
        ->where('status', 'pending') // filtra apenas professores pendentes
        ->get();
    }

    public function updateStatus($id, $status)
    {
        $professor = $this->model->find($id);
        if (!$professor) {
            return null;
        }

        $professor->status = $status;
        $professor->save();

        return $professor;
    }
}
