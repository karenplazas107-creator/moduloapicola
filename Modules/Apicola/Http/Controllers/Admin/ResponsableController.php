<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Responsable;

class ResponsableController extends Controller
{
    public function index()
    {
        return response()->json(['success' => true, 'data' => Responsable::todos()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'min:3', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/u'],
        ], [
            'nombre.regex' => 'El responsable es inválido: no puede contener números, solo nombres reales de personas.',
        ]);

        $nombre = Responsable::registrar($validated['nombre']);

        return response()->json(['success' => true, 'nombre' => $nombre, 'data' => Responsable::todos()], 201);
    }
}
