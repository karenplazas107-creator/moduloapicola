<?php

namespace Modules\SISGEDI\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Documento extends Model
{
    use SoftDeletes;

    protected $table = 'documentos';

    protected $fillable = [
        'codigo',
        'nombre',
        'descripcion',
        'estado'
    ];

    protected $dates = ['deleted_at'];
}