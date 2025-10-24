<div class="bg-white rounded-lg shadow-md p-6">
    <h2 class="text-lg font-semibold text-gray-800 mb-4">
        {{ isset($cliente) ? 'Editar cliente' : 'Registrar nuevo cliente' }}
    </h2>

    <form id="formCliente"
        action="{{ isset($cliente) ? route('clientes.update', $cliente->id) : route('clientes.store') }}" method="POST"
        class="space-y-4">
        @csrf
        @if (isset($cliente))
            @method('PUT')
        @endif

        <div class="grid grid-cols-2 gap-4">
            <!-- Tipo de persona -->
            <div>
                <label class="text-sm text-gray-600">Tipo de persona</label>
                <select name="tipo_persona" id="tipo_persona"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <option value="">Seleccione</option>
                    <option value="natural"
                        {{ old('tipo_persona', $cliente->persona->tipo_persona ?? '') == 'natural' ? 'selected' : '' }}>
                        Natural</option>
                    <option value="juridica"
                        {{ old('tipo_persona', $cliente->persona->tipo_persona ?? '') == 'juridica' ? 'selected' : '' }}>
                        Jurídica</option>
                </select>
            </div>

            <!-- Tipo de cliente -->
            <div>
                <label class="text-sm text-gray-600">Tipo de cliente</label>
                <select name="tipo_cliente"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
                    <option value="externo"
                        {{ old('tipo_cliente', $cliente->tipo_cliente ?? '') == 'externo' ? 'selected' : '' }}>Externo
                    </option>
                    <option value="interno"
                        {{ old('tipo_cliente', $cliente->tipo_cliente ?? '') == 'interno' ? 'selected' : '' }}>Interno
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
                    value="{{ old('numero_documento', $cliente->persona->numero_documento ?? '') }}"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
            </div>

            <!-- Nombre completo o razón social -->
            <div id="campoNombreNatural"
                class="col-span-2 {{ old('tipo_persona', $cliente->persona->tipo_persona ?? '') == 'juridica' ? 'hidden' : '' }}">
                <label class="text-sm text-gray-600">Nombre completo</label>
                <input type="text" name="nombre_completo"
                    value="{{ old('nombre_completo', $cliente->persona->nombre_completo ?? '') }}"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
            </div>

            <div id="campoRazonSocial"
                class="col-span-2 {{ old('tipo_persona', $cliente->persona->tipo_persona ?? '') == 'natural' ? 'hidden' : '' }}">
                <label class="text-sm text-gray-600">Razón social</label>
                <input type="text" name="razon_social"
                    value="{{ old('razon_social', $cliente->razon_social ?? '') }}"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
            </div>

            <!-- Contacto -->
            <div>
                <label class="text-sm text-gray-600">Correo electrónico</label>
                <input type="email" name="correo_electronico"
                    value="{{ old('correo_electronico', $cliente->correo_electronico ?? '') }}"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
            </div>

            <div>
                <label class="text-sm text-gray-600">Teléfono</label>
                <input type="text" name="telefono" value="{{ old('telefono', $cliente->telefono ?? '') }}"
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
                            {{ old('departamento_id', $cliente->departamento_id ?? '') == $dpto->id ? 'selected' : '' }}>
                            {{ $dpto->nombre }}
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
                <input type="text" name="direccion" value="{{ old('direccion', $cliente->direccion ?? '') }}"
                    class="w-full border-gray-300 rounded-md focus:ring-primary focus:border-primary">
            </div>
        </div>

        <!-- Botones -->
        <div class="flex justify-end space-x-2 pt-4">
            <a href="{{ route('clientes.index') }}"
                class="px-3 py-2 rounded-md bg-gray-200 hover:bg-gray-300">Cancelar</a>
            <button type="submit" class="px-3 py-2 rounded-md bg-primary text-white hover:bg-blue-800">
                {{ isset($cliente) ? 'Actualizar' : 'Guardar' }}
            </button>
        </div>

        @if ($errors->any())
            <div class="bg-red-100 text-red-800 px-4 py-2 rounded mb-4">
                <ul class="list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </form>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const departamentoSelect = document.getElementById('departamento_id');
        const municipioSelect = document.getElementById('municipio_id');

        departamentoSelect.addEventListener('change', async () => {
            const departamentoId = departamentoSelect.value;
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
                municipios.forEach(mun => {
                    const selected =
                        "{{ old('municipio_id', $cliente->municipio_id ?? '') }}" == mun
                        .id ? 'selected' : '';
                    municipioSelect.innerHTML +=
                        `<option value="${mun.id}" ${selected}>${mun.nombre}</option>`;
                });
            } catch (error) {
                municipioSelect.innerHTML = '<option value="">Error al cargar</option>';
                console.error(error);
            }
        });

        // ⚙️ Cargar municipios automáticamente si ya hay un departamento seleccionado (modo edición)
        if (departamentoSelect.value) {
            const event = new Event('change');
            departamentoSelect.dispatchEvent(event);
        }
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', () => {
        const tipoPersona = document.getElementById('tipo_persona');
        const tipoDocumento = document.getElementById('tipo_documento');
        const campoNatural = document.getElementById('campoNombreNatural');
        const campoJuridica = document.getElementById('campoRazonSocial');

        // Valor anterior (old) o existente (en modo edición)
        const oldTipoPersona = "{{ old('tipo_persona', $cliente->persona->tipo_persona ?? '') }}";
        const oldTipoDocumento = "{{ old('tipo_documento', $cliente->persona->tipo_documento ?? '') }}";

        const cargarTiposDocumento = () => {
            const valor = tipoPersona.value;
            tipoDocumento.innerHTML = '<option value="">Seleccione</option>';

            if (valor === 'natural') {
                const tipos = [{
                        id: 'cc',
                        nombre: 'Cédula de ciudadanía'
                    },

                    {
                        id: 'ce',
                        nombre: 'Cédula de extranjería'
                    },
                ];

                tipos.forEach(t => {
                    const selected = oldTipoDocumento === t.id ? 'selected' : '';
                    tipoDocumento.innerHTML +=
                        `<option value="${t.id}" ${selected}>${t.nombre}</option>`;
                });

                campoNatural.classList.remove('hidden');
                campoJuridica.classList.add('hidden');
            } else if (valor === 'juridica') {
                const selected = oldTipoDocumento === 'NIT' ? 'selected' : '';
                tipoDocumento.innerHTML += `<option value="NIT" ${selected}>NIT</option>`;
                campoJuridica.classList.remove('hidden');
                campoNatural.classList.add('hidden');
            } else {
                tipoDocumento.innerHTML = '<option value="">Seleccione tipo de persona primero</option>';
                campoNatural.classList.add('hidden');
                campoJuridica.classList.add('hidden');
            }
        };

        tipoPersona.addEventListener('change', cargarTiposDocumento);

        // Cargar automáticamente si ya hay un valor (old o edición)
        if (oldTipoPersona) {
            tipoPersona.value = oldTipoPersona;
            cargarTiposDocumento();
        }
    });

   
</script>
