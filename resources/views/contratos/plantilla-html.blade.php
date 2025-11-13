<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Compra Venta</title>
    <style>
        /* ✅ Estilo para simular tamaño A4 */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5; /* Fondo exterior */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
        }

        .a4-container {
            width: 210mm; /* Ancho A4 */
            min-height: 297mm; /* Alto A4 */
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Sombra para efecto de hoja */
            padding: 20mm; /* Margen interior */
            box-sizing: border-box;
            margin-top: 10mm; /* Espacio superior */
            margin-bottom: 10mm; /* Espacio inferior */
        }

        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 100px; float: left; }
        .empresa-info { text-align: right; }
        .section-title { font-weight: bold; margin: 20px 0 10px; }
        /* .highlight-yellow { background-color: #ffff00; padding: 2px 5px; }
        .highlight-red { background-color: #ffcccc; padding: 2px 5px; }
        .highlight-blue { background-color: #ccffff; padding: 2px 5px; }
        .highlight-green { background-color: #ccffcc; padding: 2px 5px; } */
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        td { padding: 5px; vertical-align: top; }
        .footer { text-align: center; margin-top: 50px; font-size: 12px; }
    </style>
</head>
<body>
    <div class="a4-container">
        <div class="header">
            @if($empresa->logo)
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="empresa-info">
                <strong><span class="highlight-yellow">{{ $empresa->nombre }}</span></strong><br>
                RUC: <span class="highlight-yellow">{{ $empresa->ruc }}</span><br>
                Domicilio: <span class="highlight-yellow">{{ $empresa->direccion }}, {{ $empresa->distrito }}, {{ $empresa->provincia }}, {{ $empresa->departamento }}</span>
            </div>
        </div>

        <h2 style="text-align: center;">CONTRATO DE COMPRA VENTA DE LOTE CON DERECHOS POSESIORIOS</h2>

        <div class="section-title">DE UNA PARTE:</div>
        <p>LA EMPRESA, <span class="highlight-yellow">{{ $empresa->nombre }}</span>, IDENTIFICADO CON RUC N° <span class="highlight-yellow">{{ $empresa->ruc }}</span>, CON DOMICILIO UBICADO EN LA <span class="highlight-yellow">{{ $empresa->direccion }}</span>, DISTRITO DE <span class="highlight-yellow">{{ $empresa->distrito }}</span>, PROVINCIA DE <span class="highlight-yellow">{{ $empresa->provincia }}</span>, DEPARTAMENTO DE <span class="highlight-yellow">{{ $empresa->departamento }}</span>.</p>

        <div class="section-title">Y DE OTRA PARTE:</div>
        <p>Sr.(a). <span class="highlight-red">{{ $cliente->nombre_cliente }}</span>, PERUANA DE NACIMIENTO, IDENTIFICADA CON DNI N° <span class="highlight-red">{{ $cliente->dni_ruc }}</span>, CON DOMICILIO <span class="highlight-red">{{ $cliente->direccion ?? 'N/A' }}</span>, DISTRITO <span class="highlight-red">{{ $cliente->distrito ?? 'N/A' }}</span>, PROVINCIA <span class="highlight-red">{{ $cliente->provincia ?? 'N/A' }}</span>, DEPARTAMENTO <span class="highlight-red">{{ $cliente->departamento ?? 'N/A' }}</span>.</p>

        <div class="section-title">DE PARTE DEL VENDEDOR:</div>
        <ol>
            <li>QUE ES UNA PERSONA JURIDICA QUE TIENE POR GERENTE AL SEÑOR SIR HENRY HUAMAN AMASIFUEN QUE SE ENCUENTRA EN PLENO EJERCICIO DE SU DERECHO Y QUE CUENTA CON LA CAPACIDAD LEGAL PARA CONTRATAR EN NOMBRE DEL VENDEDOR.</li>
            <li>SE MENCIONA QUE EN LA ACTUALIDAD EL PREDIO SE ENCUENTRA LIBRE Y CONSIDERADO SOLO COMO TERRENO, PUNTO QUE EL COMPRADOR TIENE TOTAL CONOCIMIENTO, HABIENDO INCLUSO YA VERIFICADO EL PREDIO; ES IMPORTANTE MENCIONAR QUE EL PREDIO A VENDER SE VENDERA SOLO COMO LOTE DE TERRENO, SIN LUZ, AGUA Y DESAGUE, POR LO QUE ESTAS GESTIONES SE ENCARGARA EL COMPRADOR.</li>
            <li>EN CASO DE SINIESTROS NATURALES DE CUALQUIER TIPO, INCENDIO, ASENTAMIENTO DEL SUELO Y/O SUBSUELO, ETC. EL VENDEDOR NO SE HACE RESPONSABLE POR DICHO ACONTECIMIENTO Y EL COMPRADOR TIENE CONOCIMIENTO Y ACEPTA TODO LO ANTES MENCIONADO.</li>
        </ol>

        <div class="section-title">DE PARTE DEL COMPRADOR:</div>
        <ol>
            <li>QUE ES UNA PERSONA NATURAL EN PLENO EJERCICIO DE SU DERECHO Y QUE CUENTA CON LA CAPACIDAD LEGAL NECESARIA PARA LA CELEBRACION DE ESTE CONTRATO.</li>
            <li>QUE CONOCE EL PREDIO OBJETO DE ESTE CONTRATO, MANIFESTANDO QUE ESTA CONFORME CON LO DECLARADO.</li>
        </ol>

        <div class="section-title">ACUERDO DE LAS PARTES:</div>
        <ol>
            <li>QUE LOS BIENES MATERIA DEL PRESENTE CONTRATO SE ENCUENTRAN UBICADOS EN <span class="highlight-blue">{{ $lote->codigo }} - {{ $lote->nombre }}</span>, {{ $lote->descripcion ?? '' }}.</li>
            <li>QUE LOS LOTES MATERIA DEL PRESENTE CONTRATO CUENTAN CON LAS SIGUIENTES MEDIDAS:</li>
            <table>
                <tr>
                    <td>Lote:</td>
                    <td><span class="highlight-blue">{{ $lote->codigo }}</span></td>
                </tr>
                <tr>
                    <td>Por el frente:</td>
                    <td><span class="highlight-blue">{{ $lote->frente }} m</span></td>
                </tr>
                <tr>
                    <td>Por el costado derecho:</td>
                    <td><span class="highlight-blue">{{ $lote->lado_derecho }} m</span></td>
                </tr>
                <tr>
                    <td>Por el costado izquierdo:</td>
                    <td><span class="highlight-blue">{{ $lote->lado_izquierdo }} m</span></td>
                </tr>
                <tr>
                    <td>Por el fondo:</td>
                    <td><span class="highlight-blue">{{ $lote->fondo }} m</span></td>
                </tr>
            </table>
            <li>EL VENDEDOR ESTA OBLIGADO A VENDER AL COMPRADOR Y ESTE ESTARA OBLIGADO A COMPRAR EL LOTE N° <span class="highlight-green">{{ $lote->codigo }}</span> UBICADO EN LA MZ. <span class="highlight-green">{{ $lote->nombre }}</span> DEL INMUEBLE ANTES MENCIONADO EN UN PLAZO DE VEINTICUATRO (24) MESES, <span class="highlight-green">{{ $venta->numero_cuotas }}</span> AÑOS, CONTADOS A PARTIR DEL DIA SIGUIENTE A LA FIRMA DE ESTE CONTRATO.</li>
            <li>LAS PARTES FIJAN POR MUTUO ACUERDO COMO PRECIO DE LA FUTURA COMPRA VENTA LA CANTIDAD TOTAL DE S/<span class="highlight-green">{{ number_format($venta->monto_financiar + $venta->inicial, 2) }}</span> MIL CON 00/100 SOLES), QUE TENDRA QUE SER CANCELADO EN <span class="highlight-green">{{ $venta->numero_cuotas }}</span> CUOTAS MENSUALES DE S/<span class="highlight-green">{{ number_format($venta->cuota, 2) }}</span> ({{ number_format($venta->cuota, 2) }}/100SOLES), QUE ESTARA DETALLADA EN EL ANEXO DEL CONTRATO.</li>
            <li>CABE INDICAR QUE EL COMPRADOR PUEDE AMORTIZAR Y LIQUIDAR EN UN FUTURO Y DE ESTA MANERA PODRA RECIBIR SU TITULO EN EL MENOR TIEMPO POSIBLE.</li>
            <li>EL VENDEDOR SE OBLIGA AL SANEAMIENTO EN CASO DE HIPOTECAS, Y OTROS PARA LA CONFORMIDAD DEL COMPRADOR DE CONFORMIDAD CON EL CODIGO CIVIL DE NUESTRA LEGISLACION Y SUS PERTINENTES ARTICULOS.</li>
            <li>EL VENDEDOR SE COMPROMETE A PROCEDER CON LA INDEPENDIZACION Y EL CAMBIO DE TITULARIDAD EN LA PARTIDA REGISTRAL UNA VEZ QUE EL COMPRADOR HAYA CANCELADO LA TOTALIDAD DEL VALOR DE VENTA DEL RESPECTIVO LOTE.</li>
            <li>EL VENDEDOR ASUME LOS GASTOS NOTARIALES.</li>
            <li>EL COMPRADOR ASUME LOS GASTOS DE INDEPENDIZACION DE SU LOTE LO CUAL SE LE INDICARA EN SU MOMENTO.</li>
            <li>EL COMPRADOR ASUME EL GASTO DEL CAMBIO DE NOMBRE Y/O TITULARIDAD, ASI COMO LOS IMPUESTOS PREDIALES.</li>
            <li>EL COMPRADOR TIENE CONOCIMIENTO QUE LA ENTREGA DEL LOTE ES EN ESTADO NATURAL, SIN EMBARGO, EL VENDEDOR SE COMPROMETE A REALIZAR LA ENTREGA DEL LOTE SIN VEGETACION.</li>
            <li>EL COMPRADOR AL RECEPCIONAR LA ENTREGA DEL LOTE SE HACE RESPONSABLE DE LA CONSERVACION Y CUIDADO DE SU PERIMETRO, ASI MISMO SE HACE RESPONSABLE DE RECORDAR LA UBICACIÓN DE SU LOTE Y ASUMIR LOS IMPUESTOS PREDIALES MUNICIPALES, Pese a que este aun figure a nombre del vendedor.</li>
            <li>EL PRESENTE CONTRATO TOMARA VIGENCIA EN EL MOMENTO EN QUE LAS PARTES PROCEDAN A FIRMARLOS.</li>
        </ol>

        <div class="section-title">PENALIDADES:</div>
        <ol>
            <li>EL PRESENTE CONTRATO SE RESOLVERA A FAVOR DE LA VENDEDORA SI EL COMPRADOR INCUMPLE CON EL PAGO DE 02 (DOS) CUOTAS CONSECUTIVAS DE LAS 24 (VEINTICUATRO) CUOTAS ESPECIFICADAS POR LOTE; EN CASO DE QUE EL COMPRADOR YA ESTE OCUPANDO EL LOTE RESPECTIVO, ESTE SE VERA OBLIGADO A DESALOJARLO EN UN PLAZO MAXIMO DE 48 HORAS, CASO CONTRARIO SE PROCEDERA A ACTUAR CONFORME A LEY.</li>
            <li>EN CASO DE EXISTIR UNA CONTRAVERSIA RESPECTO AL CUMPLIMIENTO DE ALGUNA DE LAS CLAUSULAS POR CUALQUIERA DE LAS PARTES, SE PROCEDERA DE MANERA OBLIGATORIA A RESOLVERLO MEDIANTE CONCILIACION EXTRAJUDICIAL, DE NO PODER LLEGAR A UN ACUERDO CONCILIATORIO ENTRE LAS PARTES, ESTAS SE SOMETEN A ACTUAR CONFORME A LEY Y BAJO LA LEGISLACION Y ARTICULOS CORRESPONDIENTE DE NUESTRO CODIGO CIVIL.</li>
        </ol>

        <div class="section-title">LEGISLACION Y ARTICULOS CORRESPONDIENTE DE NUESTRO CODIGO CIVIL</div>
        <ol>
            <li>EN EL CASO DE LA DISOLUCION ABSOLUTA DEL CONTRATO, LAS PARTES ACUERDAN QUE NO HABRA DEVOLUCION DE NINGUNO DE LOS PAGOS, NI LAS INICIALES, NI LAS CUOTAS QUE SE HAYAN CANCELADO HASTA EL MOMENTO EN EL QUE SE DESISTA DEL PRESENTE ACTO, YA SEA POR VOLUNTAD O FORZADO POR LA LEY.</li>
        </ol>

        <div class="footer">
            CELULAR: <span class="highlight-yellow">958679223</span><br>
            Loretto - Maynas<br>
            El presente contrato fue leído y aceptado de conformidad por ambas partes y prueba de ello firman y legalizan sus firmas ante notario públicos para efecto de la ley.
        </div>
    </div>
</body>
</html>