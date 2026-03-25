<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Unidades</title>
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
            background: linear-gradient(90deg, #8e44ad, #9b59b6);
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

        /* --- Resumen --- */
        .resumen {
            max-width: 600px;
            margin: 0 auto 30px auto;
            background: white;
            border-radius: 12px;
            padding: 15px 20px;
            box-shadow: 0 6px 14px rgba(0, 0, 0, 0.08);
            text-align: center;
        }

        .resumen h3 {
            margin-bottom: 12px;
            font-size: 18px;
            color: #34495e;
        }

        .resumen p {
            font-size: 15px;
            margin: 0;
            font-weight: 500;
        }

        /* --- Tabla --- */
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
            background: linear-gradient(90deg, #8e44ad, #9b59b6);
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
            background: #f3eafc;
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
    <h1>📦 Unidades</h1>
    <h2>Reporte Nº {{ $nuevoNumero }}</h2>

    <!-- Resumen -->
    <div class="resumen">
        <h3>Resumen General:</h3>
        <p><strong>Total de Unidades:</strong> {{ $unidades->count() }}</p>
    </div>


    <!-- Tabla -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Abreviatura</th>
                <th>Equivalencia</th>
                <th>Unidad Base</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($unidades as $unidade)
            <tr>
                <td>{{ $unidade->id }}</td>
                <td>{{ $unidade->nombre }}</td>
                <td>{{ $unidade->abreviatura }}</td>
                <td>{{ $unidade->equivalencia }}</td>
                <td>{{ optional($unidade->Unidade)->nombre ?? 'No aplica' }}</td>
                <td>
                    @if ($unidade->estado)
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
