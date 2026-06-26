<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <style>
        @font-face {
            font-family: 'Inter';
            src: url("{{ public_path('fonts/Inter-Regular.ttf') }}") format("truetype");
            font-weight: normal;
            font-style: normal;
        }

        @font-face {
            font-family: 'Inter';
            src: url("{{ public_path('fonts/Inter-Bold.ttf') }}") format("truetype");
            font-weight: bold;
            font-style: normal;
        }

        body {
            font-family: 'Inter', 'DejaVu Sans', sans-serif;
            background-color: white;
            color: #101418;
            margin: 0;
            padding: 12px;
        }

        .header {
            padding-bottom: 8px;
        }

        .title {
            font-size: 22px;
            font-weight: bold;
            color: #61b346;
            margin: 0;
        }

        .subtitle {
            font-size: 12px;
            color: #808080;
            margin-top: 4px;
        }

        .label {
            font-size: 12px;
            color: #61b346;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .value {
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 12px;
        }

        .message-container {
            padding: 12px;
            background-color: #f9fafb;
            border-radius: 6px;
            border-left: 3px solid #61b346;
        }

        .message-text {
            font-size: 10px;
            color: #4b5563;
            white-space: pre-line;
            line-height: 1.4;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }
    </style>
</head>

<body>

    <div class="header">
        <h1 class="title">Invitación de Entrevista</h1>
    </div>

    <div class="card">
        <div class="label">Ubicación</div>
        <div class="value" style="font-size: 12px;">{{ $applicant->group->location }}</div>        

        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Fecha y Hora</div>
                    <div class="value">{{ $applicant->group->date_time->translatedFormat('l d M, Y') }} a las {{ $applicant->group->date_time->translatedFormat('h:i A') }}</div>
                </td>
                <td>
                    <div class="label">Aplicante</div>
                    <div class="value">{{ $applicant->applicant_name }}</div>
                </td>
            </tr>
        </table>

        @if($applicant->group->location_link)
        <div style="margin-top: 8px; margin-bottom: 16px; text-align: start;">
            <a href="{{ $applicant->group->location_link }}" style="display: inline-block; padding: 10px 20px; background-color: #61b346; color: #ffffff; font-size: 13px; font-weight: bold; text-decoration: none; border-radius: 6px;">
                Ver ubicación en Google Maps &rarr;
            </a>
        </div>
        @endif

        @if($applicant->group->message)
        <div class="message-container">
            <div class="label">Información Importante</div>
            <div class="message-text">{{ $applicant->group->message }}</div>
        </div>
        @endif
        
        @if($applicant->attendance?->attendance_code && $qrCode)
        <div style="margin-top: 20px; text-align: center; border: 1px dashed #61b346; padding: 15px; border-radius: 8px;">
            <div class="label">Código de Asistencia</div>
            <img src="{{ $qrCode }}" style="width: 120px; height: 120px; margin: 10px 0;">
            <div style="font-family: monospace; font-size: 18px; font-weight: bold;">{{ $applicant->attendance->attendance_code }}</div>
            <div style="font-size: 10px; color: #6b7280; margin-top: 5px;">⚠️ Guarda este Código para entrar a tu Entrevista</div>
        </div>
        @endif
    </div>

    <div class="footer">
        Presente este documento (digital o en su teléfono) al llegar. <br>
        Generado el {{ now()->format('d/m/Y H:i') }} • ID: {{ $applicant->id }}
    </div>

</body>

</html>
