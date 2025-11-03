<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333;">
    <h2 style="color:#2563eb;">📋 Reporte de resultados de laboratorio</h2>
    <p>Estimado(a) Gestor Técnico,</p>
    <p>Adjunto encontrarás el archivo con los resultados primarios correspondientes a la solicitud 
        <strong>#{{ $solicitud->numero_solicitud }}</strong>.</p>

    <p>Tipo de muestra: {{ $solicitud->muestras->pluck('tipoMuestra.nombre')->implode(', ') }}</p>

    <p style="margin-top: 20px;">Saludos cordiales,<br>
    <strong>Laboratorio de Control y Calidad de Alimentos</strong></p>
</body>
</html>
