<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Contenido editable de la página pública, guardado como JSON por clave.
 *
 * Clave "servicios": sección "Nuestros Servicios" del welcome.
 *   etiqueta, titulo, descripcion, catalogo_texto, catalogo_pdf,
 *   categorias[] → { nombre, icono, nota, secciones[] }
 *   secciones[]  → { titulo, columnas[] {nombre, detalle}, items[] {nombre, precios[]} }
 * Una sección con una sola columna se muestra como lista; con varias, como tabla.
 */
class ContenidoWeb extends Model
{
    use LogsActivity;

    public const SERVICIOS = 'servicios';

    protected $table = 'contenidos_web';

    protected $fillable = ['clave', 'valor'];

    protected $casts = [
        'valor' => 'array',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['clave', 'valor'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('contenido_web');
    }

    public static function obtener(string $clave): array
    {
        $valor = static::where('clave', $clave)->value('valor');

        if ($valor !== null) {
            return is_array($valor) ? $valor : json_decode($valor, true);
        }

        return $clave === self::SERVICIOS ? static::serviciosPorDefecto() : [];
    }

    public static function guardar(string $clave, array $valor): void
    {
        static::updateOrCreate(['clave' => $clave], ['valor' => $valor]);
    }

    /** Contenido original de la página (antes de ser editable). */
    public static function serviciosPorDefecto(): array
    {
        $largos = [
            ['nombre' => 'Corto', 'detalle' => ''],
            ['nombre' => 'Mediano', 'detalle' => 'Hasta Hombros'],
            ['nombre' => 'Largo', 'detalle' => 'Hasta Media Espalda'],
            ['nombre' => 'Extra Largo', 'detalle' => 'Hasta Cintura'],
        ];
        $precio = [['nombre' => 'Precio', 'detalle' => '']];

        $items = fn (array $filas) => array_map(
            fn ($fila) => ['nombre' => $fila[0], 'precios' => array_slice($fila, 1)],
            $filas
        );

        return [
            'etiqueta'       => 'Lo Que Ofrecemos',
            'titulo'         => 'Nuestros Servicios',
            'descripcion'    => 'Descubre nuestra amplia gama de servicios diseñados para realzar tu belleza natural y proporcionarte momentos de relajación absoluta.',
            'catalogo_texto' => 'Descargar Catálogo Kérastase',
            'catalogo_pdf'   => 'pdf/CATALOGO KER JUNIO 2025.pdf',
            'categorias'     => [
                [
                    'nombre' => 'Peluquería', 'icono' => '✂️', 'nota' => '',
                    'secciones' => [
                        ['titulo' => 'Cortes', 'columnas' => $precio, 'items' => $items([
                            ['Corte de Mujer', '250 Bs.'],
                            ['Corte de Varón', '100 Bs.'],
                            ['Corte de Flequillo', '80 Bs.'],
                        ])],
                        ['titulo' => 'Peinados — por largo de cabello', 'columnas' => $largos, 'items' => $items([
                            ['Lavado', '80 Bs.', '80 Bs.', '80 Bs.', '80 Bs.'],
                            ['Planchado o Bucles', '70 Bs.', '80 Bs.', '90 Bs.', '100 Bs.'],
                            ['Semi-recogido', '100 Bs.', '120 Bs.', '150 Bs.', '160 Bs.'],
                            ['Recogido', '150 Bs.', '150 Bs.', '200 Bs.', '200 Bs.'],
                            ['Peinado de Novia', '160 Bs.', '180 Bs.', '200 Bs.', '200 Bs.'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Coloración', 'icono' => '🎨', 'nota' => '* Precios previa evaluación',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => $largos, 'items' => $items([
                            ['Retoque de Raíz', '310 Bs. (1cm)', '350 Bs. (3cm)', '—', '—'],
                            ['Matizado con masque Kérastase', '180 Bs.', '200 Bs.', '250 Bs.', '270 Bs.'],
                            ['Matizado Tono Sobre Tono', '250 Bs.', '250 Bs.', '300 Bs.', '300 Bs.'],
                            ['Tiger Eye (Sin Fondo)', '450 Bs.', '650 Bs.', '800 Bs.', '950 Bs.'],
                            ['Baby Lights (Sin Fondo)', '450 Bs.', '650 Bs.', '850 Bs.', '950 Bs.'],
                            ['Balayage Platinado', '750 Bs.', '950 Bs.', '1.150 Bs.', '1.800 Bs.'],
                            ['Color Global', '500 Bs.', '600 Bs.', '700 Bs.', '800 Bs.'],
                            ['Morena Iluminada (sin decoloración)', '550 Bs.', '750 Bs.', '950 Bs.', '1.050 Bs.'],
                            ['Mechas Creativas Rubias', '700 Bs.', '900 Bs.', '1.100 Bs.', '1.600 Bs.'],
                            ['Color de Fondo (cualquier técnica)', '300 Bs.', '300 Bs.', '350 Bs.', '350 Bs.'],
                            ['Metal-Detox', '180 Bs.', '180 Bs.', '250 Bs.', '250 Bs.'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Alisado u Ondulación', 'icono' => '💫', 'nota' => '',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => $largos, 'items' => $items([
                            ['Alisado Definitivo', '750 Bs.', '850 Bs.', '1.300 Bs.', '1.600 Bs.'],
                            ['Ondulación o Permanente', '500 Bs.', '600 Bs.', '700 Bs.', '800 Bs.'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Depilado con Cera', 'icono' => '🌿', 'nota' => '',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => [
                            ['nombre' => 'Con Cera', 'detalle' => ''],
                            ['nombre' => 'Con Hilo', 'detalle' => ''],
                        ], 'items' => $items([
                            ['Facial', '120 Bs.', '80 Bs.'],
                            ['Pierna Completa', '150 Bs.', '—'],
                            ['Cuello', '50 Bs.', '—'],
                            ['Axilas', '80 Bs.', '—'],
                            ['Perfilado de Cejas', '60 Bs.', '30 Bs.'],
                            ['Bozo', '40 Bs.', '30 Bs.'],
                            ['Espalda', '100 Bs.', '—'],
                            ['Abdomen', '80 Bs.', '—'],
                            ['Brasilero', '150 Bs.', '—'],
                            ['Brazo', '120 Bs.', '—'],
                            ['Depilación Completa', '500 Bs.', '—'],
                            ['Biquini', '100 Bs.', '—'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Maquillaje', 'icono' => '💄', 'nota' => '',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => $precio, 'items' => $items([
                            ['Maquillaje de Fiesta Sin Pestañas', '150 Bs.'],
                            ['Pestañas 3D / 5D / 6D', '80 Bs.'],
                            ['Cejas y Pestañas', '120 Bs.'],
                            ['Rizado de Pestañas', '100 Bs.'],
                            ['Laminado de Cejas', '200 Bs.'],
                            ['Rizado de Pestañas + Laminado de Cejas', 'Consultar'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Manicura & Pedicura', 'icono' => '💅', 'nota' => '* El precio varía de acuerdo al largo y al diseño',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => $precio, 'items' => $items([
                            ['Kit Manicura', '60 Bs.'],
                            ['Pedicura', '60 Bs.'],
                            ['Kit de Pedicura', '80 Bs.'],
                            ['Extensiones de Uñas', '80 Bs.'],
                            ['Esmaltado Normal', '30 Bs.'],
                            ['Esmaltado Normal con Diseños', '50 Bs.'],
                            ['Pintado en Gel', '120 Bs.'],
                            ['Forrado de Uñas — Técnica Dipping', '80 Bs.'],
                            ['Forrado de Acrílico con Tips', '160 Bs.'],
                            ['Uñas Esculpidas con Acrílico', '200 Bs.'],
                            ['Técnica Soft Gel', '200 Bs.'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Spa', 'icono' => '🧖', 'nota' => '',
                    'secciones' => [
                        ['titulo' => 'Limpiezas', 'columnas' => $precio, 'items' => $items([
                            ['Limpieza Facial', '150 Bs.'],
                            ['Limpieza con Micro-Dermo Abrasión', '250 Bs.'],
                        ])],
                        ['titulo' => 'Masajes', 'columnas' => $precio, 'items' => $items([
                            ['Masaje Relajante', '150 Bs.'],
                            ['Exfoliación Corporal', '180 Bs.'],
                            ['Envolvimiento Corporal', '180 Bs.'],
                        ])],
                    ],
                ],
                [
                    'nombre' => 'Tratamientos Capilares', 'icono' => '💎', 'nota' => '',
                    'secciones' => [
                        ['titulo' => '', 'columnas' => $precio, 'items' => $items([
                            ['Tratamiento de Lujo', '650 Bs.'],
                            ['Thermo Blindaje Capilar', '500 Bs.'],
                            ['Tratamiento Nutrición Ampolla Kérastase', '350 Bs.'],
                            ['Tratamiento 24 Kilates, Aurora Botania', '350 Bs.'],
                            ['Tratamiento Express Intenso (Shampoo + Mascarilla)', '200 Bs.'],
                            ['Tratamiento Essencial (Shampoo + Acondicionador)', '150 Bs.'],
                        ])],
                    ],
                ],
            ],
        ];
    }
}
