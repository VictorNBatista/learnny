<?php

namespace App\Services;

use App\Repositories\UserRepository;

class UserService
{
    protected $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function listUsers()
    {
        return $this->userRepository->getAll();
    }

    public function createUser(array $data)
    {
        return $this->userRepository->create($data);
    }

    public function findUserById($id)
    {
        return $this->userRepository->findById($id);
    }

    public function updateUser($id, array $data)
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return null;
        }

        $this->userRepository->update($user, $data);

        return $user;
    }

    public function deleteUser($id)
    {
        $user = $this->userRepository->findById($id);

        if (!$user) {
            return null;
        }

        $this->userRepository->delete($user);

        return $user;
    }
}
