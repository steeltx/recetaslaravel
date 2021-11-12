@extends('layouts.app')

@section('content')

    <div class="container">
        <h2 class="titulo-categoria text-uppercase mt-5 mb-4">
            Resultados búsqueda : {{$busqueda}}
        </h2>
        <div class="row">
            @if(count($recetas) > 0)
                @foreach ($recetas as $receta)
                    @include('ui.receta')
                @endforeach
            @else
                <p class="text-primary font-weight-bold">No hay resultados para la búsqueda</p>
            @endif
        </div>
        <div class="col-12 mt-4 d-flex">
            <span class="badge badge-dark">Total de registros : {{$recetas->total()}}</span>
        </div>
        <div class="d-flex justify-content-center mt-5">
            {{$recetas->links() }}
        </div>
    </div>

@endsection
