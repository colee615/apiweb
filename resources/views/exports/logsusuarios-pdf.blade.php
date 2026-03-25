<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Logs por Usuario</title>
    <style>
        @page {
            margin: 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10.5px;
            margin: 0;
            padding: 0;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 22px;
            color: #0a321b;
            margin: 0;
            padding-bottom: 5px;
            border-bottom: 2px solid #0a321b;
            display: inline-block;
        }

        .header small {
            display: block;
            margin-top: 5px;
            font-size: 11px;
            color: #555;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            margin-top: 15px;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 6px 4px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }

        th {
            background-color: #0a321b;
            color: white;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background-color: #f4f8f5;
        }

        .estado-ok {
            color: #0a321b;
            font-weight: bold;
        }

        .estado-error {
            color: #a94442;
            font-weight: bold;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #777;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>Reporte de Logs por Usuario</h1>
        <small>Generado el {{ now()->format('d/m/Y H:i') }}</small>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th style="width: 140px;">Correo</th>
                <th style="width: 100px;">Tipo de Actividad</th>
                <th style="width: 180px;">Descripción</th>
                <th style="width: 100px;">IP</th>
                <th style="width: 100px;">Fecha</th>
            </tr>
        </thead>
        <tbody>
            @foreach($logs as $index => $log)
                <tr>
                    <td>{{ $log->id }}</td>
                    <td>{{ $log->user_email ?? 'No registrado' }}</td>
                    <td>{{ $log->activity_type }}</td>
                    <td>{{ $log->description }}</td>
                    <td>{{ $log->ip_address }}</td>
                    <td>{{ \Carbon\Carbon::parse($log->fecha_actividad)->format('d/m/Y H:i') }}</td>
                    
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        &copy; {{ date('Y') }} Tu Empresa. Todos los derechos reservados.
    </div>
</div>
</body>
</html>
