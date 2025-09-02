<?php

namespace App\Repositories;

use App\Models\Admin;

class AdminRepository
{
    public function getAll()
    {
        return Admin::all();
    }

    public function findById($id)
    {
        return Admin::findOrFail($id);
    }

    public function create(array $data)
    {
        return Admin::create($data);
    }

    public function update(Admin $admin, array $data)
    {
        $admin->update($data);
        return $admin;
    }

    public function delete(Admin $admin)
    {
        return $admin->delete();
    }
}
