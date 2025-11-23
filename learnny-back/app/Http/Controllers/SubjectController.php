<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

/**
 * Controlador de Matérias (Públicas)
 * 
 * Fornece acesso público à lista de matérias disponíveis.
 */
class SubjectController extends Controller
{
    /**
     * Lista todas as matérias disponíveis.
     * 
     * @return \Illuminate\Database\Eloquent\Collection Coleção de matérias
     */
    public function index()
    {
        return Subject::all();
    }
}