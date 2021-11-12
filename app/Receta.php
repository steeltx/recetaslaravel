<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receta extends Model
{

    // campos que se agregaran
    protected $fillable = [
        'titulo', 'preparacion', 'ingredientes','imagen','categoria_id',
    ];

    // obtiene la categoria de la receta por fk
    public function categoria(){
        return $this->belongsTo(CategoriaReceta::class);
    }

    // obtiene la informacion del usuario por fk
    public function autor(){
        return $this->belongsTo(User::class,'user_id');
    }

    // likes que ha recibido una receta
    public function likes(){
        return $this->belongsToMany(User::class,'likes_receta');
    }
}
