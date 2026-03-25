<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Productos con Stock Bajo</title>
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
            background: linear-gradient(90deg, #e74c3c, #c0392b);
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
            background: linear-gradient(90deg, #e74c3c, #c0392b);
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

        tbody tr:nth-child(odd) td { background: #fcfcfc; }
        tbody tr:nth-child(even) td { background: #f8fafb; }
        tbody tr:hover td { background: #fdecea; }

        .imagen-user {
            width: 40px;
            height: 40px;
            object-fit: cover;
            border-radius: 6px;
        }

        .estado-activo { color: #27ae60; font-weight: 600; }
        .estado-inactivo { color: #e74c3c; font-weight: 600; }

        .footer {
            text-align: center;
            font-size: 11px;
            color: #95a5a6;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <h1>⚠️ Productos con Stock Bajo</h1>
    <h2>Reporte</h2>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Unidad Venta</th>
                <th>Stock Actual</th>
                <th>Stock Mínimo</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productosStockBajo as $index => $producto)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>
                    @if($producto->imageSrc)
                        <img src="{{ $producto->imageSrc }}" class="imagen-user">
                    @else
                        -
                    @endif
                </td>
                <td>{{ $producto->nombre }}</td>
                <td>{{ ucfirst($producto->unidad->nombre ?? 'Desconocida') }}</td>
                <td>{{ number_format($producto->stock, 2, ',', '.') }}</td>
                <td>{{ $producto->stock_minimo }}</td>
                <td class="{{ $producto->estado == 1 ? 'estado-activo' : 'estado-inactivo' }}">
                    {{ $producto->estado == 1 ? 'Activo' : 'Inactivo' }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} IMBAE. Todos los derechos reservados.
    </div>
</body>
</html>
