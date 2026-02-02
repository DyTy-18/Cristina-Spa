<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Cristina Spa</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;600&family=Montserrat:wght@200;300;400&display=swap"
        rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #2c2c2c;
            --secondary-color: #8b7355;
            --accent-color: #c9a96e;
            --light-bg: #f8f6f4;
            --white: #ffffff;
            --text-dark: #1a1a1a;
            --text-light: #666;
            --error-color: #c9302c;
            --transition: all 0.3s ease;
        }

        html,
        body {
            height: 100%;
        }

        body {
            font-family: 'Montserrat', sans-serif;
            color: var(--text-dark);
            background: linear-gradient(135deg, var(--light-bg) 0%, #fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            position: relative;
            overflow: hidden;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-image:
                radial-gradient(circle at 20% 50%, rgba(201, 169, 110, 0.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(139, 115, 85, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 60% 20%, rgba(201, 169, 110, 0.1) 0%, transparent 40%);
            pointer-events: none;
        }

        h1,
        h2,
        h3 {
            font-family: 'Cormorant Garamond', serif;
            font-weight: 400;
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            padding: 2rem;
        }

        .login-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 3rem;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
            animation: fadeInUp 0.8s ease;
        }

        .logo {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .logo h1 {
            font-size: 2.2rem;
            color: var(--primary-color);
            letter-spacing: 3px;
            font-weight: 300;
            margin-bottom: 0.5rem;
        }

        .logo p {
            font-size: 0.85rem;
            color: var(--text-light);
            letter-spacing: 2px;
            text-transform: uppercase;
            font-weight: 300;
        }

        .login-title {
            text-align: center;
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 2rem;
            letter-spacing: 2px;
            position: relative;
        }

        .login-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 50px;
            height: 1px;
            background: var(--accent-color);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-size: 0.8rem;
            color: var(--text-light);
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
            font-weight: 300;
        }

        .form-group input {
            width: 100%;
            padding: 1rem;
            border: 1px solid rgba(0, 0, 0, 0.1);
            background: var(--white);
            font-family: 'Montserrat', sans-serif;
            font-size: 0.95rem;
            font-weight: 300;
            transition: var(--transition);
            color: var(--text-dark);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(201, 169, 110, 0.1);
        }

        .form-group input::placeholder {
            color: #bbb;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            font-size: 0.85rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--text-light);
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--accent-color);
            cursor: pointer;
        }

        .forgot-password {
            color: var(--accent-color);
            text-decoration: none;
            transition: var(--transition);
        }

        .forgot-password:hover {
            color: var(--secondary-color);
        }

        .login-button {
            width: 100%;
            padding: 1rem 2rem;
            background: var(--primary-color);
            color: var(--white);
            border: 2px solid var(--primary-color);
            cursor: pointer;
            font-size: 0.9rem;
            letter-spacing: 2px;
            text-transform: uppercase;
            transition: var(--transition);
            font-family: 'Montserrat', sans-serif;
            font-weight: 300;
        }

        .login-button:hover {
            background: transparent;
            color: var(--primary-color);
        }

        .error-message {
            background: rgba(201, 48, 44, 0.1);
            border: 1px solid rgba(201, 48, 44, 0.3);
            color: var(--error-color);
            padding: 0.8rem 1rem;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
            font-weight: 300;
        }

        .back-home {
            display: block;
            text-align: center;
            margin-top: 2rem;
            color: var(--text-light);
            text-decoration: none;
            font-size: 0.85rem;
            letter-spacing: 1px;
            transition: var(--transition);
        }

        .back-home:hover {
            color: var(--accent-color);
        }

        .decorative-line {
            position: absolute;
            width: 1px;
            height: 60px;
            background: var(--accent-color);
            bottom: -80px;
            left: 50%;
            transform: translateX(-50%);
            animation: scrollDown 2s infinite;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes scrollDown {

            0%,
            100% {
                transform: translateX(-50%) scaleY(0);
                transform-origin: top;
            }

            50% {
                transform: translateX(-50%) scaleY(1);
                transform-origin: top;
            }
        }

        @media (max-width: 480px) {
            .login-container {
                padding: 1rem;
            }

            .login-card {
                padding: 2rem 1.5rem;
            }

            .logo h1 {
                font-size: 1.8rem;
            }

            .form-options {
                flex-direction: column;
                gap: 1rem;
                align-items: flex-start;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-card">
            <div class="logo">
                <h1>Cristina Spa</h1>
                <p>Belleza & Estilo</p>
            </div>

            <h2 class="login-title">Iniciar Sesión</h2>

            @if ($errors->any())
                <div class="error-message">
                    @foreach ($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}"
                        placeholder="tu@email.com" required autofocus>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="••••••••" required>
                </div>

                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember">
                        <span>Recordarme</span>
                    </label>
                </div>

                <button type="submit" class="login-button">
                    Entrar
                </button>
            </form>

            <a href="{{ url('/') }}" class="back-home">
                ← Volver al inicio
            </a>
        </div>
        <div class="decorative-line"></div>
    </div>
</body>

</html>
