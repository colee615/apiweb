<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Compras</title>
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

        /* --- Resumen por insumo --- */
        .resumen {
            max-width: 600px;
            margin: 0 auto 30px auto;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
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

        /* --- Tabla --- */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
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
    <h1>📦 Compras</h1>
    <h2>Reporte Nº {{ $nuevoNumero }}</h2>

    <!-- Resumen por insumo -->
    <div class="resumen">
        <h3>Resumen por Insumo:</h3>
        <ul>
            @foreach($resumenPorInsumo as $insumo => $datos)
                <li>
                    <strong>{{ $insumo }}</strong> - Cantidad Total: {{ $datos['total_cantidad'] }}, Total Compras: {{ number_format($datos['total_compra'], 2) }}
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Tabla de compras -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha</th>
                <th>Insumo</th>
                <th>Cantidad</th>
                <th>Precio Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @php $granTotal = 0; @endphp

            @foreach ($compras as $compra)
                @php $totalCompra = 0; @endphp
                @foreach ($compra->compraInventarios as $index => $compraInventario)
                    @php
                        $subtotal = $compraInventario->precio_unitario * $compraInventario->cantidad_comprada;
                        $totalCompra += $subtotal;
                    @endphp
                    <tr>
                        @if ($index == 0)
                            <td rowspan="{{ $compra->compraInventarios->count() }}">{{ $compra->id }}</td>
                            <td rowspan="{{ $compra->compraInventarios->count() }}">{{ \Carbon\Carbon::parse($compra->fecha)->format('d/m/Y') }}</td>
                        @endif
                        <td>{{ $compraInventario->inventario->insumo->nombre ?? 'No especificado' }} ({{ $compraInventario->inventario->insumo->unidade->abreviatura ?? '' }})</td>
                        <td>{{ number_format($compraInventario->cantidad_comprada, 2) }}</td>
                        <td>{{ number_format($compraInventario->precio_unitario, 2) }}</td>
                        <td>{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" style="text-align: right; font-weight: bold;">Total Compra:</td>
                    <td colspan="2" style="font-weight: bold;">{{ number_format($totalCompra, 2) }}</td>
                </tr>
                <tr><td colspan="6" style="border-top:1px solid #ccc;"></td></tr>
                @php $granTotal += $totalCompra; @endphp
            @endforeach
        </tbody>
    </table>

    <div class="total">Total General: {{ number_format($granTotal, 2) }}</div>

    <footer>
        &copy; {{ date('Y') }} IMBAE. Todos los derechos reservados.
    </footer>
</body>
</html>
