<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecetaController extends Controller
{
    public function __invoke(){

        $recetas = ['Receta Pizza', 'Receta Hamburguesa','Receta Tacos'];
        $categorias = ['Comida Mexicana','Comida Argentina','Postres'];

        return view('recetas.index')
            ->with('recetas',$recetas)
            ->with('categorias',$categorias);

        //return view('recetas.index')->compact('recetas','categorias');
    }
}
