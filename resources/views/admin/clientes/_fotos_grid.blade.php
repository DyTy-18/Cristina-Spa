{{-- Grilla de fotos de seguimiento. Recibe $fotos (colección de SeguimientoFoto). --}}
@once
    @push('styles')
    <style>
        .fotos-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 220px)); gap: 1rem; }
        .foto-item { border: 1px solid rgba(0,0,0,0.08); background: var(--white); display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
        .foto-item a.foto-thumb { display: block; aspect-ratio: 1 / 1; overflow: hidden; background: var(--light-bg); }
        .foto-item img { width: 100%; height: 100%; object-fit: cover; display: block; transition: transform 0.2s; }
        .foto-item a.foto-thumb:hover img { transform: scale(1.03); }
        .foto-meta { padding: 0.6rem 0.7rem 0.5rem; font-size: 0.72rem; color: var(--text-light); flex: 1; min-width: 0; }
        .foto-meta .foto-desc { color: var(--text-dark); font-size: 0.78rem; margin-bottom: 0.2rem; overflow-wrap: anywhere; }
        .foto-acciones { display: grid; grid-template-columns: 1fr 1fr; gap: 0.4rem; padding: 0 0.7rem 0.7rem; }
        .foto-acciones form { margin: 0; display: flex; min-width: 0; }
        .foto-acciones .btn { width: 100%; min-width: 0; box-sizing: border-box; padding: 0.4rem 0.25rem;
                              font-size: 0.62rem; letter-spacing: 0.5px; text-align: center; white-space: nowrap;
                              overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; justify-content: center; }
        .foto-acciones .btn-eliminar:hover { background: #c0392b; border-color: #c0392b; color: #fff; }
    </style>
    @endpush
@endonce

<div class="fotos-grid">
    @foreach($fotos as $foto)
        <div class="foto-item">
            <a href="{{ $foto->url }}" target="_blank" rel="noopener" class="foto-thumb" title="Ver en tamaño completo">
                <img src="{{ $foto->url }}" alt="{{ $foto->descripcion ?? 'Foto del resultado' }}" loading="lazy">
            </a>
            <div class="foto-meta">
                @if($foto->descripcion)
                    <div class="foto-desc">{{ $foto->descripcion }}</div>
                @endif
                {{ $foto->created_at->format('d/m/Y H:i') }}
            </div>
            <div class="foto-acciones">
                <a href="{{ $foto->url }}?descargar=1" class="btn btn-outline btn-sm">Descargar</a>
                <form action="{{ route('admin.seguimiento-fotos.destroy', $foto) }}" method="POST"
                      onsubmit="return confirm('¿Eliminar esta foto del álbum?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline btn-sm btn-eliminar">Eliminar</button>
                </form>
            </div>
        </div>
    @endforeach
</div>
