<?php

namespace App\Services;

use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Hash;

class AdminService
{
    protected $adminRepository;

    public function __construct(AdminRepository $adminRepository)
    {
        $this->adminRepository = $adminRepository;
    }

    public function getAll()
    {
        return $this->adminRepository->getAll();
    }

    public function findById($id)
    {
        return $this->adminRepository->findById($id);
    }

    public function create(array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->create($data);
    }

    public function update($id, array $data)
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->update($id, $data);
    }

    public function delete($id)
    {
        return $this->adminRepository->delete($id);
    }
}
