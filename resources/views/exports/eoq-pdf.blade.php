<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Reporte EOQ Detallado</title>
<style>
body { font-family: 'Poppins', Arial; margin: 30px; color: #2c3e50; background: #f9f9f9; }
h1, h2 { text-align: center; margin-bottom: 5px; }
h2 { font-size: 20px; }
.intro { margin-bottom: 20px; }
.insumo { border: 1px solid #ccc; border-radius: 12px; padding: 20px; margin-bottom: 30px; background: #fff; box-shadow: 0 2px 6px rgba(0,0,0,0.05);}
.insumo h3 { margin-top: 0; }
table { width: 100%; border-collapse: collapse; margin-top: 15px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: center; font-size: 13px; }
th { background: #34495e; color: white; }
.recommend { font-weight: bold; margin-top: 10px; display: block; padding: 8px; border-radius: 6px; }
.recommend.increase { color: #27ae60; background: #eafaf1; }
.recommend.decrease { color: #e67e22; background: #fdf2e9; }
.recommend.stable { color: #2980b9; background: #eaf1fd; }
.chart { text-align: center; margin-top: 15px; }
.footer { margin-top: 30px; font-size: 12px; color: #555; text-align: center; }
ul { margin-top: 5px; }
</style>
</head>
<body>

<h1>📦 Reporte EOQ Detallado</h1>
<h2>Optimización de Inventarios</h2>

<div class="intro">
    <h2>📘 ¿Qué es EOQ (Cantidad Económica de Pedido)?</h2>
    <p>El EOQ permite determinar la cantidad óptima de cada insumo para minimizar costos de pedidos y almacenamiento.  
    Cada insumo se analiza de forma individual usando:</p>
    <ul>
        <li><strong>Costo de pedido (Cp):</strong> gasto por realizar un pedido.</li>
        <li><strong>Costo de mantenimiento (Cm):</strong> costo anual por unidad almacenada, calculado como precio unitario × tasa de mantenimiento.</li>
        <li><strong>Demanda anual (D):</strong> consumo histórico en unidades.</li>
    </ul>
    <p>Fórmula EOQ:</p>
    <p style="text-align:center; font-weight:bold; font-size:16px;">EOQ = √(2 × D × Cp / Cm)</p>
</div>

<p><strong>Total insumos:</strong> {{ $totalInsumos }} | <strong>Con demanda:</strong> {{ $conDemanda }} | <strong>Sin demanda:</strong> {{ $sinDemanda }}</p>

@foreach($eoqData as $i => $item)
<div class="insumo">
<h3>{{ $i+1 }}. {{ $item['nombre'] }}</h3>

<p><strong>Unidad Base (medida interna):</strong> 
{{ $item['unidad_base_nombre'] ?? '-' }} ({{ $item['unidad_base_nombre_completo'] ?? '-' }})  
| <strong>Unidad de Compra:</strong> 
{{ $item['unidad_compra_nombre'] ?? '-' }} ({{ $item['unidad_compra_nombre_completo'] ?? '-' }})</p>

@if(isset($item['demanda_anual']))
<p><strong>📘 Explicación:</strong><br>
La Unidad Base es la medida interna usada para calcular la producción.  
La Unidad de Compra indica cómo adquirimos el insumo del proveedor. La relación entre ambas se obtiene de la equivalencia de la unidad.</p>

<p><strong>📊 Demanda histórica del último año:</strong> {{ $item['demanda_anual'] }} {{ $item['unidad_base_nombre_completo'] ?? $item['unidad_base_nombre'] }}</p>

<p><strong>💰 Precio y costos:</strong><br>
- Precio unitario: {{ $item['precio_unitario_base'] }} Bs por unidad<br>
<tr><td>- Costo de mantenimiento (Cm total incluyendo almacén, refrigeración, seguro y caducidad)</td>
<td>{{ $item['costo_mantenimiento'] }} Bs por unidad/año</td></tr>

</p>

<table>
<tr><th>Concepto</th><th>Valor</th></tr>
<tr><td>Demanda anual (D)</td><td>{{ $item['demanda_anual'] }} {{ $item['unidad_base_nombre_completo'] ?? $item['unidad_base_nombre'] }}</td></tr>
<tr><td>Costo de pedido (Cp)</td><td>{{ $item['costo_pedido'] ?? 20}} Bs por pedido</td></tr>
<tr><td>Precio unitario (P)</td><td>{{ $item['precio_unitario_base'] }} Bs</td></tr>
<tr><td>Costo de mantenimiento (Cm = P × tasa)</td><td>{{ $item['costo_mantenimiento'] }} Bs por unidad/año</td></tr>
<tr><td>EOQ sugerido</td><td>{{ $item['eoq_sugerido'] }} {{ $item['unidad_compra_nombre_completo'] ?? $item['unidad_compra_nombre'] }}</td></tr>
</table>

@if($item['grafica'])
<div class="chart">
<img src="{{ $item['grafica'] }}" alt="Gráfica EOQ vs Demanda" width="400">
</div>
@endif

<span class="recommend
    {{ str_contains($item['recomendacion'], 'aumentar') ? 'increase' : '' }}
    {{ str_contains($item['recomendacion'], 'reducir') ? 'decrease' : '' }}
    {{ str_contains($item['recomendacion'], 'Mantener') ? 'stable' : '' }}">
<strong>💡 Recomendación:</strong> {{ $item['recomendacion'] }}</span>

<p><strong>📌 Mensaje práctico:</strong><br>
{{ $item['mensaje_interpretativo'] }}</p>

<p><strong>🔍 Interpretación:</strong> Siguiendo la EOQ sugerida, cubrimos la demanda histórica sin exceso de inventario ni faltantes.  
No seguir esta recomendación puede generar desabastecimiento o mayores costos de almacenamiento.</p>

<p><strong>📌 Observaciones estratégicas:</strong>
<ul>
@if(!isset($item['demanda_anual']))
<li>Revisar si este insumo es necesario mantenerlo en inventario.</li>
<li>Evaluar posibles bajas de stock o reubicación de recursos.</li>
@elseif(str_contains($item['recomendacion'], 'reducir'))
<li>Considerar reducir la compra de este insumo para liberar capital.</li>
<li>Monitorear la estacionalidad de su consumo.</li>
<li>Mantener stock mínimo de seguridad.</li>
@elseif(str_contains($item['recomendacion'], 'aumentar'))
<li>Incrementar pedidos para evitar desabastecimiento.</li>
<li>Revisar proveedores para garantizar disponibilidad.</li>
<li>Analizar posibles promociones o picos de demanda futuros.</li>
@else
<li>Seguir con la cantidad sugerida.</li>
<li>Monitorear costos de almacenamiento y variaciones de precio.</li>
<li>Revisar demanda mensual para ajustes finos.</li>
@endif
</ul>
</p>

@else
<p>{{ $item['mensaje'] }}</p>
@endif
</div>
@endforeach

<div class="footer">
<p>Este reporte detalla cada insumo, mostrando demanda histórica, cálculo EOQ paso a paso, mensaje práctico, gráfica comparativa y recomendaciones.  
Ideal para decisiones de compras y optimización de inventario.</p>
</div>

</body>
</html>
