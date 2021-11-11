<?php

namespace App\Http\Controllers;

use App\Perfil;
use App\Receta;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class PerfilController extends Controller
{

    public function __construct()
    {
        // los usuarios deben de estar autenticados para ver esta vista,
        // menos el metodo de show
        $this->middleware('auth',['except' => 'show']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Perfil  $perfil
     * @return \Illuminate\Http\Response
     */
    public function show(Perfil $perfil)
    {
        // recetas con paginacion
        $recetas = Receta::where('user_id',$perfil->user_id)->paginate(10);

        return view('perfiles.show', compact('perfil','recetas'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Perfil  $perfil
     * @return \Illuminate\Http\Response
     */
    public function edit(Perfil $perfil)
    {
        //
        $this->authorize('view',$perfil);

        return view('perfiles.edit',compact('perfil'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Perfil  $perfil
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Perfil $perfil)
    {

        // ejecutar el policy
        $this->authorize('update',$perfil);

        // validar
        $data = request()->validate([
            'nombre' => 'required',
            'url' => 'required',
            'biografia' => 'required'
        ]);

        // si se sube imagen
        if($request['imagen']){
            $ruta_imagen = $request['imagen']->store('upload-perfiles','public');
            $img = Image::make(public_path("storage/{$ruta_imagen}"))->fit(600,600);
            $img->save();

            // crear arreglo de la imagen
            $array_imagen = ['imagen' => $ruta_imagen];
        }

        // asignar nombre y url
        auth()->user()->url = $data['url'];
        auth()->user()->name = $data['nombre'];
        auth()->user()->save();

        // eliminar url y data
        unset($data['url']);
        unset($data['nombre']);

        // guardar informacion
        // asignar bio e imagen
        auth()->user()->perfil()->update( array_merge(
                $data,
                $array_imagen ?? []
            ));
        // redireccionar

        return redirect()->action('RecetaController@index');
    }

}
