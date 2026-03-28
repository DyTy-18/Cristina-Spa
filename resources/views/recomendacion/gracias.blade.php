<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>¡Bienvenida! — Cristina Spa</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Montserrat:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        :root {
            --gold: #c9a96e;
            --dark: #1a1a2e;
            --mid:  #4a4a6a;
            --light:#8a8a9a;
            --bg:   #f9f8f6;
            --white:#ffffff;
        }
        body {
            font-family: 'Montserrat', sans-serif;
            background: var(--bg);
            color: var(--dark);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            text-align: center;
        }

        .logo {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.4rem;
            color: var(--dark);
            letter-spacing: 4px;
            text-transform: uppercase;
            font-weight: 300;
            margin-bottom: 2rem;
        }
        .logo span { color: var(--gold); }

        .check-circle {
            width: 64px;
            height: 64px;
            background: rgba(34,197,94,0.1);
            border: 1px solid rgba(34,197,94,0.3);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            margin: 0 auto 1.5rem;
        }

        h1 {
            font-family: 'Cormorant Garamond', serif;
            font-size: 2.2rem;
            font-weight: 400;
            letter-spacing: 1px;
            margin-bottom: 0.5rem;
        }

        .sub {
            font-size: 0.82rem;
            color: var(--light);
            font-weight: 300;
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .cupon-box {
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.07);
            padding: 1.5rem;
            max-width: 340px;
            width: 100%;
            margin: 0 auto 1.5rem;
        }

        .cupon-label {
            font-size: 0.65rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: var(--light);
            margin-bottom: 0.5rem;
        }

        .cupon-code {
            font-family: 'Cormorant Garamond', serif;
            font-size: 1.8rem;
            color: var(--dark);
            letter-spacing: 3px;
            font-weight: 400;
            margin-bottom: 0.4rem;
        }

        .cupon-desc {
            font-size: 0.7rem;
            color: var(--light);
            line-height: 1.5;
        }

        .cupon-servicio {
            display: inline-block;
            background: rgba(201,169,110,0.1);
            border: 1px solid rgba(201,169,110,0.3);
            color: var(--gold);
            font-size: 0.68rem;
            letter-spacing: 0.5px;
            padding: 0.25rem 0.7rem;
            margin-top: 0.5rem;
        }

        .info-row {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            text-align: left;
            max-width: 340px;
            width: 100%;
            margin: 0 auto 0.75rem;
            padding: 0.9rem 1rem;
            background: var(--white);
            border: 1px solid rgba(0,0,0,0.06);
        }
        .info-icon { font-size: 1rem; flex-shrink: 0; margin-top: 0.05rem; }
        .info-text { font-size: 0.75rem; color: var(--mid); line-height: 1.5; font-weight: 300; }
        .info-text strong { color: var(--dark); font-weight: 400; }

        .footer-note {
            margin-top: 2rem;
            font-size: 0.65rem;
            color: var(--light);
            letter-spacing: 0.5px;
        }
    </style>
</head>
<body>

<div class="logo">Cristina <span>Spa</span></div>

<div class="check-circle">✓</div>

<h1>¡Bienvenida!</h1>
<p class="sub">
    Tu registro fue exitoso.<br>
    <strong style="color:var(--dark);font-weight:400;">{{ request('referidor') }}</strong> te dio la bienvenida.
</p>

<div class="cupon-box">
    <div class="cupon-label">Tu cupón de bienvenida</div>
    <div class="cupon-code">{{ request('cupon') }}</div>
    <div class="cupon-desc">
        10% de descuento aplicado a tu primera visita<br>
        Válido por 90 días
    </div>
    <div class="cupon-servicio">{{ request('servicio') }}</div>
</div>

<div class="info-row">
    <span class="info-icon">📅</span>
    <div class="info-text">
        <strong>Tu cita está registrada como pendiente.</strong><br>
        Nuestro equipo se comunicará contigo para confirmar fecha y hora.
    </div>
</div>

<div class="info-row">
    <span class="info-icon">📍</span>
    <div class="info-text">
        Cristina Spa · La Paz, Bolivia<br>
        <strong>6 sucursales</strong> a tu disposición
    </div>
</div>

<p class="footer-note">© Cristina Spa · Desde 2006</p>

</body>
</html>
