<?php

namespace App\Repositories;

use App\Models\Professor;

class ProfessorRepository
{
    protected $model;

    public function __construct(Professor $model)
    {
        $this->model = $model; // Atribui o modelo Professor à propriedade $model
    }

    public function getAll()
    {
        return Professor::with('subjects:id,name')->get(); // Retorna todos os professores com os assuntos relacionados
    }

    public function findById($id)
    {
        return Professor::find($id);
    }

    public function create(array $data)
    {
        return Professor::create($data);
    }

    public function update($id, array $data)
    {
        $professor = $this->model->findOrFail($id); // Encontre o professor pelo ID
        $professor->update($data); // Atualize os dados
        return $professor;
    }

    public function delete($id)
    {
        $professor = $this->model->findOrFail($id);
        $professor->delete();
        return $professor;
    }
}
