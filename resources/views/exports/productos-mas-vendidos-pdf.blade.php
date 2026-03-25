<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Productos Más Vendidos</title>
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

        /* --- TOP 5 --- */
        .top-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-bottom: 35px;
            flex-wrap: wrap;
        }

        .card {
            flex: 1;
            min-width: 160px;
            background: white;
            border-radius: 14px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 6px 14px rgba(0,0,0,0.08);
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .medal {
            font-size: 26px;
            margin-bottom: 8px;
        }
        .card h3 {
            margin: 5px 0;
            font-size: 16px;
            color: #34495e;
        }
        .card p {
            font-size: 14px;
            color: #27ae60;
            font-weight: 600;
        }

        /* --- CHART --- */
        .chart {
            display: block;
            margin: 0 auto 35px auto;
            max-width: 92%;
            border-radius: 14px;
            box-shadow: 0 6px 16px rgba(0,0,0,0.15);
        }

        /* --- TABLE --- */
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
            padding: 14px;
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

        footer {
            position: fixed;
            bottom: 15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 11px;
            color: #95a5a6;
        }
    </style>
</head>
<body>
    <h1>📊 Productos Más Vendidos</h1>
    <h2>Reporte</h2>

    <!-- TOP 5 Cards -->
    <div class="top-container">
        @php $i = 1; @endphp
        @foreach($productos->take(5) as $producto)
            <div class="card">
                <div class="medal">
                    @if($i==1) 🥇 @elseif($i==2) 🥈 @elseif($i==3) 🥉 @else ⭐ @endif
                </div>
                <h3>{{ $producto['nombre'] }}</h3>
                <p>{{ $producto['cantidad'] }} unidades</p>
            </div>
            @php $i++; @endphp
        @endforeach
    </div>

    <!-- Chart -->
    <img src="{{ $chartImageSrc }}" alt="Gráfico de Productos" class="chart">

    <!-- Table -->
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad Vendida</th>
            </tr>
        </thead>
        <tbody>
            @foreach($productos as $producto)
                <tr>
                    <td>{{ $producto['nombre'] }}</td>
                    <td>{{ $producto['cantidad'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <footer>
          &copy; {{ date('Y') }} IMBAE. Todos los derechos reservados.
    </footer>
</body>
</html>
