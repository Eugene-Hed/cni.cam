<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body {
            font-family: 'Times-Roman', serif;
            padding: 20mm;
            line-height: 1.6;
        }
        .header {
            text-align: center;
            margin-bottom: 20mm;
        }
        .republique {
            font-size: 14pt;
            font-weight: bold;
            margin: 0;
        }
        .motto {
            font-size: 10pt;
            font-style: italic;
            margin: 0;
        }
        .title {
            font-size: 18pt;
            font-weight: bold;
            text-decoration: underline;
            margin-top: 10mm;
        }
        .content {
            font-size: 12pt;
            text-align: justify;
        }
        .field {
            font-weight: bold;
        }
        .signature-area {
            margin-top: 30mm;
            float: right;
            width: 80mm;
            text-align: center;
        }
        .signature-img {
            max-width: 60mm;
            max-height: 20mm;
            margin-top: 5mm;
        }
        .footer {
            clear: both;
            margin-top: 50mm;
            font-size: 9pt;
            border-top: 1px solid #ccc;
            padding-top: 5mm;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="republique">RÉPUBLIQUE DU CAMEROUN</p>
        <p class="motto">Paix – Travail – Patrie</p>
        <h1 class="title">CERTIFICAT DE NATIONALITÉ</h1>
        <p>N° {{ $numero }}</p>
    </div>

    <div class="content">
        <p>Le Président de la République certifie par la présente que :</p>
        
        <p>M./Mme <span class="field">{{ $nom }} {{ $prenom }}</span></p>
        <p>Né(e) le <span class="field">{{ $date_naissance }}</span> à <span class="field">{{ $lieu_naissance }}</span></p>
        <p>Fils/Fille de <span class="field">{{ $nom_pere }}</span> et de <span class="field">{{ $nom_mere }}</span></p>
        
        <p>Est de nationalité <span class="field">CAMEROUNAISE</span> en vertu des dispositions relatives à la <span class="field">{{ $motif }}</span>.</p>
        
        <p>En foi de quoi, le présent certificat est délivré pour servir et valoir ce que de droit.</p>
    </div>

    <div class="signature-area">
        <p>Fait à Yaoundé, le {{ $date_emission }}</p>
        <p><strong>Le Président de la République</strong></p>
        @if($signature_president)
            <img class="signature-img" src="{{ $signature_president }}" />
        @endif
    </div>

    <div class="footer">
        Document officiel généré par la plateforme CNI.CAM - {{ now()->format('Y') }}
    </div>
</body>
</html>
