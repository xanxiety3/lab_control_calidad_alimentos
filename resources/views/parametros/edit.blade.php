
    <div class="p-6">
        <form action="{{ route('parametros.update', $parametro) }}" method="POST">
            @method('PUT')
            @include('parametros._form')
        </form>
    </div>
