<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Cupones Asignados</title>
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
            background: linear-gradient(90deg, #f1c40f, #f39c12);
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

        /* --- Resumen por cupón --- */
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
            background: linear-gradient(90deg, #f1c40f, #f39c12);
            color: white;
            padding: 10px;
            font-size: 14px;
            text-transform: uppercase;
        }

        td {
            padding: 10px;
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
            background: #fff7e6;
        }

        .estado-usado {
            color: #27ae60;
            font-weight: 600;
        }
        .estado-no-usado {
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
    <h1>🎟️ Cupones Asignados</h1>
    <h2>Reporte Nº {{ $nuevoNumero }}</h2>

    <!-- Resumen por cupón -->
   <div class="resumen">
    <h3>Resumen por Cupón:</h3>
    <ul>
        @foreach($resumenPorCupon as $codigo => $datos)
            <li>
                <strong>{{ $codigo }}</strong> - Total asignados: {{ $datos['total_asignados'] }},
                Usados: {{ $datos['usados'] }},
                No usados: {{ $datos['no_usados'] }}
            </li>
        @endforeach
    </ul>
</div>


    <!-- Tabla de cupones -->
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Apellidos</th>
                <th>Carnet</th>
                <th>Código</th>
                <th>Descuento</th>
                <th>Tipo</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Uso Máx</th>
                <th>Usado</th>
            </tr>
        </thead>
        <tbody>
            @foreach($cupones as $index => $cupon)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $cupon->cliente->nombre ?? 'N/D' }}</td>
                    <td>{{ $cupon->cliente->apellidos ?? 'N/D' }}</td>
                    <td>{{ $cupon->cliente->carnet ?? 'N/D' }}</td>
                    <td>{{ $cupon->cupone->codigo ?? '-' }}</td>
                    <td>
                        @if(isset($cupon->cupone->tipo) && isset($cupon->cupone->descuento))
                            {{ $cupon->cupone->descuento }}
                            @if($cupon->cupone->tipo === 'Monto')
                                Bs.
                            @elseif($cupon->cupone->tipo === 'Porcentaje')
                                %
                            @endif
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $cupon->cupone->tipo ?? '-' }}</td>
                    <td>{{ \Carbon\Carbon::parse($cupon->cupone->fecha_inicio)->format('d/m/Y') }}</td>
                    <td>{{ \Carbon\Carbon::parse($cupon->cupone->fecha_fin)->format('d/m/Y') }}</td>
                    <td>{{ $cupon->cupone->uso_maximo ?? '-' }}</td>
                    <td>
                        @if($cupon->usado)
                            <span class="estado-usado">Sí</span>
                        @else
                            <span class="estado-no-usado">No</span>
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
