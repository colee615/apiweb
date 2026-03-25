<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Proveedores</title>
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

        /* --- Resumen por estado --- */
        .resumen {
            max-width: 400px;
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

        .estado-activo {
            color: #27ae60;
            font-weight: 600;
        }
        .estado-inactivo {
            color: #e74c3c;
            font-weight: 600;
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
    <h1>🏢 Proveedores</h1>
    <h2>Reporte Nº {{ $nuevoNumero }}</h2>

    <!-- Resumen por estado -->
    <div class="resumen">
        <h3>Resumen por Estado:</h3>
        <ul>
            @foreach($resumenPorEstado as $estado => $total)
                <li><strong>{{ $estado }}</strong> - Total: {{ $total }}</li>
            @endforeach
        </ul>
    </div>

    <!-- Tabla de proveedores -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($proveedores as $p)
                <tr>
                    <td>{{ $p->id }}</td>
                    <td>{{ $p->nombre }}</td>
                    <td>
                        @if($p->estado)
                            <span class="estado-activo">Activo</span>
                        @else
                            <span class="estado-inactivo">Inactivo</span>
                        @endif
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
