<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Resultados de Análisis</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2>Estimado(a) {{ $solicitud->cliente->persona->nombre_completo ?? $solicitud->cliente->razon_social }}</h2>
    <p>Adjunto encontrará los resultados de su análisis correspondiente a la solicitud <strong>{{ $solicitud->numero_solicitud }}</strong>.</p>

    <p>Si tiene alguna duda o desea más información, no dude en contactarnos.</p>

    <br>
    <p>Atentamente,<br>
    <strong>Laboratorio Clínico de Diagnóstico Animal</strong></p>
</body>
</html>
