<?php

namespace App\Services;

use App\Repositories\AdminSubjectRepository;
use Illuminate\Validation\ValidationException;

class AdminSubjectService
{
    protected $repository;

    public function __construct(AdminSubjectRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listSubjects()
    {
        return $this->repository->getAll();
    }

    public function createSubject(array $data)
    {
        if (empty($data['name'])) {
            throw ValidationException::withMessages([
                'name' => 'O campo nome é obrigatório.'
            ]);
        }

        return $this->repository->create($data);
    }

    public function getSubject($id)
    {
        return $this->repository->findById($id);
    }

    public function updateSubject($id, array $data)
    {
        $subject = $this->repository->findById($id);
        return $this->repository->update($subject, $data);
    }

    public function deleteSubject($id)
    {
        $subject = $this->repository->findById($id);
        return $this->repository->delete($subject);
    }
}
