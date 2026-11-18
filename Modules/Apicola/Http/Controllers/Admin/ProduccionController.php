<?php

namespace Modules\Apicola\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\MotivoBaja;
use Modules\Apicola\Entities\Produccion;
use Modules\Apicola\Entities\Responsable;

/**
 * Submódulo "Producción" de Colmenas (cosechas de miel por colmena).
 */
class ProduccionController extends Controller
{
    public function index(Request $request)
    {
        return $this->render($request->get('colmena'));
    }

    public function byColmena($code)
    {
        return $this->render($code);
    }

    private function render(?string $selected)
    {
        $colmenas = Colmena::with('apiario')->orderBy('code', 'asc')->get();
        if ($selected === null) {
            $selected = $colmenas->first()->code ?? '';
        }

        $produccionesInit = $this->listado();
        $inspeccionesResumen = $this->resumenInspecciones();
        $responsablesSugeridos = Responsable::todos();

        return view('apicola::Admin.Produccion.index', compact(
            'selected',
            'colmenas',
            'produccionesInit',
            'inspeccionesResumen',
            'responsablesSugeridos'
        ));
    }

    /**
     * Todos los registros de producción con el formato que usa la interfaz.
     */
    private function listado()
    {
        return Produccion::with('colmena')
            ->orderByDesc('fecha')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Produccion $p) => $this->formatear($p))
            ->values();
    }

    private function formatear(Produccion $p): array
    {
        return [
            'id' => $p->id,
            'regNumber' => $p->numero_registro ?: sprintf('PRO-%04d', $p->id),
            'colmena' => $p->colmena->code ?? '-',
            'colmenaId' => $p->colmena_id,
            'fecha' => $p->fecha->format('d/m/Y'),
            'fechaRaw' => $p->fecha->format('Y-m-d'),
            'mielKg' => (float) $p->miel_kg,
            'responsable' => $p->responsable,
            'notas' => $p->observaciones ?? '',
            'estado' => $p->estado,
        ];
    }

    /**
     * Inspecciones de cada colmena (para mostrar la relación dentro de Producción).
     */
    private function resumenInspecciones(): array
    {
        $resumen = [];
        try {
            $kgInspeccion = Produccion::mielInspeccionesPorColmena();

            $rows = DB::table('registro_inspeccion')
                ->join('colmenas', 'registro_inspeccion.id_colmena', '=', 'colmenas.id_colmenas')
                ->whereNull('registro_inspeccion.deleted_at')
                ->orderByDesc('registro_inspeccion.fecha')
                ->orderByDesc('registro_inspeccion.id_registro')
                ->select('colmenas.codigo', 'registro_inspeccion.numero_registro', 'registro_inspeccion.id_registro', 'registro_inspeccion.fecha', 'registro_inspeccion.estado_colmena')
                ->get();

            foreach ($rows as $r) {
                $code = $r->codigo;
                $resumen[$code]['total'] = ($resumen[$code]['total'] ?? 0) + 1;
                $resumen[$code]['kg'] = $kgInspeccion[$code] ?? 0;
                if (count($resumen[$code]['lista'] ?? []) < 5) {
                    $resumen[$code]['lista'][] = [
                        'regNumber' => $r->numero_registro ?: 'REG-' . str_pad($r->id_registro, 4, '0', STR_PAD_LEFT),
                        'fecha' => \Carbon\Carbon::parse($r->fecha)->format('d/m/Y'),
                        'estado' => $r->estado_colmena ?: 'Bueno',
                    ];
                }
            }
        } catch (\Throwable $e) {
            // Sin inspecciones disponibles
        }

        return $resumen;
    }

    private function reglas(Request $request): array
    {
        return $request->validate([
            'colmena_code' => ['required', 'string', 'exists:apicola_colmenas,code'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'miel_kg' => ['required', 'numeric', 'gt:0', 'max:99999'],
            'responsable' => ['required', 'string', 'max:150', 'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑüÜ\s\.\'-]+$/u'],
            'observaciones' => ['nullable', 'string', 'max:500'],
        ], [
            'colmena_code.exists' => 'La colmena seleccionada no existe.',
            'fecha.before_or_equal' => 'La fecha de la producción no puede ser futura.',
            'miel_kg.gt' => 'La miel cosechada debe ser mayor a 0 kg.',
            'miel_kg.numeric' => 'La miel cosechada debe ser un número.',
            'responsable.regex' => 'El responsable es inválido: no puede contener números, solo nombres reales de personas.',
        ]);
    }

    public function store(Request $request)
    {
        $v = $this->reglas($request);
        $colmena = Colmena::where('code', $v['colmena_code'])->firstOrFail();

        // No se registra producción en colmenas inactivas ni en apiarios desactivados
        if ($motivoBloqueo = $colmena->motivoNoRegistrable()) {
            return response()->json(['success' => false, 'message' => $motivoBloqueo], 422);
        }

        $produccion = Produccion::create([
            'colmena_id' => $colmena->id,
            'fecha' => $v['fecha'],
            'miel_kg' => $v['miel_kg'],
            'responsable' => Responsable::registrar($v['responsable']),
            'observaciones' => $v['observaciones'] ?? null,
            'estado' => 'Activa',
            'user_id' => auth()->id(),
        ]);
        $produccion->update(['numero_registro' => sprintf('PRO-%04d', $produccion->id)]);

        return response()->json([
            'success' => true,
            'message' => "Producción {$produccion->numero_registro} registrada para la colmena {$colmena->code}.",
            'data' => $this->formatear($produccion->fresh('colmena')),
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produccion = Produccion::findOrFail($id);
        $v = $this->reglas($request);
        $colmena = Colmena::where('code', $v['colmena_code'])->firstOrFail();

        // Al cambiar de colmena se aplican las mismas reglas que al crear
        if ($colmena->id !== $produccion->colmena_id && ($motivoBloqueo = $colmena->motivoNoRegistrable())) {
            return response()->json(['success' => false, 'message' => $motivoBloqueo], 422);
        }

        $produccion->update([
            'colmena_id' => $colmena->id,
            'fecha' => $v['fecha'],
            'miel_kg' => $v['miel_kg'],
            'responsable' => Responsable::registrar($v['responsable']),
            'observaciones' => $v['observaciones'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => "Producción {$produccion->numero_registro} actualizada.",
            'data' => $this->formatear($produccion->fresh('colmena')),
        ]);
    }

    /**
     * Activa o desactiva un registro. Para desactivar es obligatorio indicar el motivo.
     */
    public function toggleStatus(Request $request, $id)
    {
        $produccion = Produccion::findOrFail($id);
        $desactiva = $produccion->estado !== 'Inactiva';
        $motivo = $desactiva ? MotivoBaja::exigir($request) : null;

        $produccion->update(['estado' => $desactiva ? 'Inactiva' : 'Activa']);
        if ($motivo) {
            MotivoBaja::registrar('produccion', $produccion->id, $produccion->numero_registro, 'desactivar', $motivo);
        }

        return response()->json([
            'success' => true,
            'nuevo_estado' => $produccion->estado,
            'message' => "Producción {$produccion->numero_registro} marcada como {$produccion->estado}.",
        ]);
    }

    /**
     * "Eliminar" desactiva el registro (no se borra) y exige el motivo.
     */
    public function destroy(Request $request, $id)
    {
        $produccion = Produccion::findOrFail($id);
        $motivo = MotivoBaja::exigir($request);

        $produccion->update(['estado' => 'Inactiva']);
        MotivoBaja::registrar('produccion', $produccion->id, $produccion->numero_registro, 'desactivar', $motivo);

        return response()->json([
            'success' => true,
            'nuevo_estado' => 'Inactiva',
            'message' => "Producción {$produccion->numero_registro} desactivada con éxito.",
        ]);
    }
}
