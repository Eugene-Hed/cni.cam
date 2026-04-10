<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        @page {
            size: 85.6mm 54mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: 'Helvetica', sans-serif;
            background: #f5f5f0;
            width: 85.6mm;
            height: 54mm;
            color: #000;
        }
        .card-face {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            page-break-after: always;
            overflow: hidden;
        }
        .header {
            position: absolute;
            top: 2mm;
            left: 15mm;
            width: 65mm;
            text-align: center;
        }
        .republique {
            font-size: 6pt;
            font-weight: bold;
            color: #004600;
            margin: 0;
        }
        .motto {
            font-size: 5pt;
            margin: 0;
        }
        .title {
            font-size: 7pt;
            font-weight: bold;
            color: #960000;
            margin: 1mm 0;
        }
        .logo {
            position: absolute;
            top: 3mm;
            left: 3mm;
            width: 10mm;
        }
        .separator {
            position: absolute;
            top: 12mm;
            left: 3mm;
            right: 3mm;
            height: 0.1mm;
            background: #c8c8c8;
        }
        .photo-container {
            position: absolute;
            top: 14mm;
            left: 3mm;
            width: 25mm;
            height: 30mm;
            border: 0.2mm solid #000;
        }
        .photo-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .info-field {
            position: absolute;
            left: 30mm;
            font-size: 6pt;
        }
        .label {
            font-weight: bold;
        }
        .signature-holder {
            position: absolute;
            top: 40mm;
            left: 30mm;
            width: 35mm;
            height: 8mm;
            border: 0.1mm solid #000;
        }
        .signature-holder img {
            width: 100%;
            height: 100%;
        }
        .signature-label {
            position: absolute;
            top: 48.5mm;
            left: 30mm;
            font-size: 5pt;
            font-style: italic;
        }
        
        /* Verso */
        .qr-code {
            position: absolute;
            top: 5mm;
            left: 5mm;
            width: 20mm;
            height: 20mm;
            border: 0.1mm solid #969696;
        }
        .qr-code img {
            width: 100%;
            height: 100%;
        }
        .officier-signature-area {
            position: absolute;
            top: 30mm;
            left: 45mm;
            width: 35mm;
            text-align: center;
        }
        .officier-label {
            font-size: 6pt;
            font-weight: bold;
        }
        .officier-signature {
            width: 25mm;
            height: 10mm;
            margin-top: 1mm;
        }
        .mrz {
            position: absolute;
            bottom: 2mm;
            left: 5mm;
            font-family: 'Courier', monospace;
            font-size: 8pt;
            font-weight: bold;
            line-height: 4mm;
            letter-spacing: 0.5mm;
        }
        .watermark {
            position: absolute;
            top: 25mm;
            left: 10mm;
            font-size: 15pt;
            color: rgba(220, 220, 220, 0.3);
            transform: rotate(-30deg);
            z-index: -1;
        }
    </style>
</head>
<body>
    <!-- RECTO -->
    <div class="card-face">
        <img class="logo" src="data:image/png;base64,{{ base64_encode(file_get_contents(public_path('assets/images/Cameroun.png'))) }}" />
        
        <div class="header">
            <p class="republique">RÉPUBLIQUE DU CAMEROUN - MINISTÈRE DE L’INTÉRIEUR</p>
            <p class="motto">Paix – Travail – Patrie</p>
            <p class="title">CARTE NATIONALE D’IDENTITÉ</p>
        </div>
        
        <div class="separator"></div>
        
        <div class="photo-container">
            @if($photo)
                <img src="{{ $photo }}" />
            @endif
        </div>
        
        <div class="info-field" style="top: 14mm;">
            <span class="label">NOM / SURNAME:</span> {{ $nom }}
        </div>
        <div class="info-field" style="top: 18mm;">
            <span class="label">PRÉNOMS / GIVEN NAMES:</span> {{ $prenom }}
        </div>
        <div class="info-field" style="top: 22mm;">
            <span class="label">DATE DE NAISSANCE:</span> {{ $date_naissance }}
        </div>
        <div class="info-field" style="top: 26mm;">
            <span class="label">SEXE / SEX:</span> {{ $sexe }}
        </div>
        <div class="info-field" style="top: 30mm;">
            <span class="label">NUMÉRO CNI:</span> <strong>{{ $numero }}</strong>
        </div>
        <div class="info-field" style="top: 34mm;">
            <span class="label">DATE D’EXPIRATION:</span> {{ $date_expiration }}
        </div>
        
        <div class="signature-holder">
            @if($signature_citoyen)
                <img src="{{ $signature_citoyen }}" />
            @endif
        </div>
        <div class="signature-label">Signature du titulaire / Holder’s Signature</div>
    </div>

    <!-- VERSO -->
    <div class="card-face">
        <div class="qr-code">
            <img src="{{ $qr_code }}" />
        </div>
        
        <div class="info-field" style="top: 5mm; left: 30mm; width: 45mm;">
            <span class="label">LIEU DE NAISSANCE / PLACE OF BIRTH:</span><br/>
            {{ $lieu_naissance }}
        </div>
        
        <div class="info-field" style="top: 13mm; left: 30mm; width: 45mm;">
            <span class="label">PROFESSION / OCCUPATION:</span><br/>
            {{ $profession }}
        </div>
        
        <div class="info-field" style="top: 21mm; left: 30mm; width: 45mm;">
            <span class="label">ADRESSE / ADDRESS:</span><br/>
            {{ $adresse }}
        </div>
        
        <div class="info-field" style="top: 29mm; left: 30mm;">
            <span class="label">TAILLE / HEIGHT:</span> {{ $taille }} cm
        </div>
        
        <div class="info-field" style="top: 30mm; left: 5mm;">
            <span class="label">DATE DÉLIVRANCE / ISSUE DATE:</span><br/>
            {{ $date_emission }}
        </div>
        
        <div class="officier-signature-area">
            <div class="officier-label">LE DGNS / DGNS</div>
            @if($signature_officier)
                <img class="officier-signature" src="{{ $signature_officier }}" />
            @endif
        </div>
        
        <div class="mrz">
            {{ $mrz_line1 }}<br/>
            {{ $mrz_line2 }}
        </div>
        
        <div class="watermark">OFFICIEL</div>
    </div>
</body>
</html>
