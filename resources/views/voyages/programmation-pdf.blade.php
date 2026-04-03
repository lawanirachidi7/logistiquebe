<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Programmation Journalière - {{ $dateFormatted }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            line-height: 1.3;
            padding: 15px;
        }
        
        .header {
            display: table;
            width: 100%;
            margin-bottom: 15px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        
        .header-left {
            display: table-cell;
            width: 30%;
            vertical-align: middle;
        }
        
        .header-center {
            display: table-cell;
            width: 40%;
            text-align: center;
            vertical-align: middle;
        }
        
        .header-right {
            display: table-cell;
            width: 30%;
            text-align: right;
            vertical-align: middle;
        }
        
        .logo-container {
            text-align: left;
        }
        
        .logo-img {
            max-height: 60px;
            width: auto;
        }
        
        .logo-text {
            font-size: 16px;
            font-weight: bold;
            color: #c41e3a;
        }
        
        .logo-text-express {
            font-size: 14px;
            font-style: italic;
            color: #2d8f47;
        }
        
        .logo-subtext {
            font-size: 10px;
            color: #666;
        }
        
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
        }
        
        .date-box {
            font-size: 14px;
            padding: 5px 10px;
            border: 1px solid #333;
            display: inline-block;
        }
        
        .date-label {
            font-weight: normal;
        }
        
        .date-value {
            font-weight: bold;
            color: #1a5f7a;
        }
        
        .section {
            margin-top: 15px;
            margin-bottom: 20px;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            background-color: #1a5f7a;
            color: white;
            padding: 6px 12px;
            margin-bottom: 0;
            text-align: center;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        
        th, td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            vertical-align: middle;
        }
        
        th {
            background-color: #f5f5f5;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }
        
        td {
            font-size: 11px;
        }
        
        .col-numero {
            width: 5%;
            text-align: center;
        }
        
        .col-immat {
            width: 15%;
        }
        
        .col-depart {
            width: 18%;
        }
        
        .col-destination {
            width: 18%;
        }
        
        .col-heure {
            width: 10%;
            text-align: center;
        }
        
        .col-conducteur {
            width: 34%;
        }
        
        .empty-row td {
            height: 25px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 20px;
        }
        
        .signature-area {
            width: 250px;
            margin-left: auto;
            text-align: center;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        
        .signature-title {
            font-size: 11px;
            color: #666;
        }
        
        .periode-badge {
            display: inline-block;
            font-size: 9px;
            padding: 1px 4px;
            border-radius: 3px;
            background-color: #ffc107;
            color: #333;
            margin-left: 5px;
        }
        
        .periode-nuit {
            background-color: #6c757d;
            color: white;
        }

        .check-column {
            width: 5%;
            text-align: center;
        }
    </style>
</head>
<body>
    {{-- En-tête --}}
    <div class="header">
        <div class="header-left">
            <div class="logo-container">
                <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo-img">
            </div>
        </div>
        <div class="header-center">
            <div class="title">Programmation Journalière</div>
        </div>
        <div class="header-right">
            <div class="date-box">
                <span class="date-label">Date :</span>
                <span class="date-value">{{ $dateFormatted }}</span>
            </div>
        </div>
    </div>

    {{-- Section Les Allers --}}
    <div class="section">
        <div class="section-title">Les Allers</div>
        <table>
            <thead>
                <tr>
                    <th class="col-numero">N°</th>
                    <th class="col-immat">Immatriculation</th>
                    <th class="col-depart">Départs</th>
                    <th class="col-destination">Destination</th>
                    <th class="col-heure">Heure</th>
                    <th class="col-conducteur">Bougeurs</th>
                    <th class="check-column">✓</th>
                </tr>
            </thead>
            <tbody>
                @forelse($allers as $index => $voyage)
                    <tr>
                        <td class="col-numero">{{ $index + 1 }}</td>
                        <td class="col-immat">{{ $voyage->bus->immatriculation ?? '-' }}</td>
                        <td class="col-depart">{{ $voyage->ligne->ville_depart ?? '-' }}</td>
                        <td class="col-destination">{{ $voyage->ligne->ville_arrivee ?? '-' }}</td>
                        <td class="col-heure">
                            {{ $voyage->ligne->horaire_formate ?? '-' }}
                            @if($voyage->periode === 'Nuit')
                                <span class="periode-badge periode-nuit">N</span>
                            @endif
                        </td>
                        <td class="col-conducteur">
                            M. {{ $voyage->conducteur->prenom ?? '' }} {{ $voyage->conducteur->nom ?? '' }}
                            @if($voyage->conducteur2)
                                / {{ $voyage->conducteur2->prenom ?? '' }}
                            @endif
                        </td>
                        <td class="check-column"></td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7" style="text-align: center; font-style: italic; color: #666;">
                            Aucun voyage aller programmé
                        </td>
                    </tr>
                @endforelse
                {{-- Lignes vides pour compléter --}}
                @for($i = count($allers); $i < 10; $i++)
                    <tr class="empty-row">
                        <td class="col-numero">{{ $i + 1 }}</td>
                        <td class="col-immat"></td>
                        <td class="col-depart"></td>
                        <td class="col-destination"></td>
                        <td class="col-heure"></td>
                        <td class="col-conducteur"></td>
                        <td class="check-column"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    {{-- Section Les Retours --}}
    <div class="section">
        <div class="section-title">Les Retours</div>
        <table>
            <thead>
                <tr>
                    <th class="col-numero">N°</th>
                    <th class="col-immat">Immatriculation</th>
                    <th class="col-depart">Départs</th>
                    <th class="col-destination">Destination</th>
                    <th class="col-heure">Heure</th>
                    <th class="col-conducteur">Bougeurs</th>
                    <th class="check-column">✓</th>
                </tr>
            </thead>
            <tbody>
                @forelse($retours as $index => $voyage)
                    <tr>
                        <td class="col-numero">{{ $index + 1 }}</td>
                        <td class="col-immat">{{ $voyage->bus->immatriculation ?? '-' }}</td>
                        <td class="col-depart">{{ $voyage->ligne->ville_depart ?? '-' }}</td>
                        <td class="col-destination">{{ $voyage->ligne->ville_arrivee ?? '-' }}</td>
                        <td class="col-heure">
                            {{ $voyage->ligne->horaire_formate ?? '-' }}
                            @if($voyage->periode === 'Nuit')
                                <span class="periode-badge periode-nuit">N</span>
                            @endif
                        </td>
                        <td class="col-conducteur">
                            M. {{ $voyage->conducteur->prenom ?? '' }} {{ $voyage->conducteur->nom ?? '' }}
                            @if($voyage->conducteur2)
                                / {{ $voyage->conducteur2->prenom ?? '' }}
                            @endif
                        </td>
                        <td class="check-column"></td>
                    </tr>
                @empty
                    <tr class="empty-row">
                        <td colspan="7" style="text-align: center; font-style: italic; color: #666;">
                            Aucun voyage retour programmé
                        </td>
                    </tr>
                @endforelse
                {{-- Lignes vides pour compléter --}}
                @for($i = count($retours); $i < 10; $i++)
                    <tr class="empty-row">
                        <td class="col-numero">{{ $i + 1 }}</td>
                        <td class="col-immat"></td>
                        <td class="col-depart"></td>
                        <td class="col-destination"></td>
                        <td class="col-heure"></td>
                        <td class="col-conducteur"></td>
                        <td class="check-column"></td>
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>

    {{-- Pied de page avec signature --}}
    <div class="footer">
        <div class="signature-area">
            <div class="signature-title">Le Responsable</div>
            <div class="signature-line"></div>
        </div>
    </div>
</body>
</html>
