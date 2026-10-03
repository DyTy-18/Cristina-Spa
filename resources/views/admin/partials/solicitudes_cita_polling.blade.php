{{-- Notificaciones de solicitudes de cita (polling). Se incluye desde admin.layouts.app --}}
<style>
    .sol-nav-badge {
        background: #e74c3c; color: #fff; border-radius: 9999px;
        font-size: .62rem; font-weight: 700; padding: .1rem .42rem;
        margin-left: auto; line-height: 1.4;
    }
    .sol-bell-wrap { position: relative; display: inline-block; margin-right: .5rem; }
    .sol-bell {
        position: relative; background: none; border: 1px solid var(--border-color, rgba(0,0,0,.1));
        border-radius: 6px; padding: .3rem .55rem; font-size: 1rem; cursor: pointer; line-height: 1;
    }
    .sol-bell.ring { animation: solRing .9s ease 2; }
    @keyframes solRing {
        0%, 100% { transform: rotate(0); } 20% { transform: rotate(-15deg); }
        40% { transform: rotate(13deg); } 60% { transform: rotate(-9deg); } 80% { transform: rotate(5deg); }
    }
    .sol-bell-badge {
        position: absolute; top: -6px; right: -7px;
        background: #e74c3c; color: #fff; border-radius: 9999px;
        font-size: .6rem; font-weight: 700; padding: .08rem .38rem; line-height: 1.4;
    }
    .sol-bell-panel {
        display: none; position: absolute; right: 0; top: calc(100% + 8px); z-index: 1000;
        width: 320px; max-width: calc(100vw - 2rem);
        background: #fff; border: 1px solid rgba(0,0,0,.1); box-shadow: 0 10px 30px rgba(0,0,0,.15);
    }
    .sol-bell-panel.open { display: block; }
    .sol-bell-head {
        display: flex; justify-content: space-between; padding: .7rem 1rem;
        font-size: .75rem; text-transform: uppercase; letter-spacing: 1px;
        border-bottom: 1px solid rgba(0,0,0,.07); color: var(--text-light);
    }
    .sol-bell-list { max-height: 340px; overflow-y: auto; }
    .sol-bell-item {
        display: block; padding: .7rem 1rem; border-bottom: 1px solid rgba(0,0,0,.05);
        text-decoration: none; color: var(--text-dark); font-size: .8rem;
    }
    .sol-bell-item:hover { background: var(--light-bg); }
    .sol-bell-item strong { font-weight: 500; }
    .sol-bell-item small { display: block; color: var(--text-light); margin-top: .15rem; }
    .sol-bell-empty { padding: 1rem; font-size: .8rem; color: var(--text-light); text-align: center; }
    .sol-bell-all {
        display: block; text-align: center; padding: .6rem; font-size: .75rem;
        text-transform: uppercase; letter-spacing: 1px; color: var(--secondary-color); text-decoration: none;
    }

    .sol-toasts { position: fixed; right: 1rem; bottom: 1rem; z-index: 2000; display: flex; flex-direction: column; gap: .6rem; }
    .sol-toast {
        width: 320px; max-width: calc(100vw - 2rem);
        background: #2c2c2c; color: #fff; padding: .85rem 1rem; border-left: 3px solid var(--accent-color);
        box-shadow: 0 10px 30px rgba(0,0,0,.25); font-size: .8rem; cursor: pointer;
        animation: solToastIn .3s ease;
    }
    .sol-toast-title { font-size: .65rem; letter-spacing: 1.5px; text-transform: uppercase; color: var(--accent-color); margin-bottom: .3rem; }
    .sol-toast p { margin: .15rem 0 0; color: rgba(255,255,255,.75); }
    @keyframes solToastIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: none; } }
</style>

<div class="sol-toasts" id="solToasts"></div>

<script>
(function () {
    var URL_POLL  = @json(route('admin.solicitudes-cita.notificaciones'));
    var URL_INDEX = @json(route('admin.solicitudes-cita.index', ['estado' => 'pendiente']));
    // 10 s en la vista de solicitudes (se refresca la lista), 20 s en el resto del panel
    var INTERVALO = document.getElementById('solLive') ? 10000 : 20000;
    var STORAGE   = 'solCitaUltimoId';

    var bell     = document.getElementById('solBell');
    var panel    = document.getElementById('solBellPanel');
    var list     = document.getElementById('solBellList');
    var toasts   = document.getElementById('solToasts');
    var badges   = [document.getElementById('solBellBadge'), document.getElementById('solCitaNavBadge')];
    var lblPend  = document.getElementById('solBellPendientes');
    var timer    = null;
    var cargando = false;

    function leerUltimo() { try { return parseInt(localStorage.getItem(STORAGE) || '0', 10) || 0; } catch (e) { return 0; } }
    function guardarUltimo(id) { try { localStorage.setItem(STORAGE, String(id)); } catch (e) {} }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    function actualizarContador(n) {
        badges.forEach(function (b) {
            if (!b) return;
            b.textContent = n;
            b.hidden = n < 1;
        });
        if (lblPend) lblPend.textContent = n + ' pendiente' + (n === 1 ? '' : 's');
        document.title = document.title.replace(/^\(\d+\)\s*/, '');
        if (n > 0) document.title = '(' + n + ') ' + document.title;
    }

    function sonar() {
        try {
            var Ctx = window.AudioContext || window.webkitAudioContext;
            var ctx = new Ctx();
            [880, 1175].forEach(function (f, i) {
                var o = ctx.createOscillator(), g = ctx.createGain();
                o.frequency.value = f; o.type = 'sine';
                g.gain.setValueAtTime(0.0001, ctx.currentTime + i * 0.18);
                g.gain.exponentialRampToValueAtTime(0.2, ctx.currentTime + i * 0.18 + 0.02);
                g.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + i * 0.18 + 0.3);
                o.connect(g); g.connect(ctx.destination);
                o.start(ctx.currentTime + i * 0.18); o.stop(ctx.currentTime + i * 0.18 + 0.32);
            });
        } catch (e) {}
    }

    function mostrarToast(s) {
        var el = document.createElement('div');
        el.className = 'sol-toast';
        el.innerHTML = '<div class="sol-toast-title">🔔 Nueva solicitud de cita</div>'
            + '<strong>' + esc(s.nombre) + '</strong> · ' + esc(s.telefono)
            + (s.servicio ? '<p>✂️ ' + esc(s.servicio) + '</p>' : '')
            + '<p>📅 ' + esc(s.fecha) + '</p>'
            + '<p>' + esc(s.consulta) + '</p>';
        el.addEventListener('click', function () { location.href = URL_INDEX + '#sol-' + s.id; });
        toasts.appendChild(el);
        setTimeout(function () { el.remove(); }, 12000);
    }

    function notificarNavegador(s) {
        if (!('Notification' in window) || Notification.permission !== 'granted') return;
        var n = new Notification('Nueva solicitud de cita — ' + s.nombre, { body: (s.servicio ? s.servicio + ' · ' : '') + s.fecha + ' · ' + s.telefono + '\n' + s.consulta, tag: 'sol-' + s.id });
        n.onclick = function () { window.focus(); location.href = URL_INDEX + '#sol-' + s.id; };
    }

    function agregarALista(s) {
        var vacio = list.querySelector('.sol-bell-empty');
        if (vacio) vacio.remove();
        var a = document.createElement('a');
        a.className = 'sol-bell-item';
        a.href = URL_INDEX + '#sol-' + s.id;
        a.innerHTML = '<strong>' + esc(s.nombre) + '</strong> · ' + esc(s.telefono)
            + (s.servicio ? '<small>✂️ ' + esc(s.servicio) + '</small>' : '')
            + '<small>📅 ' + esc(s.fecha) + '</small><small>' + esc(s.consulta) + '</small><small>' + esc(s.hace) + '</small>';
        list.insertBefore(a, list.firstChild);
    }

    function poll() {
        if (cargando) return;
        cargando = true;
        var desde = leerUltimo();

        fetch(URL_POLL + (desde ? '?desde=' + desde : ''), {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
        .then(function (r) {
            if (r.status === 401 || r.status === 419) { detener(); return null; } // sesión expirada
            return r.ok ? r.json() : null;
        })
        .then(function (data) {
            if (!data) return;
            actualizarContador(data.pendientes);
            document.dispatchEvent(new CustomEvent('solicitudes:poll', { detail: data }));

            // Primera vez en este navegador: solo fijamos la línea base, sin notificar el histórico
            if (!desde) { guardarUltimo(data.ultimo_id); return; }

            if (data.nuevas.length) {
                data.nuevas.forEach(function (s) {
                    agregarALista(s);
                    mostrarToast(s);
                    notificarNavegador(s);
                });
                sonar();
                bell.classList.remove('ring'); void bell.offsetWidth; bell.classList.add('ring');
            }
            if (data.ultimo_id > desde) guardarUltimo(data.ultimo_id);
        })
        .catch(function () {})
        .finally(function () { cargando = false; });
    }

    function iniciar() { if (!timer) { poll(); timer = setInterval(poll, INTERVALO); } }
    function detener() { clearInterval(timer); timer = null; }

    // Pausar el polling cuando la pestaña no está visible para no cargar el servidor
    document.addEventListener('visibilitychange', function () {
        document.hidden ? detener() : iniciar();
    });

    bell.addEventListener('click', function (e) {
        e.stopPropagation();
        panel.classList.toggle('open');
        // Pedimos permiso de notificaciones del navegador tras una interacción del usuario
        if ('Notification' in window && Notification.permission === 'default') Notification.requestPermission();
    });
    document.addEventListener('click', function (e) {
        if (!e.target.closest('#solBellWrap')) panel.classList.remove('open');
    });

    if (!document.hidden) iniciar();
})();
</script>
