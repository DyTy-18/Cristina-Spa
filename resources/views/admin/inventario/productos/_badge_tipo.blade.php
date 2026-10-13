{{-- Etiqueta del tipo de un producto de reventa. Espera $p (con tipo y peso_gramos). --}}
@if ($p->tipo === 'vitrina')
    <span class="badge badge-success" style="font-size:0.65rem;">Vitrina</span>
@elseif ($p->tipo === 'seguimiento')
    <span class="badge badge-info" style="font-size:0.65rem;">Seguimiento{{ $p->peso_gramos ? ' · ' . rtrim(rtrim(number_format($p->peso_gramos, 2, '.', ''), '0'), '.') . ' g' : '' }}</span>
@else
    <span class="badge" style="font-size:0.65rem;">Genérico</span>
@endif
