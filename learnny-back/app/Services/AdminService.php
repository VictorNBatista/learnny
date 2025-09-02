<?php

namespace App\Services;

use App\Repositories\AdminRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

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
        // $data['password'] = Hash::make($data['password']);
        return $this->adminRepository->create($data);
    }

    public function update($id, array $data)
    {
        $admin = $this->adminRepository->findById($id);

        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->adminRepository->update($admin, $data);
    }

    public function delete($id)
    {
        $admin = $this->adminRepository->findById($id);
        return $this->adminRepository->delete($admin);
    }
}
