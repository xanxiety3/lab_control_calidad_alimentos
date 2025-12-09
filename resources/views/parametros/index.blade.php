<x-app-layout>
<style>/* Contenedor general */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
    background-color: #f7f7f7;
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Encabezado */
.header {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.header h1 {
    font-size: 2rem;
    font-weight: bold;
    color: #1a202c;
}

.btn-nuevo {
    background-color: #2563eb;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    text-decoration: none;
    transition: background 0.3s;
}

.btn-nuevo:hover {
    background-color: #1d4ed8;
}

/* Mensaje de éxito */
.alert-success {
    margin-bottom: 1.5rem;
    padding: 1rem;
    background-color: #d1fae5;
    border-left: 4px solid #10b981;
    color: #065f46;
    border-radius: 0.5rem;
}

/* Grid de tarjetas */
.param-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1.5rem;
}

/* Tarjetas de parámetros */
.param-card {
    background-color: white;
    border-radius: 1rem;
    padding: 1.5rem;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    transition: transform 0.3s, box-shadow 0.3s;
}

.param-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 15px rgba(0,0,0,0.15);
}

.param-card h2 {
    font-size: 1.25rem;
    font-weight: 600;
    text-transform: uppercase;
    color: #111827;
    margin-bottom: 1rem;
}

/* Valores dentro de la tarjeta */
.valores {
    display: flex;
    justify-content: space-between;
    gap: 0.5rem;
}

.valor {
    background-color: #f3f4f6;
    flex: 1;
    padding: 0.5rem;
    text-align: center;
    border-radius: 0.5rem;
    font-weight: 500;
    color: #374151;
}

/* Acciones de editar y eliminar */
.acciones {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.acciones a {
    color: #2563eb;
    text-decoration: none;
    font-weight: 500;
    transition: color 0.3s;
}

.acciones a:hover {
    color: #1d4ed8;
}

.acciones button {
    background: none;
    border: none;
    color: #dc2626;
    font-weight: 500;
    cursor: pointer;
    transition: color 0.3s;
}

.acciones button:hover {
    color: #b91c1c;
}

/* Mensaje de vacío */
.empty {
    grid-column: 1/-1;
    text-align: center;
    color: #9ca3af;
    padding: 2rem;
    font-size: 1.1rem;
}



            header {
        display: flex;
        align-items: center;
        justify-content: center; /* elementos a los extremos */
        flex-direction: column;
        padding: 15px 30px;
        background-color: #2c3e50; /* azul oscuro */
        color: white;
        box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        border-radius: 0 0 10px 10px;
        margin-bottom: 50px;
            }
      

    header a {
        text-decoration: none;
        color: white;
        font-weight: bold;
        background-color: #3498db; /* azul más claro */
        padding: 8px 15px;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }

header a:hover {
        background-color: #2980b9;
    }

    header h1 {
        margin: 0 0 10px;
        font-size: 1.5rem;
    }

    @media (max-width: 600px) {
        header {
            flex-direction: column;
            align-items: flex-start;
            gap: 10px;
        }

        header h1 {
            font-size: 1.2rem;
        }
    }
    </style>
</head>
<body>

<div class="container">
    <!-- Encabezado -->
    <div class="header">
        <h1>Parámetros de Corrección</h1>

        @php
            $tieneTemperatura = $parametros->where('tipo', 'temperatura')->count() > 0;
            $tieneHumedad = $parametros->where('tipo', 'humedad')->count() > 0;
        @endphp

        @if (!$tieneTemperatura || !$tieneHumedad)
            <a href="{{ route('parametros.create') }}" class="btn-nuevo">+ Nuevo Parámetro</a>
        @endif
    </div>

    <!-- Mensaje de éxito -->
    @if (session('success'))
        <div class="alert-success">
            <span>{{ session('success') }}</span>
        </div>
    @endif

    <!-- Tarjetas de Parámetros -->
    <div class="param-grid">
        @forelse ($parametros as $parametro)
            <div class="param-card">
                <h2>{{ ucfirst($parametro->tipo) }}</h2>
                <div class="valores">
                    <div class="valor">{{ $parametro->valor_1 }}</div>
                    <div class="valor">{{ $parametro->valor_2 }}</div>
                    <div class="valor">{{ $parametro->valor_3 }}</div>
                </div>
                <div class="acciones">
                    <a href="{{ route('parametros.edit', $parametro) }}">Editar</a>
                    <form action="{{ route('parametros.destroy', $parametro) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                onclick="return confirm('¿Estás seguro de que deseas eliminar este parámetro?');">
                            Eliminar
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <div class="empty">No hay parámetros registrados.</div>
        @endforelse
    </div>
</div>
</x-app-layout>