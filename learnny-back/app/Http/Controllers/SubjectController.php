<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index()
    {
        return Subject::all();
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:subjects,name|max:255',
        ]);

        $subject = Subject::create([
            'name' => $request->name,
        ]);

        return response()->json($subject, 201);
    }

    public function show($id)
    {
        $subject = Subject::findOrFail($id);
        return response()->json($subject);
    }

    public function update(Request $request, $id)
    {
        $subject = Subject::findOrFail($id);

        $request->validate([
            'name' => 'required|unique:subjects,name,' . $id,
        ]);

        $subject->update([
            'name' => $request->name,
        ]);

        return response()->json($subject);
    }

    public function destroy($id)
    {
        $subject = Subject::findOrFail($id);
        $subject->delete();

        return response()->json(['message' => 'Matéria excluída com sucesso.']);
    }
}