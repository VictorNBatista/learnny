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
        return Admin::find($id);
    }

    public function create(array $data)
    {
        return Admin::create($data);
    }

    public function update($id, array $data)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return null;
        }

        $admin->update($data);
        return $admin;
    }

    public function delete($id)
    {
        $admin = Admin::find($id);
        if (!$admin) {
            return null;
        }

        $admin->delete();
        return $admin;
    }
}
