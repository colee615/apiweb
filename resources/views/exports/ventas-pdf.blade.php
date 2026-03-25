<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas Mensual Detallado</title>
    <style>
        body {
            font-family: "Poppins", "Segoe UI", Arial, sans-serif;
            margin: 40px;
            color: #2c3e50;
            background: #f4f6f9;
        }

        h1 {
            text-align: center;
            font-size: 34px;
            margin-bottom: 8px;
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        h2 {
            text-align: center;
            color: #7f8c8d;
            font-size: 18px;
            margin-bottom: 30px;
            font-weight: 400;
        }

        .resumen {
            max-width: 600px;
            margin: 0 auto 30px auto;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
        }

        .resumen h3 {
            margin-bottom: 12px;
            font-size: 18px;
            color: #34495e;
        }

        .descripcion {
            font-size: 14px;
            margin-top: 8px;
            color: #34495e;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        th {
            background: linear-gradient(90deg, #27ae60, #2ecc71);
            color: white;
            padding: 12px;
            font-size: 15px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            text-align: center;
            font-size: 14px;
            border-bottom: 1px solid #ecf0f1;
        }

        tbody tr:nth-child(odd) td {
            background: #fcfcfc;
        }

        tbody tr:nth-child(even) td {
            background: #f8fafb;
        }

        tbody tr:hover td {
            background: #eafaf1;
        }

        .estado-activo {
            color: #27ae60;
            font-weight: 600;
        }

        .estado-inactivo {
            color: #e74c3c;
            font-weight: 600;
        }

        .total {
            font-size: 24px;
            font-weight: bold;
            color: #27ae60;
            text-align: right;
            margin-top: 15px;
        }

        footer {
            text-align: center;
            font-size: 11px;
            color: #95a5a6;
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <h1>🛒 Ventas</h1>
    <h2>Reporte Nº {{ $nuevoNumero }}</h2>

    <!-- 12 gráficas, una por mes -->
    @foreach($meses as $mes => $productos)
    @php
    // Convertir mes a español
    $nombreMes = \Carbon\Carbon::parse($mes.'-01')->locale('es')->isoFormat('MMMM YYYY');
    @endphp
    <div class="resumen">
        <h3>{{ ucfirst($nombreMes) }}</h3>
        <canvas id="chart-{{ $mes }}" width="100" height="100"></canvas>
        <div class="descripcion">
            @if(count($productos) > 0)
            <ul>
                @foreach($productos as $nombre => $info)
                <li>{{ $nombre }}: {{ $info['cantidad'] }} unidades, {{ number_format($info['dinero'],2) }} Bs</li>
                @endforeach
            </ul>
            @else
            No hubo ventas
            @endif
        </div>
    </div>
    @endforeach



    <!-- Tabla general de ventas -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Estado</th>
                <th>Cliente</th>
                <th>Carnet</th>
                <th>Fecha</th>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($ventas as $venta)
            @php $totalVenta = 0; @endphp
            @foreach($venta->ventaInventarios as $index => $vi)
            @if($index==0)
            <tr>
                <td rowspan="{{ $venta->ventaInventarios->count() }}">{{ $venta->id }}</td>
                <td rowspan="{{ $venta->ventaInventarios->count() }}">
                    @if($venta->denegada)
                    <span class="estado-inactivo">Anulada</span>
                    @else
                    <span class="estado-activo">Válida</span>
                    @endif
                </td>
                <td rowspan="{{ $venta->ventaInventarios->count() }}">{{ $venta->cliente_id ? $venta->cliente->nombre : 'Sin cliente' }}</td>
                <td rowspan="{{ $venta->ventaInventarios->count() }}">{{ $venta->cliente_id ? $venta->cliente->carnet : 'Sin cliente' }}</td>
                <td rowspan="{{ $venta->ventaInventarios->count() }}">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y') }}</td>
                <td>{{ $vi->inventario->producto->nombre ?? 'No especificado' }} ({{ $vi->inventario->producto->unidad->abreviatura ?? '' }})</td>
                <td>{{ $vi->cantidad }}</td>
                <td>{{ number_format($vi->precio,2) }} Bs</td>
                <td>{{ number_format($vi->precio*$vi->cantidad,2) }} Bs</td>
            </tr>
            @else
            <tr>
                <td>{{ $vi->inventario->producto->nombre ?? 'No especificado' }} ({{ $vi->inventario->producto->unidad->abreviatura ?? '' }})</td>
                <td>{{ $vi->cantidad }}</td>
                <td>{{ number_format($vi->precio,2) }}</td>
                <td>{{ number_format($vi->precio*$vi->cantidad,2) }}</td>
            </tr>
            @endif
            @php $totalVenta += $vi->precio*$vi->cantidad; @endphp
            @endforeach
            <tr>
                <td colspan="7" style="text-align:right;font-weight:bold;">Total Venta:</td>
                <td colspan="2">{{ number_format($totalVenta,2) }} Bs</td>
            </tr>
            <tr>
                <td colspan="9" style="border-top:1px solid #ccc;"></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    @php
    $totalGeneral = $ventas->filter(fn($v)=>!$v->denegada)
    ->flatMap(fn($v)=>$v->ventaInventarios)
    ->sum(fn($vi)=>$vi->precio*$vi->cantidad);
    @endphp
    <div class="total">Total General: {{ number_format($totalGeneral,2) }} Bs</div>

    <footer>
        &copy; {{ date('Y') }} IMBAE. Todos los derechos reservados.
    </footer>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>

<script>
const meses = @json($meses);

// Lista global de productos (ej: 10 fijos)
const todosLosProductos = Array.from(
    new Set(Object.values(meses).flatMap(p => Object.keys(p)))
);

Object.keys(meses).forEach((mes) => {
    const ctx = document.getElementById(`chart-${mes}`).getContext('2d');
    const productosMes = meses[mes] || {};

    const labels = todosLosProductos;

    // Datos con mínimo "0.01" para que se vea en la torta
    const data = labels.map(nombre => {
        const cantidadReal = productosMes[nombre] ? productosMes[nombre].cantidad : 0;
        return cantidadReal > 0 ? cantidadReal : 0.01;
    });

    // Guardar cantidades reales (para tooltip y labels)
    const dataReales = labels.map(nombre => productosMes[nombre] ? productosMes[nombre].cantidad : 0);

    const colors = labels.map((_, i) => `hsl(${(i * 36) % 360}, 70%, 55%)`);
    const totalReal = dataReales.reduce((s, v) => s + v, 0);

    if (window.Chart && window.ChartDataLabels) {
        Chart.register(ChartDataLabels);
    }

    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                backgroundColor: colors,
                borderColor: '#fff',
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: { boxWidth: 14, padding: 12, font: { size: 12 } }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const index = context.dataIndex;
                            const nombre = context.label;
                            const real = dataReales[index];
                            const pct = totalReal > 0 ? (real / totalReal * 100).toFixed(1) : 0;
                            return `${nombre}: ${real} unidades (${pct}%)`;
                        }
                    }
                },
                datalabels: {
                    color: '#fff',
                    formatter: function(value, ctx) {
                        const real = dataReales[ctx.dataIndex];
                        if (totalReal === 0) return '0%';
                        if (real === 0) return '0%';
                        return (real / totalReal * 100).toFixed(1) + '%';
                    },
                    font: { weight: 'bold', size: 11 }
                }
            }
        }
    });
});
</script>




</body>

</html>