<?php

namespace App\Http\Controllers;

use App\CategoriaReceta;
use App\Receta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class RecetaController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth',['except'=>['show','search']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //$recetas = Auth::user()->recetas;

        $usuario = Auth::user();

        //$meGusta = auth()->user()->meGusta;

        // recetas con paginacion
        $recetas = Receta::where('user_id',$usuario->id)->paginate(10);

        return view('recetas.index')
            ->with('recetas',$recetas)
            ->with('usuario',$usuario);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        // obtener categorias sin modelo
        // $categorias = DB::table('categoria_recetas')->get()->pluck('nombre','id');

        // usando modelos
        $categorias = CategoriaReceta::all(['id','nombre']);

        return view('recetas.create')->with('categorias',$categorias);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        //validaciones de los datos de formulario
        $data = $request->validate([
            'titulo' => 'required|min:6',
            'preparacion' => 'required',
            'ingredientes' => 'required',
            'imagen' => 'required|image',
            'categoria' => 'required',
        ]);

        //obtener la ruta de la imagen
        $ruta_imagen = $request['imagen']->store('upload-recetas','public');

        //resize de la imagen
        $img = Image::make(public_path("storage/{$ruta_imagen}"))->fit(1000,550);
        $img->save();

        // almacen en BD sin modelo
        // DB::table('recetas')->insert([
        //     'titulo' => $data['titulo'],
        //     'preparacion' => $data['preparacion'],
        //     'ingredientes' => $data['ingredientes'],
        //     'imagen' => $ruta_imagen,
        //     'user_id' => Auth::user()->id, // obtener el usuario que inicio sesion
        //     'categoria_id'=> $data['categoria']
        // ]);

        // guardar con modelo
        auth()->user()->recetas()->create([
            'titulo' => $data['titulo'],
            'preparacion' => $data['preparacion'],
            'ingredientes' => $data['ingredientes'],
            'imagen' => $ruta_imagen,
            'categoria_id'=> $data['categoria']
        ]);

        //redireccionar
        return redirect()->action('RecetaController@index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Receta  $receta
     * @return \Illuminate\Http\Response
     */
    public function show(Receta $receta)
    {
        // obtener si el usuario dio like y esta autenticado
        $like = (auth()->user()) ? auth()->user()->meGusta->contains($receta->id) : false;

        // cantidad de likes
        $likes = $receta->likes->count();

        return view('recetas.show',compact('receta','like','likes'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Receta  $receta
     * @return \Illuminate\Http\Response
     */
    public function edit(Receta $receta)
    {
        // revisar el policy
        $this->authorize('view',$receta);

        // obtener las categorias y pasarlas a la vista
        $categorias = CategoriaReceta::all(['id','nombre']);
        return view('recetas.edit', compact('categorias','receta'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Receta  $receta
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Receta $receta)
    {

        // revisar que el policy sea cumplido
        $this->authorize('update',$receta);

        // realizar la validacion
        $data = $request->validate([
            'titulo' => 'required|min:6',
            'preparacion' => 'required',
            'ingredientes' => 'required',
            'categoria' => 'required',
        ]);

        // asignar los valores
        $receta->titulo = $data['titulo'];
        $receta->preparacion = $data['preparacion'];
        $receta->ingredientes = $data['ingredientes'];
        $receta->categoria_id = $data['categoria'];

        // cuando se suba una nueva imagen
        if(request('imagen')){
            $ruta_imagen = $request['imagen']->store('upload-recetas','public');
            $img = Image::make(public_path("storage/{$ruta_imagen}"))->fit(1000,550);
            $img->save();
            // asignar al objeto
            $receta->imagen = $ruta_imagen;
        }

        $receta->save();

        //enviar a la lista de registros
        return redirect()->action('RecetaController@index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Receta  $receta
     * @return \Illuminate\Http\Response
     */
    public function destroy(Receta $receta)
    {
        // usar el policy
        $this->authorize('delete',$receta);

        // eliminar la receta
        $receta->delete();
        return redirect()->action('RecetaController@index');
    }

    public function search(Request $request){

        //$busqueda = $request['buscar'];
        $busqueda = $request->get('buscar');


        $recetas = Receta::where('titulo','like', '%'.$busqueda.'%')->paginate(1);
        $recetas->appends(['buscar' => $busqueda]);

        return view('busqueda.show',compact('recetas','busqueda'));
    }
}
