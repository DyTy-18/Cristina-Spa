{{--
    Filtrado en tiempo real para los formularios .table-filter del inventario.
    Al escribir (con pausa) o cambiar un select/fecha, pide la misma URL y reemplaza
    solo los resultados (lo que va después del form), sin recargar la página.
    Sin JS el form sigue funcionando con el botón "Filtrar".
--}}
@push('scripts')
<script>
(function () {
    document.querySelectorAll('form.table-filter[method="GET"], form.table-filter:not([method])').forEach(function (form) {
        var contenedor = form.parentElement;
        var timer = null;
        var peticion = 0;

        var boton = form.querySelector('button[type="submit"]');
        if (boton) boton.style.display = 'none';

        function resultados(raiz) {
            var nodos = [], visto = false;
            Array.prototype.forEach.call(raiz.children, function (el) {
                if (visto) nodos.push(el);
                if (el.matches('form.table-filter')) visto = true;
            });
            return nodos;
        }

        function cargar(url) {
            var actual = ++peticion;
            contenedor.style.opacity = '0.6';

            fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (r) { return r.text(); })
                .then(function (html) {
                    if (actual !== peticion) return; // llegó una respuesta más nueva
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var nuevoForm = doc.querySelector('form.table-filter[action="' + form.getAttribute('action') + '"]');
                    if (!nuevoForm) { window.location = url; return; }

                    resultados(contenedor).forEach(function (el) { el.remove(); });
                    resultados(nuevoForm.parentElement).forEach(function (el) {
                        contenedor.appendChild(document.importNode(el, true));
                    });

                    // Botón "Limpiar": aparece/desaparece según haya filtros.
                    form.querySelectorAll('a').forEach(function (a) { a.remove(); });
                    nuevoForm.querySelectorAll('a').forEach(function (a) {
                        form.appendChild(document.importNode(a, true));
                    });

                    history.replaceState(null, '', url);
                })
                .catch(function () { window.location = url; })
                .finally(function () { if (actual === peticion) contenedor.style.opacity = ''; });
        }

        function aplicar() {
            var params = new URLSearchParams(new FormData(form));
            Array.from(params.keys()).forEach(function (k) { if (params.get(k) === '') params.delete(k); });
            var base = form.getAttribute('action');
            cargar(base + (params.toString() ? '?' + params.toString() : ''));
        }

        form.addEventListener('submit', function (e) { e.preventDefault(); aplicar(); });
        form.addEventListener('change', function (e) {
            if (e.target.matches('select, input[type="date"]')) aplicar();
        });
        form.addEventListener('input', function (e) {
            if (!e.target.matches('input[type="text"], input[type="search"]')) return;
            clearTimeout(timer);
            timer = setTimeout(aplicar, 300);
        });

        // Paginación y "Limpiar" también sin recargar.
        contenedor.addEventListener('click', function (e) {
            var a = e.target.closest('a');
            if (!a || !a.href || a.target) return;
            var esPaginacion = a.closest('nav');
            var esLimpiar = form.contains(a);
            if (!esPaginacion && !esLimpiar) return;
            e.preventDefault();
            if (esLimpiar) {
                form.querySelectorAll('input[type="text"], input[type="search"], input[type="date"]').forEach(function (i) { i.value = ''; });
                form.querySelectorAll('select').forEach(function (s) { s.selectedIndex = 0; });
            }
            cargar(a.href);
        });
    });
})();
</script>
@endpush
