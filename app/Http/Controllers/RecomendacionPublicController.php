<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Cupon;
use App\Models\CuponUso;
use App\Models\Lead;
use App\Models\RecomendacionLink;
use App\Models\Servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RecomendacionPublicController extends Controller
{
    const DESCUENTO_REFERIDO   = 10; // % para quien llena el formulario
    const DESCUENTO_REFERIDOR  = 20; // % para quien recomendó
    const DIAS_VENCIMIENTO     = 90; // días de validez del cupón

    public function show(string $token)
    {
        $link = RecomendacionLink::with('cliente')
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        $servicios = Servicio::activos()->orderBy('categoria')->orderBy('nombre')->get();

        $sucursales = [
            'Hotel Gloria',
            'San Miguel',
            'Calacoto',
            'Achumani',
            'Obrajes',
            'Otra sucursal',
        ];

        return view('recomendacion.form', compact('link', 'servicios', 'sucursales'));
    }

    public function store(Request $request, string $token)
    {
        $link = RecomendacionLink::with('cliente')
            ->where('token', $token)
            ->where('activo', true)
            ->firstOrFail();

        $data = $request->validate([
            'nombre'      => 'required|string|max:100',
            'apellido'    => 'nullable|string|max:100',
            'telefono'    => 'required|string|max:30',
            'sucursal'    => 'required|string|max:100',
            'servicio_id' => 'required|exists:servicios,id',
        ]);

        // Incrementar clicks
        $link->increment('clicks');

        $servicio = Servicio::findOrFail($data['servicio_id']);

        // Crear cliente referido (o recuperar si ya existe por teléfono)
        $cliente = Cliente::firstOrCreate(
            ['telefono' => $data['telefono']],
            [
                'nombre'   => $data['nombre'],
                'apellido' => $data['apellido'] ?? null,
                'notas'    => "Referido por {$link->cliente->nombre_completo} vía enlace de recomendación.",
            ]
        );

        // Crear cita pendiente
        $precioBase = (float) $servicio->precio;
        $precioNeto = round($precioBase * (1 - self::DESCUENTO_REFERIDO / 100), 2);

        $cita = Cita::create([
            'cliente_id'  => $cliente->id,
            'fecha'       => today()->toDateString(),
            'hora'        => '10:00:00',
            'estado'      => 'pendiente',
            'precio_final'=> $precioNeto,
            'notas'       => "Sucursal solicitada: {$data['sucursal']}. Cita generada desde enlace de recomendación.",
        ]);

        CitaServicio::create([
            'cita_id'              => $cita->id,
            'servicio_id'          => $servicio->id,
            'precio_unitario'      => $precioBase,
            'descuento_porcentaje' => self::DESCUENTO_REFERIDO,
        ]);

        // Cupón para el referido (10% off — ya aplicado a esta cita)
        $cuponReferido = Cupon::create([
            'codigo'            => 'BIENVENIDA-' . strtoupper(Str::random(6)),
            'descripcion'       => "Bienvenida — recomendado por {$link->cliente->nombre_completo}",
            'tipo'              => 'porcentaje',
            'valor'             => self::DESCUENTO_REFERIDO,
            'usos_maximos'      => 1,
            'fecha_vencimiento' => today()->addDays(self::DIAS_VENCIMIENTO),
            'activo'            => true,
        ]);

        CuponUso::create([
            'cupon_id'   => $cuponReferido->id,
            'cita_id'    => $cita->id,
            'cliente_id' => $cliente->id,
            'notas'      => 'Aplicado automáticamente al registrarse por recomendación.',
        ]);

        // Cupón para el referidor (10% off su próxima visita)
        $cuponReferidor = Cupon::create([
            'codigo'            => 'GRACIAS-' . strtoupper(Str::random(6)),
            'descripcion'       => "Gracias por recomendar — {$cliente->nombre_completo} se registró",
            'tipo'              => 'porcentaje',
            'valor'             => self::DESCUENTO_REFERIDOR,
            'usos_maximos'      => 1,
            'fecha_vencimiento' => today()->addDays(self::DIAS_VENCIMIENTO),
            'activo'            => true,
        ]);

        // Registrar lead con todo relacionado
        Lead::create([
            'nombre'                => $cliente->nombre,
            'telefono'              => $cliente->telefono,
            'sucursal'              => $data['sucursal'],
            'servicio'              => $servicio->nombre,
            'servicio_id'           => $servicio->id,
            'estado'                => 'convertido',
            'ip_address'            => $request->ip(),
            'recomendacion_link_id' => $link->id,
            'cliente_id'            => $cliente->id,
            'cita_id'               => $cita->id,
            'cupon_referido_id'     => $cuponReferido->id,
            'cupon_referidor_id'    => $cuponReferidor->id,
        ]);

        return redirect()->route('recomendacion.gracias', [
            'token'          => $token,
            'cupon'          => $cuponReferido->codigo,
            'servicio'       => $servicio->nombre,
            'referidor'      => $link->cliente->nombre,
        ]);
    }
}
