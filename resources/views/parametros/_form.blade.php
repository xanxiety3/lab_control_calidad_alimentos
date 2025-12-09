<x-app-layout>
<style>/* Contenedor principal */
.form-container {
    max-width: 600px;
    margin: 2rem auto;
    padding: 2rem;
    background-color: #ffffff;
    border-radius: 1rem;
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
    border: 1px solid #e5e7eb;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Encabezado */
.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
}

.form-header h2 {
    font-size: 1.75rem;
    font-weight: bold;
    color: #1f2937;
}

.form-header a {
    color: #6b7280;
    text-decoration: none;
    transition: color 0.3s;
}

.form-header a:hover {
    color: #2563eb;
}

/* Errores */
.alert-error {
    background-color: #fee2e2;
    border: 1px solid #fca5a5;
    color: #b91c1c;
    padding: 1rem;
    border-radius: 0.5rem;
    margin-bottom: 1.5rem;
    font-size: 0.9rem;
}

.alert-error ul {
    margin: 0;
    padding-left: 1.2rem;
}

/* Grid del formulario */
.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
    margin-bottom: 1rem;
}

.form-group label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #374151;
}

/* Inputs y select */
.form-group input,
.form-group select {
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
    font-size: 1rem;
    color: #111827;
    transition: border 0.3s, box-shadow 0.3s;
}

.form-group input:focus,
.form-group select:focus {
    outline: none;
    border-color: #2563eb;
    box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
}

.form-group input[readonly] {
    background-color: #f3f4f6;
    cursor: not-allowed;
}

/* Botones */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
}

.btn-cancel {
    background-color: #e5e7eb;
    color: #374151;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: background 0.3s;
}

.btn-cancel:hover {
    background-color: #d1d5db;
}

.btn-submit {
    background-color: #2563eb;
    color: #ffffff;
    padding: 0.5rem 1.25rem;
    border-radius: 0.5rem;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.3s;
}

.btn-submit:hover {
    background-color: #1d4ed8;
}

/* Responsive: en móvil que el grid sea una columna */
@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
<div class="form-container">

    <!-- ENCABEZADO -->
    <div class="form-header">
        <h2>
            {{ isset($parametro) ? 'Editar Parámetro de Corrección' : 'Nuevo Parámetro de Corrección' }}
        </h2>
        <a href="{{ route('parametros.index') }}">← Volver a la lista</a>
    </div>

    <!-- ERRORES -->
    @if ($errors->any())
        <div class="alert-error">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- FORMULARIO -->
    <form action="{{ isset($parametro) ? route('parametros.update', $parametro) : route('parametros.store') }}" method="POST">
        @csrf
        @if(isset($parametro))
            @method('PUT')
        @endif

        <div class="form-grid">

            <!-- TIPO -->
            <div class="form-group">
                <label>Tipo</label>

                @if (isset($parametro))
                    <input type="text" value="{{ ucfirst($parametro->tipo) }}" readonly>
                    <input type="hidden" name="tipo" value="{{ $parametro->tipo }}">
                @else
                    <select name="tipo" required>
                        <option value="">Seleccione tipo...</option>
                        @if (!$tieneTemperatura)
                            <option value="temperatura">Temperatura</option>
                        @endif
                        @if (!$tieneHumedad)
                            <option value="humedad">Humedad</option>
                        @endif
                    </select>
                @endif
            </div>

            <!-- VALOR 1 -->
            <div class="form-group">
                <label>Valor 1</label>
                <input type="number" step="any" name="valor_1" value="{{ old('valor_1', $parametro->valor_1 ?? '') }}">
            </div>

            <!-- VALOR 2 -->
            <div class="form-group">
                <label>Valor 2</label>
                <input type="number" step="any" name="valor_2" value="{{ old('valor_2', $parametro->valor_2 ?? '') }}">
            </div>

            <!-- VALOR 3 -->
            <div class="form-group">
                <label>Valor 3</label>
                <input type="number" step="any" name="valor_3" value="{{ old('valor_3', $parametro->valor_3 ?? '') }}">
            </div>

        </div>

        <!-- BOTONES -->
        <div class="form-actions">
            <a href="{{ route('parametros.index') }}" class="btn-cancel">Cancelar</a>
            <button type="submit" class="btn-submit">{{ isset($parametro) ? 'Actualizar' : 'Guardar' }}</button>
        </div>

    </form>
</div>
</x-app-layout>