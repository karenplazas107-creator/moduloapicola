<?php

namespace Modules\Apicola\Http\Controllers\Aprendiz;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Apicola\Entities\Apiario;
use Modules\Apicola\Entities\Colmena;
use Modules\Apicola\Entities\Inspeccion;
use Modules\Apicola\Entities\Inventario;

class AprendizController extends Controller
{
    /**
     * El aprendiz tiene Colmenas como su módulo exclusivo.
     */
    public function dashboard()
    {
        return redirect()->route('apicola.aprendiz.colmenas.index');
    }
}
