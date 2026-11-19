<?php

namespace Modules\Apicola\Http\Controllers\Aprendiz;

use Illuminate\Http\Request;
use Modules\Apicola\Http\Controllers\Admin\ProduccionController as AdminProduccionController;

/**
 * Producción para el rol de aprendiz: puede consultar y registrar cosechas,
 * pero no editar ni desactivar registros (eso es del administrador).
 */
class ProduccionController extends AdminProduccionController
{
    public function index(Request $request)
    {
        return $this->render($request->get('colmena'), true);
    }

    public function byColmena($code)
    {
        return $this->render($code, true);
    }

    public function store(Request $request)
    {
        // El aprendiz firma con su propio nombre
        $user = auth()->user();
        $nombre = $user?->person
            ? trim($user->person->first_name . ' ' . $user->person->first_last_name)
            : ($user->nickname ?? 'Aprendiz');
        $request->merge(['responsable' => $nombre]);

        return parent::store($request);
    }

    /**
     * El aprendiz no agrega su nombre a la lista de responsables compartida.
     */
    protected function nombreResponsable(string $nombre): string
    {
        return trim($nombre);
    }

    public function update(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'El rol de aprendiz no puede editar registros de producción.'], 403);
    }

    public function toggleStatus(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'El rol de aprendiz no puede cambiar el estado de la producción.'], 403);
    }

    public function destroy(Request $request, $id)
    {
        return response()->json(['success' => false, 'message' => 'El rol de aprendiz no puede desactivar registros de producción.'], 403);
    }
}
