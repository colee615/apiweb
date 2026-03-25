<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Kardex - Productos</title>
    <style>
        body {
            font-family: 'Poppins', 'Segoe UI', Arial, sans-serif;
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
            max-width: 700px;
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

        .resumen ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .resumen li {
            padding: 6px 0;
            font-size: 14px;
        }

        .chart-container {
            text-align: center;
            margin: 20px 0;
        }

        .chart-container img {
            max-width: 100%;
            height: auto;
            border-radius: 12px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
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
            font-size: 14px;
            text-transform: uppercase;
        }

        td {
            padding: 12px;
            text-align: center;
            font-size: 13px;
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

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #34495e;
            margin-top: 30px;
            margin-bottom: 10px;
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
    <h1>📦 Kardex de Productos</h1>
    <h2>Reporte  Nº {{ $nuevoNumero }}: {{ $producto->nombre }}</h2>

    <!-- Resumen de Stock -->
    <div class="resumen">
        <h3>Información del Producto:</h3>
        <ul>
            <li><strong>Nombre:</strong> {{ $producto->nombre }}</li>
            <li><strong>Precio de Venta:</strong> {{ number_format($producto->venta, 2) }} Bs</li>
            <li><strong>Unidad Base:</strong> {{ $producto->unidad->nombre }}</li>
            <li><strong>Stock Actual:</strong> {{ number_format($producto->stock_calculado, 2) }} {{ $producto->unidad->abreviatura }}</li>
            <li><strong>Cantidad Vendida:</strong> {{ $producto->cantidad_vendida }}</li>
            <li><strong>Ganancia Total:</strong> {{ number_format($producto->ganancia_total, 2) }} Bs</li>
        </ul>
    </div>

    <!-- Gráfico de Movimientos -->
    <div class="chart-container">
        <img src="{{ $chartImageSrc ?? '' }}" alt="Gráfico de Movimientos">
    </div>

    <!-- Tabla de Movimientos -->
    <div class="section-title">Movimientos de Inventario</div>
    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Cantidad</th>
                <th>Tipo</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($producto->inventarios->where('estado', 1) as $movimiento)
            <tr>
                <td>{{ \Carbon\Carbon::parse($movimiento->fecha)->format('d/m/Y') }}</td>
                <td>{{ $movimiento->motivo }}</td>
                <td>{{ number_format($movimiento->cantidad, 2) }}</td>
                <td>
                    @switch($movimiento->tipo)
                        @case(1) Reabastecimiento @break
                        @case(2) Venta @break
                        @case(3) Reembolso @break
                        @default Otro
                    @endswitch
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <footer>
        &copy; {{ date('Y') }} IMBAE. Todos los derechos reservados.
    </footer>
</body>

</html>
