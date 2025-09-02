<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use App\Services\AdminSubjectService;
use Illuminate\Http\Request;

class AdminSubjectController extends Controller
{
    protected $adminSubjectService;

    public function __construct(AdminSubjectService $adminSubjectService)
    {
        $this->adminSubjectService = $adminSubjectService;
    }

    public function index()
    {
        return response()->json($this->adminSubjectService->listSubjects());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name|max:255',
        ]);

        $subject = $this->adminSubjectService->createSubject($request->only('name'));
        return response()->json($subject, 201);
    }

    public function show($id)
    {
        return response()->json($this->adminSubjectService->getSubject($id));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name,' . $id,
        ]);

        $subject = $this->adminSubjectService->updateSubject($id, $request->only('name'));
        return response()->json($subject);
    }

    public function destroy($id)
    {
        $this->adminSubjectService->deleteSubject($id);
        return response()->json(['message' => 'Matéria excluída com sucesso.']);
    }
}
