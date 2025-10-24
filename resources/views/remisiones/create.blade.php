<x-app-layout>
    <!-- jQuery (Select2 depende de jQuery) -->
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


    <div class="max-w-5xl mx-auto p-8 bg-white shadow-md rounded-xl mt-6">
        <h1 class="text-2xl font-bold text-primary mb-6 flex items-center">
            <x-heroicon-o-document-plus class="w-7 h-7 mr-2 text-primary" />
            Registrar nueva remisión
        </h1>

        @if (session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-2 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">¡Ups!</strong>
                <span class="block">Hay algunos errores con los datos ingresados:</span>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('remisiones.store') }}" class="space-y-6">
            @csrf

            {{-- Cliente --}}
            <div class="mb-4">

                <label for="cliente_id" class="block text-sm font-medium text-gray-700">Cliente</label>
                <div class="flex space-x-2">
                    <select id="cliente_id" name="cliente_id" class="w-full border-gray-300 rounded-md shadow-sm">
                        <option value="">Seleccione un cliente</option>
                        @foreach ($clientes as $cliente)
                            <option value="{{ $cliente->id }}">
                                {{ $cliente->persona->tipo_persona === 'natural' ? $cliente->persona->nombre_completo : $cliente->persona->razon_social }}
                                - {{ $cliente->persona->numero_documento }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Botón para crear nuevo cliente -->
                    <button type="button" id="btnNuevoCliente"
                        class="bg-secondary text-white px-3 py-2 rounded-md hover:bg-green-700 transition">
                        <x-heroicon-o-plus class="w-5 h-5 inline" />
                    </button>
                </div>




            </div>



            {{-- Entrega de resultados --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700">Entrega de resultados</label>
                <select name="entrega_resultados" class="w-full border-gray-300 rounded-md shadow-sm">
                    <option value="">Seleccione una opción</option>
                    <option value="correo">Correo electrónico</option>
                    <option value="personal">Entrega personal</option>
                    <option value="ambos">Ambos</option>
                </select>
            </div>

            {{-- Observaciones generales --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700">Observaciones</label>
                <textarea name="observaciones" rows="3"
                    class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary focus:border-primary"></textarea>
            </div>

            <div id="muestrasContainer" class="space-y-4">
                <div class="muestra-item p-4 border border-gray-300 rounded-md bg-gray-50 flex flex-col gap-3">
                    <div class="flex flex-col md:flex-row md:gap-4">
                        <div class="flex-1">
                            <label class="block text-sm text-gray-600">Código Cliente</label>
                            <input type="text" name="muestras[0][codigo_cliente]"
                                class="w-full border-gray-300 rounded-md p-2">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm text-gray-600">Código Interno</label>
                            <input type="text" name="muestras[0][codigo_interno]" value="A-001" readonly
                                class="w-full border-gray-300 rounded-md p-2 bg-gray-100">
                        </div>
                    </div>

                    <div class="flex flex-col md:flex-row md:gap-4">
                        <div class="flex-1">
                            <label class="block text-sm text-gray-600">Tipo de Muestra</label>
                            <select name="muestras[0][tipo_muestra_id]"
                                class="tipo-muestra w-full border-gray-300 rounded-md p-2">
                                <option value="">Seleccione</option>
                                @foreach ($tipoMuestras as $tipo)
                                    <option value="{{ $tipo->id }}" data-ensayos='@json($tipo->ensayos)'>
                                        {{ $tipo->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-4">
                            <label for="cantidad" class="block text-sm font-medium text-gray-700">
                                Cantidad (masa o volumen)
                            </label>
                            <input type="text" id="cantidad" name="muestras[0][cantidad]"
                                placeholder="Ej: 250 ml, 2 g, 1 L..."
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-primary focus:ring focus:ring-primary focus:ring-opacity-50" />
                        </div>

                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Ensayos</label>
                        <select name="muestras[0][ensayos][]" class="ensayos w-full border-gray-300 rounded-md p-2"
                            multiple></select>
                    </div>

                    <div>
                        <label class="block text-sm text-gray-600">Condiciones / Observaciones</label>
                        <input type="text" name="muestras[0][condiciones]"
                            class="w-full border-gray-300 rounded-md p-2">
                    </div>

                    <div class="text-right">
                        <button type="button"
                            class="remove-muestra hidden px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">Eliminar</button>
                    </div>
                </div>
            </div>

            <button type="button" id="addMuestra"
                class="mt-4 px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">Agregar Muestra</button>

            <div class="mt-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="accept_terms" v-model="form.accept_terms" class="w-4 h-4 text-primary">
                    <span>El cliente acepta todos los términos y condiciones del laboratorio.</span>
                </label>
            </div>

            {{-- Botón enviar --}}
            <div class="flex justify-end mt-8">
                <x-primary-button>
                    <x-heroicon-o-check class="w-5 h-5 mr-1" />
                    Guardar remisión
                </x-primary-button>
            </div>
        </form>
        <!-- Modal Crear Cliente -->
        <!-- Modal Crear Cliente -->
        <div id="modalCliente"
            class="{{ $errors->any() ? '' : 'hidden' }} fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg shadow-lg w-full max-w-2xl p-6 relative">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Registrar nuevo cliente</h2>

                <form id="formCliente" action="{{ route('clientes.store') }}" method="POST" class="space-y-4">
                    @csrf

                    {{-- Mostrar errores --}}
                    @if ($errors->any())
                        <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
                            <strong class="font-bold">¡Ups! Hay errores:</strong>
                            <ul class="list-disc list-inside text-sm mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Tipo de persona -->
                        <div>
                            <label class="text-sm text-gray-600">Tipo de persona</label>
                            <select name="tipo_persona" id="tipo_persona"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                <option value="">Seleccione</option>
                                <option value="natural" {{ old('tipo_persona') == 'natural' ? 'selected' : '' }}>Natural
                                </option>
                                <option value="juridica" {{ old('tipo_persona') == 'juridica' ? 'selected' : '' }}>
                                    Jurídica</option>
                            </select>
                        </div>

                        <!-- Tipo de cliente -->
                        <div>
                            <label class="text-sm text-gray-600">Tipo de cliente</label>
                            <select name="tipo_cliente"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                <option value="externo" {{ old('tipo_cliente') == 'externo' ? 'selected' : '' }}>Externo
                                </option>
                                <option value="interno" {{ old('tipo_cliente') == 'interno' ? 'selected' : '' }}>Interno
                                </option>
                            </select>
                        </div>

                        <!-- Tipo de documento -->
                        <div>
                            <label class="text-sm text-gray-600">Tipo de documento</label>
                            <select name="tipo_documento" id="tipo_documento"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                <option value="">Seleccione tipo de persona primero</option>
                            </select>
                        </div>

                        <!-- Número de documento -->
                        <div>
                            <label class="text-sm text-gray-600">Número de documento</label>
                            <input type="text" name="numero_documento" id="numero_documento"
                                value="{{ old('numero_documento') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Nombre completo o razón social -->
                        <div id="campoNombreNatural"
                            class="{{ old('tipo_persona') == 'juridica' ? 'hidden' : '' }} col-span-2">
                            <label class="text-sm text-gray-600">Nombre completo</label>
                            <input type="text" name="nombre_completo" value="{{ old('nombre_completo') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>

                        <div id="campoRazonSocial"
                            class="{{ old('tipo_persona') == 'natural' || !old('tipo_persona') ? 'hidden' : '' }} col-span-2">
                            <label class="text-sm text-gray-600">Razón social</label>
                            <input type="text" name="razon_social" value="{{ old('razon_social') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Contacto -->
                        <div>
                            <label class="text-sm text-gray-600">Correo electrónico</label>
                            <input type="email" name="correo_electronico" value="{{ old('correo_electronico') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Teléfono</label>
                            <input type="text" name="telefono" value="{{ old('telefono') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>

                        <!-- Ubicación -->
                        <div>
                            <label class="text-sm text-gray-600">Departamento</label>
                            <select name="departamento_id" id="departamento_id"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                <option value="">Seleccione</option>
                                @foreach ($departamentos as $dpto)
                                    <option value="{{ $dpto->id }}"
                                        {{ old('departamento_id') == $dpto->id ? 'selected' : '' }}>{{ $dpto->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-sm text-gray-600">Municipio</label>
                            <select name="municipio_id" id="municipio_id"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                                <option value="">Seleccione un departamento primero</option>
                            </select>
                        </div>

                        <div class="col-span-2">
                            <label class="text-sm text-gray-600">Dirección</label>
                            <input type="text" name="direccion" value="{{ old('direccion') }}"
                                class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                        </div>
                    </div>

                    <!-- Botones -->
                    <div class="flex justify-end space-x-2 pt-4">
                        <button type="button" id="cancelarCliente"
                            class="px-3 py-2 rounded-md bg-gray-200 hover:bg-gray-300">Cancelar</button>
                        <button type="submit"
                            class="px-3 py-2 rounded-md bg-primary text-white hover:bg-blue-800">Guardar</button>
                    </div>
                </form>
            </div>
        </div>

    

    </div>


    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const formRemision = document.querySelector('form[action="{{ route('remisiones.store') }}"]');
            const checkTerms = document.getElementById('accept_terms');

            formRemision.addEventListener('submit', function(e) {
                if (!checkTerms.checked) {
                    e.preventDefault(); // Evita el envío
                    alert('⚠️ Debes aceptar los términos y condiciones antes de guardar la remisión.');
                    return false;
                }
            });
        });
    </script>




</x-app-layout>



<script>
    document.addEventListener('DOMContentLoaded', function() {


        // Inicializar Select2
        $('#cliente_id').select2({
            placeholder: "Seleccione o escriba un cliente",
            allowClear: true,
            width: '100%'
        });

    });
</script>




<script>
    document.addEventListener('DOMContentLoaded', () => {
        let index = 1;
        const container = document.getElementById('muestrasContainer');
        const addBtn = document.getElementById('addMuestra');

        function actualizarEnsayos(fila) {
            const selectMuestra = fila.querySelector('.tipo-muestra');
            const selectEnsayos = fila.querySelector('.ensayos');

            selectEnsayos.innerHTML = '';

            const selected = selectMuestra.selectedOptions[0];
            if (selected && selected.dataset.ensayos) {
                const ensayos = JSON.parse(selected.dataset.ensayos);
                ensayos.forEach(e => {
                    const opt = document.createElement('option');
                    opt.value = e.id;
                    opt.textContent = e.nombre;
                    selectEnsayos.appendChild(opt);
                });
            }
        }

        // Actualizar la primera fila
        actualizarEnsayos(container.querySelector('.muestra-item'));

        container.addEventListener('change', e => {
            if (e.target.classList.contains('tipo-muestra')) {
                const fila = e.target.closest('.muestra-item');
                actualizarEnsayos(fila);
            }
        });

        addBtn.addEventListener('click', () => {
            const firstItem = container.querySelector('.muestra-item');
            const newItem = firstItem.cloneNode(true);

            // Limpiar inputs y selects
            newItem.querySelectorAll('input').forEach(input => input.value = '');
            newItem.querySelectorAll('select').forEach(select => select.selectedIndex = -1);
            newItem.querySelector('.remove-muestra').classList.remove('hidden');

            // Actualizar nombres para el store
            newItem.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                el.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
            });

            // Código interno automático
            newItem.querySelector('input[name*="codigo_interno"]').value = String.fromCharCode(65 +
                index) + '-001';

            container.appendChild(newItem);
            index++;
        });

        container.addEventListener('click', e => {
            if (e.target.classList.contains('remove-muestra')) {
                e.target.closest('.muestra-item').remove();
            }
        });
    });



    document.addEventListener('DOMContentLoaded', function() {
        const btnAgregar = document.getElementById('agregarMuestra');
        const contenedor = document.getElementById('contenedorMuestras');
        let index = 1;

        // --- Agregar nueva muestra ---
        btnAgregar.addEventListener('click', () => {
            const nueva = contenedor.querySelector('.muestra').cloneNode(true);
            nueva.querySelectorAll('input, select').forEach(el => {
                const name = el.getAttribute('name');
                if (name) el.setAttribute('name', name.replace(/\[\d+\]/, `[${index}]`));
                if (el.tagName === 'INPUT') el.value = '';
            });

            nueva.querySelector('.lista-ensayos').innerHTML =
                '<p class="text-gray-400 text-sm">Seleccione tipo de muestra primero</p>';

            contenedor.appendChild(nueva);
            index++;
        });

        // --- Filtrar ensayos por tipo de muestra ---
        document.addEventListener('change', async function(e) {
            if (e.target.classList.contains('tipo-muestra')) {
                const tipoId = e.target.value;
                const lista = e.target.closest('.muestra').querySelector('.lista-ensayos');

                lista.innerHTML = '<p class="text-gray-400 text-sm">Cargando ensayos...</p>';

                if (!tipoId) {
                    lista.innerHTML =
                        '<p class="text-gray-400 text-sm">Seleccione tipo de muestra primero</p>';
                    return;
                }

                try {
                    const resp = await fetch(`/api/ensayos/${tipoId}`);
                    const ensayos = await resp.json();
                    lista.innerHTML = '';

                    ensayos.forEach(e => {
                        const label = document.createElement('label');
                        label.classList.add('flex', 'items-center', 'space-x-2');
                        label.innerHTML =
                            `<input type="checkbox" name="muestras[${index - 1}][ensayos][]" value="${e.id}" class="text-primary rounded border-gray-300 focus:ring-primary"> <span>${e.nombre}</span>`;
                        lista.appendChild(label);
                    });
                } catch (error) {
                    lista.innerHTML =
                        '<p class="text-red-500 text-sm">Error al cargar ensayos</p>';
                }
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const departamentoSelect = document.getElementById('departamento_id');
        const municipioSelect = document.getElementById('municipio_id');

        departamentoSelect.addEventListener('change', async function() {
            const departamentoId = this.value;
            municipioSelect.innerHTML = '<option value="">Cargando...</option>';

            if (!departamentoId) {
                municipioSelect.innerHTML =
                    '<option value="">Seleccione un departamento primero</option>';
                return;
            }

            try {
                const response = await fetch(`/api/municipios/${departamentoId}`);
                const municipios = await response.json();

                municipioSelect.innerHTML = '<option value="">Seleccione</option>';
                municipios.forEach(m => {
                    const option = document.createElement('option');
                    option.value = m.id;
                    option.textContent = m.nombre;
                    municipioSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error al cargar municipios:', error);
                municipioSelect.innerHTML = '<option value="">Error al cargar</option>';
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('modalCliente');
        const btnNuevo = document.getElementById('btnNuevoCliente');
        const cancelar = document.getElementById('cancelarCliente');
        const form = document.getElementById('formCliente');

        // Abrir modal
        btnNuevo.addEventListener('click', () => modal.classList.remove('hidden'));

        // Cerrar modal
        cancelar.addEventListener('click', () => modal.classList.add('hidden'));

        // Mostrar campos según tipo de persona
        const tipoPersona = document.getElementById('tipo_persona');
        tipoPersona.addEventListener('change', function() {
            const natural = document.getElementById('campoNombreNatural');
            const juridica = document.getElementById('campoRazonSocial');
            if (this.value === 'natural') {
                natural.classList.remove('hidden');
                juridica.classList.add('hidden');
            } else if (this.value === 'juridica') {
                juridica.classList.remove('hidden');
                natural.classList.add('hidden');
            } else {
                natural.classList.add('hidden');
                juridica.classList.add('hidden');
            }

            // Actualizar tipo de documento
            const tipoDocumento = document.getElementById('tipo_documento');
            const opciones = {
                natural: [{
                        value: 'cc',
                        text: 'Cédula de ciudadanía'
                    },
                    {
                        value: 'ce',
                        text: 'Cédula de extranjería'
                    },
                    {
                        value: 'pe',
                        text: 'Permiso especial de permanencia'
                    }
                ],
                juridica: [{
                    value: 'nit',
                    text: 'NIT'
                }]
            };
            tipoDocumento.innerHTML = '';
            if (opciones[this.value]) {
                opciones[this.value].forEach(opt => {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.text;
                    tipoDocumento.appendChild(option);
                });
            } else {
                tipoDocumento.innerHTML =
                    '<option value="">Seleccione tipo de persona primero</option>';
            }
        });
    });
</script>
