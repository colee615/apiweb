<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Asignaciones</title>
    <style>
        @page { margin: 25px; }
        body { font-family: 'Poppins', 'Segoe UI', Arial, sans-serif; margin: 0; padding: 0; color: #2c3e50; background: #f4f6f9; }
        .container { padding: 20px; }

        .header { text-align: center; margin-bottom: 20px; }
        .header h1 { font-size: 22px; color: #0a321b; margin: 0; padding-bottom: 5px; border-bottom: 2px solid #0a321b; display: inline-block; }
        .header small { display: block; margin-top: 5px; font-size: 11px; color: #555; }

        .summary { display: flex; justify-content: center; gap: 40px; margin-bottom: 20px; }
        .summary div { text-align: center; background: #fff; padding: 10px 20px; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .summary div h2 { margin: 0; font-size: 20px; color: #e74c3c; }
        .summary div p { margin: 0; font-size: 12px; color: #555; }

        .chart { text-align: center; margin-bottom: 20px; }
        .chart img { max-width: 80%; border-radius: 8px; }

        table { width: 100%; border-collapse: collapse; table-layout: fixed; margin-top: 15px; background: #fff; border-radius: 8px; overflow: hidden; }
        th, td { border: 1px solid #ccc; padding: 6px 4px; text-align: center; vertical-align: middle; word-wrap: break-word; }
        th { background-color: #0a321b; color: white; font-weight: bold; }
        tbody tr:nth-child(even) { background-color: #f4f8f5; }

        .firma { margin-top: 20px; text-align: center; font-size: 10px; }
        .firma img { max-width: 200px; max-height: 80px; }

        .footer { margin-top: 30px; text-align: center; font-size: 9px; color: #777; border-top: 1px solid #ccc; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Reporte de Asignaciones</h1>
            <small>Generado el {{ now()->format('d/m/Y H:i') }}</small>
        </div>

        <!-- Resumen por usuario -->
        <div class="summary">
            @foreach($resumenPorUsuario as $userId => $total)
                <div>
                    <h2>{{ $total }}</h2>
                    <p>{{ \App\Models\User::find($userId)->nombre ?? 'Sin asignar' }}</p>
                </div>
            @endforeach
        </div>

        <!-- Gráfico -->
        @if(isset($chartImageSrc))
        <div class="chart">
            <img src="{{ $chartImageSrc }}" alt="Gráfico de Asignaciones">
        </div>
        @endif

        <!-- Tabla de asignaciones -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;">#</th>
                    <th style="width: 140px;">Producto</th>
                    <th style="width: 70px;">Cantidad</th>
                    <th style="width: 90px;">Sucursal</th>
                    <th style="width: 120px;">Encargado</th>
                </tr>
            </thead>
            <tbody>
                @foreach($asignaciones as $i => $asig)
                <tr>
                    <td>#{{ $asig->venta->id ?? '—' }}</td>
                    <td>{{ $asig->ventaInventario->inventario->producto->nombre ?? '—' }}</td>
                    <td>{{ $asig->cantidad }}</td>
                    <td>{{ $asig->sucursal->nombre ?? '—' }}</td>
                    <td>{{ $asig->user->nombre ?? '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Firma opcional -->
        <div class="firma">
    @if(!empty($asignaciones->first()?->imagen))
        <img src="{{ $asignaciones->first()->imagen }}" alt="Imagen">
        <div>Imagen del responsable</div>
    @else
        <div><em>Imagen no disponible</em></div>
    @endif
</div>


        <div class="footer">
            &copy; {{ date('Y') }} Tu Empresa. Todos los derechos reservados.
        </div>
    </div>
</body>
</html>
