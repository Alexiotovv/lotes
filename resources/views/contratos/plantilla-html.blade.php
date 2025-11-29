<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contrato de Compra Venta</title>
    <style>
        button {
            margin-top: 15px;
            padding: 6px 12px;
            background-color: #0d6efd;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 11px;
        }
        @layer utilities {
            .page-break { page-break-before: always; }
            .break-before-page { break-before: page; }
        }
        /* ✅ Estilo general para vista en pantalla */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f5f5f5; /* Fondo exterior */
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            line-height: 1.5;
            
        }

        .a4-container {
            width: 210mm; /* Ancho A4 */
            min-height: 297mm; /* Alto A4 */
            background-color: white;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Efecto de hoja real */
            padding: 15mm; /* Margen interno */
            box-sizing: border-box;
            margin-top: 10mm; /* Margen superior en pantalla */
            margin-bottom: 10mm; /* Margen inferior en pantalla */
        }

        .header { text-align: center; margin-bottom: 20px; }
        .logo { max-width: 100px; float: left; }
        .empresa-info { text-align: right; }
        .section-title { font-weight: bold; margin: 20px 0 10px; }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 10px 0;
        }
        td {
            padding: 5px;
            vertical-align: top;
        }

        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 12px;
            padding-top: 430px
        }

         button:hover {
            background-color: #0a58ca;
        }
        /* ✅ Estilo para impresión */
        @media print {
            .page-break {
                page-break-before: always;
            }
            button { display: none; }

            body {
                background-color: white; /* Quitar fondo gris al imprimir */
                display: block; /* Cambiar a bloque */
                align-items: normal; /* Quitar centrado vertical */
                
            }

            .a4-container {
                box-shadow: none; /* Quitar sombra */
                margin: 0; /* Quitar márgenes de pantalla */
                padding-top: 5mm; /* Asegurar márgenes internos */
                padding-bottom: 5mm;
                padding-left: 5mm;
                padding-right: 5mm;
            }

            /* ✅ Forzar márgenes de impresión */
            @page {
                margin: 20mm 20mm; /* Margen superior/inferior: 20mm, laterales: 15mm */
                size: A4;
            }

            /* Opcional: Forzar salto de página antes o después si hay varios contratos */
            /* .a4-container { break-inside: avoid; } */
        }
        li {
            text-align: justify;
            text-justify: inter-word;
        }
        p{
            text-align: justify;
            text-justify: inter-word;
        }
    </style>
</head>
<body>
    <div class="a4-container">
        <div class="header">
            <button onclick="window.print()">🖨️ Imprimir</button>

            @if($empresa->logo)
                <img src="{{ asset('storage/' . $empresa->logo) }}" alt="Logo" class="logo">
            @endif
            <div class="empresa-info">
                <strong>{{ $empresa->nombre }}</strong><br>
                RUC: {{ $empresa->ruc }}<br>
                Domicilio: {{ $empresa->direccion }}, {{ $empresa->distrito }}, {{ $empresa->provincia }}, {{ $empresa->departamento }}
            </div>
        </div>

        <h2 style="text-align: center;">CONTRATO DE COMPRA VENTA DE LOTE CON DERECHOS POSESORIOS</h2>

        <p>MEDIANTE EL PRESENTE ACTO SE CELEBRA EL CONTRATO DE COMPROMISO DE COMPRA VENTA DE DERECHOS POSESORIOS DE INMUEBLE.=================</p>
        <div class="section-title">DE UNA PARTE:=====================================================</div>
        <p>LA EMPRESA, {{ $empresa->nombre }}, IDENTIFICADO CON RUC N° {{ $empresa->ruc }}, CON DOMICILIO UBICADO EN LA {{ $empresa->direccion }}, DISTRITO DE {{ $empresa->distrito }}, PROVINCIA DE {{ $empresa->provincia }}, DEPARTAMENTO DE {{ $empresa->departamento }}.</p>

        <div class="section-title">Y DE OTRA PARTE:==================================================</div>
        <p>Sr.(a). {{ $cliente->nombre_cliente }}, PERUANA DE NACIMIENTO, IDENTIFICADA CON DNI N° {{ $cliente->dni_ruc }}, CON DOMICILIO {{ $cliente->direccion ?? 'N/A' }}, DISTRITO {{ $cliente->distrito ?? 'N/A' }}, PROVINCIA {{ $cliente->provincia ?? 'N/A' }}, DEPARTAMENTO {{ $cliente->departamento ?? 'N/A' }}.</p>



        <div class="section-title">DE PARTE DEL VENDEDOR:============================================</div>
        <ol>
            <li>QUE ES UNA PERSONA JURÍDICA QUE TIENE POR GERENTE AL SEÑOR SIR 
                HENRY HUAMAN AMASIFUEN QUE SE ENCUENTRA EN PLENO EJERCICIO DE 
                SU DERECHO Y QUE CUENTA CON LA CAPACIDAD LEGAL PARA CONTRATAR 
                EN NOMBRE DEL VENDEDOR.=====================================</li>
            <li>SE MENCIONA QUE EN LA ACTUALIDAD EL PREDIO SE ENCUENTRA LIBRE Y
                 CONSIDERADO SOLO COMO TERRENO, PUNTO QUE EL COMPRADOR TIENE 
                 TOTAL CONOCIMIENTO, HABIENDO INCLUSO YA VERIFICADO EL PREDIO; 
                 ES IMPORTANTE MENCIONAR QUE EL PREDIO A VENDER SE VENDERÁ SOLO 
                 COMO LOTE DE TERRENO, SIN LUZ, AGUA Y DESAGUE, POR LO QUE ESTAS 
                 GESTIONES SE ENCARGARA EL COMPRADOR.=======================</li>
            <li>EN CASO DE SINIESTROS NATURALES DE CUALQUIER TIPO, INCENDIO, ASENTAMIENTO 
                DEL SUELO Y/O SUBSUELO, ETC. EL VENDEDOR NO SE HACE RESPONSABLE POR DICHO ACONTECIMIENTO 
                Y EL COMPRADOR TIENE CONOCIMIENTO Y ACEPTA TODO LO ANTES MENCIONADO.========</li>
        </ol>

        <div class="section-title">DE PARTE DEL COMPRADOR:=========================================</div>
        <ol>
            <li>QUE ES UNA PERSONA NATURAL EN PLENO EJERCICIO DE SU DERECHO Y QUE CUENTA CON LA CAPACIDAD 
                LEGAL NECESARIA PARA LA CELEBRACION DE ESTE CONTRATO.============================================</li>
            <li>QUE CONOCE EL PREDIO OBJETO DE ESTE CONTRATO, MANIFESTANDO QUE ESTA CONFORME CON LO 
                DECLARADO.=========================</li>
        </ol>
        <div class="page-break"></div>
        <div class="section-title">ACUERDO DE LAS PARTES:========================================</div>
        <ol>
            @php
                $mz = substr($lote->codigo, 0, 1);       // Primera letra
                $lt = substr($lote->codigo, 1);          // Resto del código
            @endphp
            <li>QUE LOS BIENES MATERIA DEL PRESENTE CONTRATO SE ENCUENTRAN UBICADOS EN          PRED.RUST.DEN.SECTOR PEÑA NEGRA 13-MARGEN DERECHO DE LA CARRETERA IQUITOS – NAUTA KM.11.00 AREA Ha. 10HA 5835.24 M2 U.C. 026408. SAN JUAN BAUTISTA, PROVINCIA MAYNAS, DEPARTAMENTO LORETO, INSCRITO CON PARTIDA REGISTRAL N° 11016989.==========.</li>
            <li>QUE LOS LOTES MATERIA DEL PRESENTE CONTRATO (LOTE N° {{$lt}} PERTENECIENTE A LA MANZANA “{{$mz}}”) CUENTAN CON LAS SIGUIENTES MEDIDAS: ======================================</li>
            <table>
                <tr>
                    <td>Lote:</td>
                    <td>{{ $lote->codigo }}</td>
                </tr>
                <tr>
                    <td>Por el frente:</td>
                    <td>{{ $lote->frente }} m</td>
                </tr>
                <tr>
                    <td>Por el costado derecho:</td>
                    <td>{{ $lote->lado_derecho }} m</td>
                </tr>
                <tr>
                    <td>Por el costado izquierdo:</td>
                    <td>{{ $lote->lado_izquierdo }} m</td>
                </tr>
                <tr>
                    <td>Por el fondo:</td>
                    <td>{{ $lote->fondo }} m</td>
                </tr>
            </table>
            <li>EL VENDEDOR ESTA OBLIGADO A VENDER AL COMPRADOR Y ESTE ESTARA OBLIGADO A COMPRAR 
                EL LOTE N° {{ $lt }} UBICADO EN LA MZ. "{{ $mz }}" DEL INMUEBLE
                ANTES MENCIONADO EN UN PLAZO DE {{ $venta->numero_cuotas }} MESES,
                CONTADOS A PARTIR DEL DIA SIGUIENTE A LA FIRMA DE ESTE CONTRATO.============</li>
            <li>LAS PARTES FIJAN POR MUTUO ACUERDO COMO PRECIO DE LA FUTURA COMPRA VENTA LA
                CANTIDAD TOTAL DE S/{{ number_format($venta->monto_financiar + $venta->inicial, 2) }} 
                MIL CON 00/100 SOLES), QUE TENDRA QUE SER CANCELADO EN {{ $venta->numero_cuotas }} 
                CUOTAS MENSUALES DE S/{{ number_format($venta->cuota, 2) }} 
                ({{ number_format($venta->cuota, 2) }}/100 SOLES), QUE ESTARA DETALLADA EN EL 
                ANEXO DEL CONTRATO.===================</li>
            <li>CABE INDICAR QUE EL COMPRADOR PUEDE AMORTIZAR Y LIQUIDAR EN UN FUTURO Y DE 
                ESTA MANERA PODRA RECIBIR SU TITULO EN EL MENOR TIEMPO POSIBLE.</li>
            <li>EL VENDEDOR SE OBLIGA AL SANEAMIENTO EN CASO DE HIPOTECAS, Y OTROS PARA LA 
                CONFORMIDAD DEL COMPRADOR DE CONFORMIDAD CON EL CODIGO CIVIL DE NUESTRA 
                LEGISLACIÓN Y SUS PERTINENTES ARTICULOS.===============</li>
            <li>EL VENDEDOR SE COMPROMETE A PROCEDER CON LA INDEPENDIZACION Y EL CAMBIO DE 
                TITULARIDAD EN LA PARTIDA REGISTRAL UNA VEZ QUE EL COMPRADOR HAYA CANCELADO LA 
                TOTALIDAD DEL VALOR DE VENTA DEL RESPECTIVO LOTE.=============</li>
            <li>EL VENDEDOR ASUME LOS GASTOS NOTARIALES.============</li>
            <li>EL COMPRADOR ASUME LOS GASTOS DE INDEPENDIZACION DE SU LOTE LO CUAL SE LE
                INDICARA EN SU MOMENTO.===================</li>
            <li>EL COMPRADOR ASUME EL GASTO DEL CAMBIO DE NOMBRE Y/O TITULARIDAD, ASI COMO LOS
                 IMPUESTOS PREDIALES.==================</li>
            <li>EL COMPRADOR TIENE CONOCIMIENTO QUE LA ENTREGA DEL LOTE ES EN ESTADO NATURAL, 
                SIN EMBARGO, EL VENDEDOR SE COMPROMETE A REALIZAR LA ENTREGA DEL LOTE SIN 
                VEGETACION.==============</li>
            <li>EL COMPRADOR AL RECEPCIONAR LA ENTREGA DEL LOTE SE HACE RESPONSABLE DE LA 
                CONSERVACION Y CUIDADO DE SU PERIMETRO, ASI MISMO SE HACE RESPONSABLE DE 
                RECORDAR LA UBICACIÓN DE SU LOTE Y ASUMIR LOS IMPUESTOS PREDIALES MUNICIPALES, 
                PESE A QUE ESTE AÚN FIGURE A NOMBRE DEL VENDEDOR.=======</li>
            <li>EL PRESENTE CONTRATO TOMARA VIGENCIA EN EL MOMENTO EN QUE LAS PARTES PROCEDAN 
                A FIRMARLOS.==================</li>
        </ol>
        

        <div class="section-title">PENALIDADES:</div>
        <ol>
            <li>EL PRESENTE CONTRATO SE RESOLVERA A FAVOR DE LA VENDEDORA SI EL COMPRADOR INCUMPLE 
                CON EL PAGO DE 02 (DOS) CUOTAS CONSECUTIVAS DE LAS 24 (VEINTICUATRO) CUOTAS 
                ESPECIFICADAS POR LOTE; EN CASO DE QUE EL COMPRADOR YA ESTE OCUPANDO EL LOTE 
                RESPECTIVO, ESTE SE VERA OBLIGADO A DESALOJARLO EN UN PLAZO MAXIMO DE 48 HORAS,
                 CASO CONTRARIO SE PROCEDERÁ A ACTUAR CONFORME A LEY.=============</li>
            <li>EN CASO DE EXISTIR UNA CONTRAVERSIA RESPECTO AL CUMPLIMIENTO DE ALGUNA DE LAS 
                CLAUSULAS POR CUALQUIERA DE LAS PARTES, SE PROCEDERÁ DE MANERA OBLIGATORIA A 
                RESOLVERLO MEDIANTE CONCILIACIÓN EXTRAJUDICIAL, DE NO PODER LLEGAR A UN ACUERDO 
                CONCILIATORIO ENTRE LAS PARTES, ESTAS SE SOMETEN A ACTUAR CONFORME A LEY Y BAJO
                 LA LEGISLACIÓN Y ARTICULOS CORRESPONDIENTE DE NUESTRO CODIGO CIVIL.===============</li>
        </ol>

        <div class="section-title">LEGISLACIÓN Y ARTICULOS CORRESPONDIENTE DE 
            NUESTRO CODIGO CIVIL</div>
        <ol>
            <li>EN EL CASO DE LA DISOLUCIÓN ABSOLUTA DEL CONTRATO, LAS PARTES ACUERDAN 
                QUE NO HABRA DEVOLUCION DE NINGUNO DE LOS PAGOS, NI LAS INICIALES, 
                NI LAS CUOTAS QUE SE HAYAN CANCELADO HASTA EL MOMENTO EN EL QUE SE DESISTA 
                DEL PRESENTE ACTO, YA SEA POR VOLUNTAD O FORZADO POR LA LEY.====================</li>
        </ol>

        <div class="footer">
            CELULAR: {{$empresa->telefono}}<br>
            {{$empresa->departamento}} - {{$empresa->provincia}} - {{$empresa->distrito}}<br>
            El presente contrato fue leído y aceptado de conformidad por ambas partes y prueba de ello firman y legalizan sus firmas ante notario públicos para efecto de la ley.
        </div>
    </div>
</body>
</html>