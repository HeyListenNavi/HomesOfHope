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
            text-transform: uppercase;
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
        <h1 class="title">Confirmación de Entrevista</h1>
        <div class="subtitle">Casas de Esperanza • Comprobante de Registro</div>
    </div>

    <div class="card">
        <div class="label">Ubicación</div>
        <div class="value">{{ $applicant->group->location }}</div>

        <table>
            <tr>
                <td style="width: 50%;">
                    <div class="label">Fecha y Hora</div>
                    <div class="value">{{ $applicant->group->date_time->format('d/m/Y - h:i A') }}</div>
                </td>
                <td>
                    <div class="label">Solicitante</div>
                    <div class="value">{{ $applicant->applicant_name }}</div>
                </td>
            </tr>
        </table>

        @if($applicant->group->message)
        <div class="message-container">
            <div class="label">Información Importante</div>
            <div class="message-text">{{ $applicant->group->message }}</div>
        </div>
        @endif
    </div>

    <div class="footer">
        Presente este documento (digital o en su teléfono) al llegar. <br>
        Generado el {{ now()->format('d/m/Y H:i') }} • ID: {{ $applicant->id }}
    </div>

</body>

</html>
