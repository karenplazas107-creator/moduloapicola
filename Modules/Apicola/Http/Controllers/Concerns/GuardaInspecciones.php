<?php

namespace Modules\Apicola\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Lógica común para registrar inspecciones (panel del administrador y del aprendiz):
 * validación, comprobación de la colmena (existe, activa y en apiario activo) y guardado de enfermedades.
 */
trait GuardaInspecciones
{
    /**
     * Nombre del responsable que se guarda en la inspección. Los administradores lo agregan a la lista
     * compartida de responsables; el aprendiz puede sobrescribir este método.
     */
    protected function responsableTexto(Request $request): string
    {
        return \Modules\Apicola\Entities\Responsable::registrar($request->input('responsable'));
    }

    /**
     * Reglas de validación compartidas por crear y editar una inspección.
     */
    protected function validarInspeccion(Request $request): array
    {
        return $request->validate([
            'registration_number' => 'required|string|max:50',
            'colmena_code' => 'required|string|max:50',
            'date' => 'required|date',
            'responsable' => ['required', 'string', 'max:150', 'regex:/^[^\d]+$/'],
            'general_state' => 'required|in:Bueno,Regular,Malo',
            'honey_presence' => 'required|in:Bueno,Regular,Malo',
            'harvested_honey_kg' => 'nullable|numeric|min:0',
            'queen_seen' => 'nullable|boolean',
            'queen_marked' => 'nullable|boolean',
            'queen_color' => 'nullable|string|max:50',
            'diseases' => 'nullable|array',
            'tasks' => 'nullable|array',
            'feed_supplied' => ['nullable', 'string', 'max:150', 'regex:/^[^\d]*$/'],
            'notes' => 'nullable|string',
        ], [
            'responsable.regex' => 'No se permiten números en el responsable. Solo se aceptan datos reales de personas.',
            'feed_supplied.regex' => 'No se permiten números en la alimentación suministrada. Solo se permiten letras.',
        ]);
    }

    /**
     * Busca la colmena por código y verifica que se pueda inspeccionar (activa y en un apiario activo).
     * Devuelve [colmena, idLegacy] o una respuesta de error.
     */
    protected function resolverColmena(string $code)
    {
        $colmena = \Modules\Apicola\Entities\Colmena::with('apiario')->where('code', $code)->first();
        if (!$colmena) {
            return response()->json(['success' => false, 'message' => "La colmena \"{$code}\" no existe. Escribe un código registrado."], 422);
        }

        if ($motivo = $colmena->motivoNoRegistrable()) {
            return response()->json(['success' => false, 'message' => $motivo], 422);
        }

        $legacy = DB::table('colmenas')->where('codigo', $colmena->code)->first();
        if (!$legacy) {
            return response()->json(['success' => false, 'message' => "La colmena {$colmena->code} no está sincronizada. Edítala y guárdala de nuevo."], 422);
        }

        return [$colmena, $legacy->id_colmenas];
    }

    protected function guardarEnfermedades(int $idRegistro, array $enfermedades): void
    {
        DB::table('enfermedades_registro')->where('id_registro', $idRegistro)->delete();

        foreach ($enfermedades as $enf) {
            if (!empty($enf)) {
                DB::table('enfermedades_registro')->insert([
                    'id_registro' => $idRegistro,
                    'nombre_enfermedad' => $enf,
                    'estado' => 'Detectada',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    protected function datosInspeccion(Request $request, $idColmena): array
    {
        $notas = $request->input('notes');
        $responsableText = $this->responsableTexto($request);
        $observacionesFinal = $responsableText ? "Responsable: {$responsableText}" . ($notas ? " | {$notas}" : "") : $notas;

        return [
            'id_colmena' => $idColmena,
            'numero_registro' => $request->input('registration_number'),
            'fecha' => $request->input('date'),
            'miel_cosechada' => $request->input('harvested_honey_kg') ?? 0,
            'estado_colmena' => $request->input('general_state') ?? 'Bueno',
            'presencia_miel' => ($request->input('honey_presence') === 'Bueno' || $request->input('honey_presence') == 1) ? 1 : 0,
            'reina_vista' => $request->boolean('queen_seen') ? 1 : 0,
            'reina_marcada' => $request->boolean('queen_marked') ? 1 : 0,
            'color_reina' => $request->input('queen_color') ?? 'Sin marcar',
            'alimentacion_suministrada' => $request->input('feed_supplied'),
            'observaciones' => $observacionesFinal,
        ];
    }
}
