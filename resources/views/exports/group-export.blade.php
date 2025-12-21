<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar Grupo</title>
    <style>
        /* PDF-safe styles inspired by your example */
        body {
            font-family: 'Inter', 'sans-serif';
            color: #111827;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            padding: 40px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .logo {
            width: 150px;
            height: auto;
            margin-bottom: 20px;
            border-radius: 100%;
            padding: 8px;
            background: #F2F2F2;
        }

        h1 {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
            color: #000;
        }

        .section {
            margin-bottom: 30px;
        }

        .section h2 {
            font-size: 15px;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #f3f4f6;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .info-grid {
            width: 100%;
            border-collapse: collapse;
        }

        .info-grid td {
            padding: 8px 0;
            font-size: 10px;
        }

        .info-grid td.label {
            font-weight: 600;
            color: #4b5563;
            width: 150px;
        }

        /* Member List Table */
        .member-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .member-table th,
        .member-table td {
            text-align: left;
            padding: 6px;
            font-size: 10px;
            border-bottom: 1px solid #e5e7eb;
        }

        .member-table th {
            background-color: #f9fafb;
            font-weight: 600;
            color: #4b5563;
        }

        .member-table tr:last-child td {
            border-bottom: none;
        }

        .empty-state {
            text-align: center;
            padding: 20px;
            color: #6b7280;
        }

        .curp {
            text-transform: uppercase;
        }
    </style>
</head>

<body>

    <div class="container">
        <div class="header">
            <img src="{{ public_path('images/logo.png') }}" alt="Logo" class="logo">
            <h1>Reporte de Grupo</h1>
        </div>

        <div class="section">
            <h2>Información del Grupo</h2>
            <table class="info-grid">
                <tr>
                    <td class="label">Nombre:</td>
                    <td>{{ $group->name }}</td>
                </tr>
                <tr>
                    <td class="label">Ubicación:</td>
                    <td>{{ $group->location ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Fecha y Hora:</td>
                    <td>{{ $group->date_time ? $group->date_time->format('F d, Y \a \l\a\s g:i A') : 'N/A' }}</td>
                </tr>
                <tr>
                    <td class="label">Creación:</td>
                    <td>{{ $group->created_at ? $group->created_at->format('F d, Y') : 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <div class="section">
            <h2>
                Miembros del Grupo ({{ $group->applicants->count() }})
            </h2>

            @if($group->applicants->count() > 0)
                <table class="member-table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Teléfono</th>
                            <th>CURP</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($group->applicants as $applicant)
                            <tr>
                                <td>{{ $applicant->applicant_name }}</td>
                                <td>{{ $applicant->chat_id }}</td>
                                <td class="curp">{{ $applicant->curp }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <p class="empty-state">No hay miembros en este grupo.</p>
            @endif
        </div>
    </div>

</body>
</html>