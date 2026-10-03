@extends('layouts.app')

@section('title', 'Agenda tu cita — Cristina Spa | La Paz, Bolivia')
@section('meta_description', 'Agenda tu cita en Cristina Spa. Elige la fecha que prefieras y nuestro equipo te contactará para confirmar tu reserva.')

@section('head')
    @include('agendar._styles')
@endsection

@section('content')

    <!-- Hero -->
    <section class="nosotros-hero agendar-hero">
        <div class="nosotros-hero-inner">
            <span class="section-label">Reserva en línea</span>
            <h1 class="nosotros-hero-title">Agenda tu cita</h1>
            <p class="nosotros-hero-sub">Elige el día y la hora que prefieras, déjanos tus datos y nuestro equipo te contactará para confirmar tu cita.</p>
        </div>
    </section>

    <section class="contact agendar">
        <form method="POST" action="{{ route('agendar.store') }}" id="agendarForm" class="contact-inner agendar-inner" novalidate>
            @csrf

            {{-- Honeypot anti-spam --}}
            <div class="ag-hp" aria-hidden="true">
                <label>No llenar <input type="text" name="website" tabindex="-1" autocomplete="off"></label>
            </div>

            <!-- Paso 1: calendario -->
            <div class="agendar-col">
                <p class="contact-form-eyebrow"><span class="ag-step">1</span> Elige tu fecha</p>
                <h3 class="contact-form-title">¿Qué día te gustaría venir?</h3>

                <div class="ag-cal {{ $errors->has('fecha_preferida') ? 'is-invalid' : '' }}" id="agCal"
                     data-hoy="{{ $hoy }}" data-min="{{ $primerDia }}" data-max="{{ $fechaMax }}" data-selected="{{ old('fecha_preferida') }}">
                    <div class="ag-cal-head">
                        <button type="button" class="ag-cal-nav" id="agCalPrev" aria-label="Mes anterior">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="15 18 9 12 15 6"/></svg>
                        </button>
                        <span class="ag-cal-month" id="agCalMonth"></span>
                        <button type="button" class="ag-cal-nav" id="agCalNext" aria-label="Mes siguiente">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><polyline points="9 18 15 12 9 6"/></svg>
                        </button>
                    </div>
                    <div class="ag-cal-week">
                        <span>Lu</span><span>Ma</span><span>Mi</span><span>Ju</span><span>Vi</span><span>Sá</span><span>Do</span>
                    </div>
                    <div class="ag-cal-grid" id="agCalGrid"></div>
                    <p class="ag-cal-legend">Atendemos de lunes a sábado · 9:00 – 20:00</p>
                </div>
                <input type="hidden" name="fecha_preferida" id="fechaPreferida" value="{{ old('fecha_preferida') }}">
                @error('fecha_preferida') <p class="ag-err">{{ $message }}</p> @enderror

                @php
                    $grupos = ['Mañana' => [], 'Tarde' => [], 'Noche' => []];
                    foreach ($horarios as $h) {
                        $grupos[$h < '13:00' ? 'Mañana' : ($h < '17:00' ? 'Tarde' : 'Noche')][] = $h;
                    }
                @endphp
                <div class="ag-horas-wrap {{ $errors->has('hora_preferida') ? 'is-invalid' : '' }}" id="agHoras" data-limite-hoy="{{ $limiteHoy }}">
                    <p class="contact-form-eyebrow ag-horas-title">Elige la hora</p>
                    <p class="ag-horas-hint" id="agHorasHint">Primero selecciona una fecha en el calendario.</p>
                    @foreach($grupos as $grupo => $horas)
                        @continue(empty($horas))
                        <div class="ag-horas-grupo">
                            <span class="ag-horas-label">{{ $grupo }}</span>
                            <div class="ag-horas">
                                @foreach($horas as $h)
                                    <label class="ag-hora">
                                        <input type="radio" name="hora_preferida" value="{{ $h }}" {{ old('hora_preferida') === $h ? 'checked' : '' }} disabled>
                                        <span>{{ ltrim(substr($h, 0, 2), '0') . substr($h, 2) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
                @error('hora_preferida') <p class="ag-err">{{ $message }}</p> @enderror
            </div>

            <!-- Paso 2: datos -->
            <div class="contact-form-wrap">
                <div class="contact-form">
                    <p class="contact-form-eyebrow"><span class="ag-step">2</span> Tus datos</p>
                    <h3 class="contact-form-title">¿Lista para transformarte?</h3>

                    <div class="ag-resumen" id="agResumen" hidden>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span id="agResumenTexto"></span>
                    </div>

                    <div class="form-field">
                        <label for="nombre">Nombre completo</label>
                        <input type="text" id="nombre" name="nombre" value="{{ old('nombre') }}" placeholder="Tu nombre"
                               autocomplete="name" maxlength="150" required class="{{ $errors->has('nombre') ? 'is-invalid' : '' }}">
                        @error('nombre') <p class="ag-err">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label for="telefono">Teléfono / WhatsApp</label>
                        <div class="ag-phone {{ $errors->has('telefono') ? 'is-invalid' : '' }}">
                            <span class="ag-phone-prefix">+591</span>
                            <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="7xxxxxxx"
                                   inputmode="numeric" autocomplete="tel-national" maxlength="8" required>
                        </div>
                        @error('telefono') <p class="ag-err">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label for="servicio_id">Servicio</label>
                        <select id="servicio_id" name="servicio_id" required class="{{ $errors->has('servicio_id') ? 'is-invalid' : '' }}">
                            <option value="">Selecciona un servicio</option>
                            @foreach($servicios as $categoria => $items)
                                <optgroup label="{{ \App\Models\Servicio::CATEGORIAS[$categoria] ?? ucfirst(str_replace('_', ' ', $categoria)) }}">
                                    @foreach($items as $servicio)
                                        <option value="{{ $servicio->id }}" {{ (string) old('servicio_id') === (string) $servicio->id ? 'selected' : '' }}>
                                            {{ $servicio->nombre }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            @endforeach
                        </select>
                        @error('servicio_id') <p class="ag-err">{{ $message }}</p> @enderror
                    </div>

                    <div class="form-field">
                        <label for="consulta">Consulta</label>
                        <textarea id="consulta" name="consulta" rows="4" maxlength="1000" required
                                  placeholder="¿En qué sucursal te gustaría atenderte? ¿Algo que debamos saber?"
                                  class="{{ $errors->has('consulta') ? 'is-invalid' : '' }}">{{ old('consulta') }}</textarea>
                        @error('consulta') <p class="ag-err">{{ $message }}</p> @enderror
                    </div>

                    <button type="submit" class="submit-button ag-submit" id="agSubmit">Solicitar cita</button>
                    <p class="ag-note">Te confirmaremos tu cita por WhatsApp o llamada.</p>
                </div>
            </div>
        </form>
    </section>

    <script>
    (function () {
        var MESES = ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        var DIAS  = ['Domingo','Lunes','Martes','Miércoles','Jueves','Viernes','Sábado'];

        var cal     = document.getElementById('agCal');
        var grid    = document.getElementById('agCalGrid');
        var lblMes  = document.getElementById('agCalMonth');
        var btnPrev = document.getElementById('agCalPrev');
        var btnNext = document.getElementById('agCalNext');
        var input   = document.getElementById('fechaPreferida');
        var resumen = document.getElementById('agResumen');
        var resTxt  = document.getElementById('agResumenTexto');

        function parse(s) { var p = s.split('-'); return new Date(+p[0], p[1] - 1, +p[2]); }
        function iso(d) {
            return d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
        }

        var hoy = cal.dataset.hoy;
        var min = parse(cal.dataset.min);
        var max = parse(cal.dataset.max);
        var seleccion = cal.dataset.selected ? parse(cal.dataset.selected) : null;
        var vista = new Date((seleccion || min).getFullYear(), (seleccion || min).getMonth(), 1);

        function render() {
            lblMes.textContent = MESES[vista.getMonth()] + ' ' + vista.getFullYear();
            grid.innerHTML = '';

            // Lunes como primer día de la semana
            var offset = (vista.getDay() + 6) % 7;
            for (var i = 0; i < offset; i++) grid.appendChild(document.createElement('span'));

            var diasMes = new Date(vista.getFullYear(), vista.getMonth() + 1, 0).getDate();
            for (var d = 1; d <= diasMes; d++) {
                var fecha = new Date(vista.getFullYear(), vista.getMonth(), d);
                var btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ag-day';
                btn.textContent = d;
                btn.dataset.fecha = iso(fecha);

                var deshabilitado = fecha < min || fecha > max || fecha.getDay() === 0;
                if (deshabilitado) btn.disabled = true;
                if (iso(fecha) === hoy) btn.classList.add('is-today');
                if (seleccion && iso(fecha) === iso(seleccion)) btn.classList.add('is-selected');

                grid.appendChild(btn);
            }

            btnPrev.disabled = vista <= new Date(min.getFullYear(), min.getMonth(), 1);
            btnNext.disabled = vista >= new Date(max.getFullYear(), max.getMonth(), 1);
        }

        // Habilita las horas según la fecha elegida (hoy: solo las que respetan la anticipación)
        var horasWrap  = document.getElementById('agHoras');
        var horasHint  = document.getElementById('agHorasHint');
        var radiosHora = document.querySelectorAll('input[name="hora_preferida"]');

        function actualizarHoras() {
            var esHoy  = seleccion && iso(seleccion) === hoy;
            var limite = horasWrap.dataset.limiteHoy;

            radiosHora.forEach(function (r) {
                r.disabled = !seleccion || (esHoy && r.value < limite);
                if (r.disabled) r.checked = false;
            });

            horasHint.hidden = !!seleccion;
        }

        function actualizarResumen() {
            if (!seleccion) { resumen.hidden = true; return; }
            var hora  = document.querySelector('input[name="hora_preferida"]:checked');
            var texto = DIAS[seleccion.getDay()] + ' ' + seleccion.getDate() + ' de ' + MESES[seleccion.getMonth()].toLowerCase();
            if (hora) texto += ' · ' + hora.nextElementSibling.textContent + ' hrs';
            var srv = document.getElementById('servicio_id');
            if (srv.value) texto += ' · ' + srv.options[srv.selectedIndex].text.trim();
            resTxt.textContent = texto;
            resumen.hidden = false;
        }

        grid.addEventListener('click', function (e) {
            var btn = e.target.closest('.ag-day');
            if (!btn || btn.disabled) return;
            seleccion = parse(btn.dataset.fecha);
            input.value = btn.dataset.fecha;
            cal.classList.remove('is-invalid');
            render();
            actualizarHoras();
            actualizarResumen();
        });
        btnPrev.addEventListener('click', function () { vista.setMonth(vista.getMonth() - 1); render(); });
        btnNext.addEventListener('click', function () { vista.setMonth(vista.getMonth() + 1); render(); });
        radiosHora.forEach(function (r) {
            r.addEventListener('change', function () { horasWrap.classList.remove('is-invalid'); actualizarResumen(); });
        });
        document.getElementById('servicio_id').addEventListener('change', actualizarResumen);

        render();
        actualizarHoras();
        actualizarResumen();

        // Teléfono: solo dígitos; si pegan el número con +591 se lo quitamos
        var tel = document.getElementById('telefono');
        tel.addEventListener('input', function () {
            var v = tel.value.replace(/\D/g, '');
            if (v.length > 8 && v.indexOf('591') === 0) v = v.slice(3);
            tel.value = v.slice(0, 8);
        });

        document.getElementById('agendarForm').addEventListener('submit', function (e) {
            if (!input.value) {
                e.preventDefault();
                cal.classList.add('is-invalid');
                cal.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            if (!document.querySelector('input[name="hora_preferida"]:checked')) {
                e.preventDefault();
                horasWrap.classList.add('is-invalid');
                horasWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                return;
            }
            var btn = document.getElementById('agSubmit');
            btn.disabled = true;
            btn.textContent = 'Enviando…';
        });
    })();
    </script>

@endsection
