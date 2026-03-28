<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Tu primera visita — Cristina Spa</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --gold:    #c9a96e;
            --dark:    #1a1a2e;
            --mid:     #4a4a6a;
            --light:   #8a8a9a;
            --bg:      #f9f8f6;
            --white:   #ffffff;
            --success: #22c55e;
            --error:   #ef4444;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--bg);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ── Header ── */
        .reco-header {
            background: var(--dark);
            padding: 1.4rem 1.5rem 1.2rem;
            text-align: center;
        }
        .reco-logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.6rem;
            color: var(--white);
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 300;
        }
        .reco-logo span { color: var(--gold); }

        /* ── Hero band ── */
        .reco-hero {
            background: linear-gradient(135deg, var(--dark) 0%, #2d2d4a 100%);
            padding: 2rem 1.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .reco-hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%23c9a96e' fill-opacity='0.04'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        .reco-badge {
            display: inline-block;
            background: rgba(201,169,110,0.15);
            border: 1px solid rgba(201,169,110,0.4);
            color: var(--gold);
            font-size: 0.65rem;
            letter-spacing: 2.5px;
            text-transform: uppercase;
            padding: 0.35rem 1rem;
            margin-bottom: 1rem;
            position: relative;
        }
        .reco-hero-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: var(--white);
            font-weight: 400;
            letter-spacing: 1px;
            line-height: 1.2;
            margin-bottom: 0.6rem;
            position: relative;
        }
        .reco-hero-sub {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.55);
            font-weight: 300;
            letter-spacing: 0.3px;
            position: relative;
        }
        .reco-hero-sub strong {
            color: var(--gold);
            font-weight: 400;
        }

        /* ── Discount chip ── */
        .descuento-chip {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            margin: -1px 0 0;
            padding: 0.9rem 1.5rem;
            background: var(--gold);
        }
        .descuento-pct {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2rem;
            color: var(--white);
            font-weight: 400;
            line-height: 1;
        }
        .descuento-label {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.9);
            font-weight: 300;
            letter-spacing: 0.5px;
            line-height: 1.4;
            text-align: left;
        }

        /* ── Form card ── */
        .reco-body {
            flex: 1;
            padding: 1.5rem;
            max-width: 480px;
            margin: 0 auto;
            width: 100%;
        }

        .form-card {
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.07);
            padding: 1.75rem 1.5rem;
            margin-bottom: 1.25rem;
        }

        .form-card-title {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.1rem;
            color: var(--dark);
            letter-spacing: 1px;
            margin-bottom: 1.25rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px solid rgba(0,0,0,0.06);
        }

        .fg { margin-bottom: 1rem; }
        .fg:last-child { margin-bottom: 0; }

        label {
            display: block;
            font-size: 0.68rem;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: var(--light);
            margin-bottom: 0.35rem;
            font-weight: 400;
        }
        label .req { color: var(--gold); }

        input, select {
            width: 100%;
            padding: 0.7rem 0.9rem;
            border: 1px solid rgba(0,0,0,0.12);
            border-radius: 0;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.85rem;
            color: var(--dark);
            font-weight: 300;
            background: var(--white);
            outline: none;
            transition: border-color 0.15s;
            -webkit-appearance: none;
            appearance: none;
        }
        input:focus, select:focus { border-color: var(--gold); }
        input::placeholder { color: var(--light); font-weight: 300; }

        .select-wrap { position: relative; }
        .select-wrap::after {
            content: '▾';
            position: absolute;
            right: 0.8rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light);
            font-size: 0.75rem;
            pointer-events: none;
        }

        .two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }

        .err-msg {
            font-size: 0.72rem;
            color: var(--error);
            margin-top: 0.25rem;
            display: block;
        }

        /* ── Submit ── */
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: var(--dark);
            color: var(--white);
            border: none;
            font-family: 'Montserrat', sans-serif;
            font-size: 0.75rem;
            font-weight: 400;
            letter-spacing: 2px;
            text-transform: uppercase;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 0.25rem;
        }
        .btn-submit:hover { background: #2d2d4a; }
        .btn-submit:active { opacity: 0.9; }

        /* ── Trust strip ── */
        .trust-strip {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            flex-wrap: wrap;
            padding: 1.25rem 1.5rem;
            border-top: 1px solid rgba(0,0,0,0.06);
            background: var(--white);
        }
        .trust-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.68rem;
            color: var(--light);
            letter-spacing: 0.3px;
        }
        .trust-icon { font-size: 0.9rem; }

        /* ── Footer ── */
        .reco-footer {
            padding: 1rem 1.5rem;
            text-align: center;
            font-size: 0.65rem;
            color: var(--light);
            letter-spacing: 0.5px;
        }

        @media (max-width: 360px) {
            .two-col { grid-template-columns: 1fr; }
            .reco-hero-title { font-size: 1.7rem; }
        }
    </style>
</head>
<body>

<header class="reco-header">
    <div class="reco-logo">Cristina <span>Spa</span></div>
</header>

<div class="reco-hero">
    <div class="reco-badge">Invitación especial</div>
    <h1 class="reco-hero-title">Tu primera visita<br>con descuento</h1>
    <p class="reco-hero-sub">
        <strong>{{ $link->cliente->nombre }}</strong> te invita a conocer Cristina Spa
    </p>
</div>

<div class="descuento-chip">
    <div class="descuento-pct">10%</div>
    <div class="descuento-label">
        de descuento<br>en tu primera visita
    </div>
</div>

<div class="reco-body">

    @if($errors->any())
        <div style="background:rgba(239,68,68,0.08);border:1px solid rgba(239,68,68,0.25);padding:0.85rem 1rem;margin-bottom:1rem;font-size:0.78rem;color:#b91c1c;">
            @foreach($errors->all() as $error)
                <div>· {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form action="{{ route('recomendacion.store', $link->token) }}" method="POST">
        @csrf

        <div class="form-card">
            <div class="form-card-title">Tus datos</div>

            <div class="two-col">
                <div class="fg">
                    <label>Nombre <span class="req">*</span></label>
                    <input type="text" name="nombre" value="{{ old('nombre') }}"
                           placeholder="Tu nombre" required autocomplete="given-name">
                    @error('nombre')<span class="err-msg">{{ $message }}</span>@enderror
                </div>
                <div class="fg">
                    <label>Apellido</label>
                    <input type="text" name="apellido" value="{{ old('apellido') }}"
                           placeholder="Apellido" autocomplete="family-name">
                </div>
            </div>

            <div class="fg">
                <label>WhatsApp / Teléfono <span class="req">*</span></label>
                <input type="tel" name="telefono" value="{{ old('telefono') }}"
                       placeholder="Ej. 70000000" required autocomplete="tel">
                @error('telefono')<span class="err-msg">{{ $message }}</span>@enderror
            </div>
        </div>

        <div class="form-card">
            <div class="form-card-title">Tu visita</div>

            <div class="fg">
                <label>Sucursal que prefieres <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="sucursal" required>
                        <option value="">— Elige una sucursal —</option>
                        @foreach($sucursales as $s)
                            <option value="{{ $s }}" {{ old('sucursal') === $s ? 'selected' : '' }}>
                                {{ $s }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @error('sucursal')<span class="err-msg">{{ $message }}</span>@enderror
            </div>

            <div class="fg">
                <label>Servicio que te interesa <span class="req">*</span></label>
                <div class="select-wrap">
                    <select name="servicio_id" required>
                        <option value="">— Elige un servicio —</option>
                        @php $catActual = null; @endphp
                        @foreach($servicios as $srv)
                            @if($srv->categoria !== $catActual)
                                @if($catActual !== null)</optgroup>@endif
                                <optgroup label="{{ ucfirst($srv->categoria ?? 'General') }}">
                                @php $catActual = $srv->categoria; @endphp
                            @endif
                            <option value="{{ $srv->id }}"
                                {{ old('servicio_id') == $srv->id ? 'selected' : '' }}>
                                {{ $srv->nombre }}
                                @if($srv->precio) — Bs. {{ number_format((float)$srv->precio, 0) }} @endif
                            </option>
                        @endforeach
                        @if($catActual !== null)</optgroup>@endif
                    </select>
                </div>
                @error('servicio_id')<span class="err-msg">{{ $message }}</span>@enderror
            </div>
        </div>

        <button type="submit" class="btn-submit">
            Reservar con 10% de descuento
        </button>

    </form>

    <div class="trust-strip">
        <div class="trust-item"><span class="trust-icon">✦</span> Desde 2006</div>
        <div class="trust-item"><span class="trust-icon">✦</span> 6 sucursales</div>
        <div class="trust-item"><span class="trust-icon">✦</span> La Paz, Bolivia</div>
    </div>

</div>

<footer class="reco-footer">
    © Cristina Spa · La Paz, Bolivia
</footer>

</body>
</html>
