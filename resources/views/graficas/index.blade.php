<x-app-layout>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gráficas y Reporte de Temperatura y Humedad</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body {
            background: #f5f6fa;
            font-family: Arial, Helvetica, sans-serif;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 40px;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        h2, h3 {
            margin-bottom: 20px;
            color: #333;
            text-align: center;
        }

     .padre {
    display: flex;
    justify-content: center;
    gap: 40px;
    flex-wrap: wrap; /* permite que los gráficos bajen en pantallas pequeñas */
}

.chart-box {
    flex: 1 1 400px; /* crece y se encoge, ancho base 400px */
    max-width: 500px; /* ancho máximo */
    max-height: 500px;
    margin: 15px;
    padding: 55px;
}


        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }

        table, th, td {
            border: 1px solid #ddd;
        }

        th, td {
            padding: 8px;
            text-align: center;
        }

        th {
            background: #f0f0f0;
        }

        /* Paginación simple */
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            padding-left: 0;
            margin-top: 15px;
        }

        .pagination li {
            margin: 0 5px;
        }

        .pagination li span,
        .pagination li a {
            display: block;
            padding: 5px 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            text-decoration: none;
            color: #333;
        }

        .pagination li span {
            background: #eee;
            font-weight: bold;
        }

        .pagination li a:hover {
            background: #ddd;
        }

     
    </style>
</head>
<body>


    <!-- ========================= -->
    <!--   TABLA DE REGISTROS      -->
    <!-- ========================= -->
    <div class="card">
        <h3>Reporte de Registros</h3>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Tipo</th>
                    <th>Valor Original</th>
                    <th>Valor Corregido</th>
                </tr>
            </thead>
            <tbody>
                @foreach($registros as $reg)
                    <tr>
                        <td>{{ $reg->created_at->format('Y-m-d') }}</td>
                        <td>{{ $reg->created_at->format('H:i') }}</td>
                        <td>{{ ucfirst($reg->tipo) }}</td>
                        <td>{{ $reg->valor_original }}</td>
                        <td>{{ $reg->valor_corregido }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Links de paginación -->
        <div>
            {{ $registros->links('pagination::simple-default') }}
        </div>
    </div>

    <!-- ========================= -->
    <!--   GRÁFICAS TEMPERATURA Y HUMEDAD -->
    <!-- ========================= -->
    <div style="text-align:center; margin-bottom:20px;">
        <button id="btnDescargarPDF" style="background:#3490dc; color:white; font-weight:bold; padding:8px 15px; border:none; border-radius:5px; cursor:pointer;">
            Descargar Reporte PDF
        </button>
    </div>

    <div class="padre">
        <div class="card chart-box">
            <h3>Temperatura (°C)</h3>
            <canvas id="graficaTemperatura"></canvas>
        </div>

        <div class="card chart-box">
            <h3>Humedad Relativa (%)</h3>
            <canvas id="graficaHumedad"></canvas>
        </div>
    </div>

    <script>
        const opciones = { responsive: true, maintainAspectRatio: false };
        const dias = @json($dias);

        const t9 = @json($temp_9);
        const t11 = @json($temp_11);
        const t15 = @json($temp_15);

        const h9 = @json($hum_9);
        const h11 = @json($hum_11);
        const h15 = @json($hum_15);

        const tempInferior = Array(dias.length).fill(19);
        const tempOptima = Array(dias.length).fill(22);
        const tempSuperior = Array(dias.length).fill(24);

        const humInferior = Array(dias.length).fill(30);
        const humOptima = Array(dias.length).fill(45);
        const humSuperior = Array(dias.length).fill(60);

        // Temperatura
        const ctx1 = document.getElementById('graficaTemperatura').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: dias,
                datasets: [
                    { label: "9:00 AM", data: t9, borderColor: "green", borderWidth: 2 },
                    { label: "11:00 AM", data: t11, borderColor: "orange", borderWidth: 2 },
                    { label: "3:00 PM", data: t15, borderColor: "purple", borderWidth: 2 },
                    { label: "Límite inferior (19°C)", data: tempInferior, borderColor: "red", borderWidth: 2, borderDash: [5,5] },
                    { label: "Temperatura óptima (22°C)", data: tempOptima, borderColor: "blue", borderWidth: 2, borderDash: [5,5] },
                    { label: "Límite superior (24°C)", data: tempSuperior, borderColor: "red", borderWidth: 2, borderDash: [5,5] }
                ]
            },
            options: opciones
        });

        // Humedad
        const ctx2 = document.getElementById('graficaHumedad').getContext('2d');
        new Chart(ctx2, {
            type: 'line',
            data: {
                labels: dias,
                datasets: [
                    { label: "9:00 AM", data: h9, borderColor: "green", borderWidth: 2 },
                    { label: "11:00 AM", data: h11, borderColor: "orange", borderWidth: 2 },
                    { label: "3:00 PM", data: h15, borderColor: "purple", borderWidth: 2 },
                    { label: "Límite inferior (30%)", data: humInferior, borderColor: "red", borderWidth: 2, borderDash: [5,5] },
                    { label: "Humedad óptima (45%)", data: humOptima, borderColor: "blue", borderWidth: 2, borderDash: [5,5] },
                    { label: "Límite superior (60%)", data: humSuperior, borderColor: "red", borderWidth: 2, borderDash: [5,5] }
                ]
            },
            options: opciones
        });

        document.getElementById('btnDescargarPDF').addEventListener('click', async function () {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            const tabla = document.querySelector('table');
            const canvasTabla = await html2canvas(tabla, { scale: 2 });
            const imgTabla = canvasTabla.toDataURL('image/png');
            doc.addImage(imgTabla, 'PNG', 10, 10, 190, canvasTabla.height * 190 / canvasTabla.width);

            let yOffset = 10 + (canvasTabla.height * 190 / canvasTabla.width) + 10;

            const canvasTemp = await html2canvas(document.getElementById('graficaTemperatura').parentNode, { scale: 2 });
            const imgTemp = canvasTemp.toDataURL('image/png');

            const canvasHum = await html2canvas(document.getElementById('graficaHumedad').parentNode, { scale: 2 });
            const imgHum = canvasHum.toDataURL('image/png');

            doc.addPage();
            doc.addImage(imgTemp, 'PNG', 10, 10, 190, canvasTemp.height * 190 / canvasTemp.width);

            doc.addPage();
            doc.addImage(imgHum, 'PNG', 10, 10, 190, canvasHum.height * 190 / canvasHum.width);

            doc.save('reporte_temperatura_humedad.pdf');
        });
    </script>

</body>
</html>
    </x-app-layout>