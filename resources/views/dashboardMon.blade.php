<x-app-layout>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Ambiental</title>

    <!-- ESTILOS PUROS -->
    <style>
        
        body {
            background: #f4f5f7;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        header {
            background: white;
            box-shadow: 0px 2px 6px rgba(0,0,0,0.1);
            padding: 20px;
            margin-bottom: 25px;
        }

        .header-flex {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .container {
            width: 95%;
            max-width: 1400px;
            margin: auto;
        }

        h2 {
            font-size: 26px;
            font-weight: bold;
            color: #333;
        }

        /* Tarjetas */
        .card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            border: 1px solid #e5e5e5;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.05);
        }

        /* Grid principal */
        .grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 25px;
        }

        @media(min-width: 992px){
            .grid{
                grid-template-columns: 2fr 1fr;
            }
        }

        .input-group{
            display: flex;
            flex-direction: column;
            margin-bottom: 18px;
        }

        .input-group input{
            padding: 10px;
            font-size: 18px;
            border: 2px solid #ccc;
            border-radius: 8px;
            transition: 0.2s;
        }

        .input-group input:focus{
            border-color: #3b82f6;
            outline: none;
        }

        .button{
            padding: 12px 25px;
            border-radius: 6px;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
        }

        .button-blue{
            background: #2563eb;
        }

        .button-blue:hover{
            background: #1d4ed8;
        }

        .button-disabled{
            background: #9ca3af;
            cursor: not-allowed;
        }

        /* Alertas */
        .alert-success{
            background: #dcfce7;
            border: 1px solid #86efac;
            padding: 12px;
            border-radius: 8px;
            color: #166534;
            margin-bottom: 10px;
        }

        .alert-error{
            background: #fee2e2;
            border: 1px solid #fca5a5;
            padding: 12px;
            border-radius: 8px;
            color: #b91c1c;
            margin-bottom: 10px;
        }

        .block-summary{
            padding: 12px;
            border-radius: 10px;
            border-left: 5px solid;
            margin-bottom: 10px;
        }

        .completed{
            background: #f0fdf4;
            border-color: #22c55e;
        }

        .current{
            background: #eff6ff;
            border-color: #3b82f6;
        }

        .pending{
            background: #fef9c3;
            border-color: #eab308;
        }

        .flex-between{
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .history-grid{
            display: grid;
            grid-template-columns: 1fr;
            gap: 15px;
        }
.form-group label {
    font-weight: bold;
    margin-bottom: 6px;
    display: block;
    font-size: 16px;
    color: #333;
}

.form-group input {
    width: 100%;
    padding: 14px 5px;
    font-size: 18px;
    border-radius: 10px;
    border: 2px solid #ccc;
    transition: all 0.3s ease;
}

.form-group input:focus {
    border-color: #2563eb;
    box-shadow: 0 0 8px rgba(37, 99, 235, 0.3);
    outline: none;
}

button.btn-registrar {
    width: 100%;
    padding: 16px;
    font-size: 18px;
    border-radius: 10px;
    background-color: #2563eb;
    color: white;
    border: none;
    font-weight: bold;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 12px;
}

button.btn-registrar:hover:not(:disabled) {
    background-color: #1d4ed8;
}

button.btn-registrar:disabled {
    background-color: #9ca3af;
    cursor: not-allowed;
}

        @media(min-width: 768px){
            .history-grid{
                grid-template-columns: repeat(2,1fr);
            }
        }

        @media(min-width: 1024px){
            .history-grid{
                grid-template-columns: repeat(3,1fr);
            }
        }

        @media(min-width: 1400px){
            .history-grid{
                grid-template-columns: repeat(4,1fr);
            }
        }
        header a{
            color:blue;
        }
    </style>
</head>
<body>


<header>
    <div class="container header-flex">
        <h2>Panel de Registro Ambiental</h2>
  <a href="{{ route('parametros.index') }}">Parametros de correcion</a>
<a href="{{ route('graficas') }}">Graficas</a>
        <div style="text-align: right;">
            <div id="clock" style="font-size: 24px; font-weight: bold;"></div>
            <div style="font-size: 12px; color: gray;">Hora local</div>
        </div>
    </div>
</header>

<div class="container">

    {{-- ALERTAS --}}
    @if(session('success'))
        <div class="alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert-error">
            <ul style="margin-left:15px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid">

        {{-- FORMULARIO PRINCIPAL --}}
        <div class="card">

            <h3 style="font-size:22px;font-weight:bold;">Registro Conjunto: Temperatura & Humedad</h3>

            @php
                $isBlockComplete = $bloqueActual && $estadoBloques[$bloqueActual]['temperatura'] && $estadoBloques[$bloqueActual]['humedad'];
                $disabled = !$bloqueActual || $isBlockComplete;
            @endphp
{{-- Alerta --}}
@if($limiteAlcanzado)
    <div class="alert-error">
        Ya alcanzaste el límite de 6 registros para hoy. No se puede registrar más.
    </div>
@endif

<form id="form-registro" class="form" action="{{ route('registros.storeAmbos') }}" method="POST">
    @csrf

    <div class="form-group">
        <label>Temperatura (°C)</label>
        <input type="number" id="temp" name="temperatura_valor" placeholder="Ej: 21.5" required
            @if($limiteAlcanzado) disabled @endif>
    </div>

    <div class="form-group">
        <label>Humedad (%)</label>
        <input type="number" id="hum" name="humedad_valor" placeholder="Ej: 55.0" required
            @if($limiteAlcanzado) disabled @endif>
    </div>

    <input type="hidden" id="hora-hidden" name="hora">

    <button type="submit" class="btn-registrar" 
        @if($limiteAlcanzado) disabled class="button-disabled" @endif>
        Registrar
    </button>
</form>

          

<script>
document.getElementById("form-registro").addEventListener("submit", function () {
    const now = new Date();
    const hh = String(now.getHours()).padStart(2,'0');
    const mm = String(now.getMinutes()).padStart(2,'0');

    document.getElementById("hora-hidden").value = `${hh}:${mm}`;
});
</script>

        </div>

        {{-- LADO DERECHO --}}
        <div>

            <div class="card">
                <h4 style="font-size:18px;font-weight:bold;">Resumen de la Jornada</h4>

                <div class="block-summary {{ $ultimoCompleto ? 'completed' : 'pending' }}">
                    Último Completado:
                    @if($ultimoCompleto)
                        <b>B. {{ $ultimoCompleto }} ({{ $bloques[$ultimoCompleto] }})</b>
                    @else
                        <b>No hay registros</b>
                    @endif
                </div>

                <div class="block-summary {{ $bloqueActual ? 'current' : 'completed' }}">
                    Bloque actual:
                    @if($bloqueActual)
                        <b>B. {{ $bloqueActual }} ({{ $bloques[$bloqueActual] }})</b>
                    @else
                        <b>Día Completado 🎉</b>
                    @endif
                </div>

                <div class="block-summary pending">
                    Próximo registro: <b id="next-block-text"></b>
                </div>
            </div>


            <div class="card">
                <h4 style="font-size:18px;font-weight:bold;">Horarios Nominales</h4>

                @foreach($bloques as $n => $time)
                    <div class="flex-between" style="padding:8px; border:1px solid #ddd; border-radius:8px; margin-bottom:5px;">
                        <span>Bloque {{ $n }}</span>
                        <span>{{ $time }}</span>
                    </div>
                @endforeach
            </div>

        </div>
    </div>

    {{-- HISTORIAL DEL DÍA --}}
    <div class="card" style="margin-top:25px;">
        <h3 style="font-size:22px;font-weight:bold;margin-bottom:15px;">Resumen de Cumplimiento</h3>

        <div class="history-grid">

            @foreach($estadoBloques as $num => $est)
                @php
                    $isCompleted = $est['temperatura'] && $est['humedad'];
                    $isCurrent = $bloqueActual == $num;
                @endphp

                <div class="card {{ $isCompleted ? 'completed' : ($isCurrent ? 'current' : 'pending') }}">
                    <h4 style="margin:0;">
                        Bloque {{ $num }} ({{ $bloques[$num] }})
                    </h4>
                    <p>
                        Estado:
                        <b>
                        @if($isCompleted)
                            Completado
                        @elseif($isCurrent)
                            Actual
                        @else
                            Pendiente
                        @endif
                        </b>
                    </p>
                    <p>T°: <b>{{ $est['temperatura'] ? 'OK' : 'Falta' }}</b></p>
                    <p>H°: <b>{{ $est['humedad'] ? 'OK' : 'Falta' }}</b></p>
                </div>
            @endforeach
        </div>

    </div>
</div>


<audio id="alarma" src="{{ asset('alarma.mp3') }}" hidden></audio>

<script>
    const bloques = @json($bloques);
    const bloqueActual = @json($bloqueActual);

    function updateClock(){
        const now = new Date();
        const h = now.getHours().toString().padStart(2,'0');
        const m = now.getMinutes().toString().padStart(2,'0');
        const s = now.getSeconds().toString().padStart(2,'0');
        document.getElementById('clock').textContent = `${h}:${m}:${s}`;

        updateNextBlockText();
    }

    function updateNextBlockText(){
        let txt = "Día completado";
        if(bloqueActual){
            txt = `Bloque ${bloqueActual} — ${bloques[bloqueActual]}`;
        }
        document.getElementById('next-block-text').textContent = txt;
    }

    // alarma
    function checkAlarm(){
        const now = new Date();
        const hour = now.getHours();
        const minute = now.getMinutes();
        const alarmTimes = [9,11,15];

        if(alarmTimes.includes(hour) && minute === 0){
            const a = document.getElementById('alarma');
            a.play().catch(()=>{});
        }
    }

    setInterval(updateClock, 1000);
    setInterval(checkAlarm, 30000);
    updateClock();

    // Guardar hora exacta al enviar formulario
    document.querySelector('form').addEventListener('submit', ()=>{
        const now = new Date();
        const h = now.getHours().toString().padStart(2,'0');
        const m = now.getMinutes().toString().padStart(2,'0');
        document.getElementById('hora-hidden').value = `${h}:${m}`;
    });
</script>

</body>
</html>
</x-app-layout>