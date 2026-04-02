<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AlertaStock;

class AlertaStockController extends Controller
{
    public function leer(AlertaStock $alerta)
    {
        $alerta->update(['leida' => true]);
        return response()->json(['ok' => true]);
    }
}
