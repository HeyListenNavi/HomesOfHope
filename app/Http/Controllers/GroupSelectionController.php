<?php

namespace App\Http\Controllers;

use App\Enums\AttendanceStatus;
use App\Models\Applicant;
use App\Models\Group;
use App\Services\Group\GroupService;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class GroupSelectionController extends Controller
{
    public function __construct(protected GroupService $groupService) {}

    /**
     * Muestra el formulario para que el aplicante elija un grupo.
     */
    public function showSelectionForm(Applicant $applicant)
    {
        // La ruta firmada ya protege contra manipulación de URL.
        // Verificamos el estado lógico del aplicante.
        if ($applicant->group_id !== null) {
            return view('selection.invalid', [
                'message' => 'Este enlace no es válido o ya has seleccionado un grupo.'
            ]);
        }

        // Buscamos todos los grupos que tengan cupo disponible.
        $availableGroups = Group::where('current_members_count', '<', DB::raw('capacity'))
            ->where('is_active', '=', true)
            ->whereNotNull('date_time')
            ->where('date_time', '>=', Carbon::tomorrow())
            ->orderBy('date_time', 'asc')
            ->get();

        return view('selection.form', compact('applicant', 'availableGroups'));
    }

    /**
     * Procesa la selección del grupo, lo asigna y confirma.
     */
    public function assignToGroup(Request $request, Applicant $applicant)
    {
        // Validación inicial
        if ($applicant->group_id !== null) {
            return redirect()->route('selection.invalid')->with('error', 'Acción no permitida.');
        }

        $request->validate([
            'group_id' => 'required'
        ]);

        // Usamos una transacción para garantizar la integridad de los datos
        return DB::transaction(function () use ($request, $applicant) {
            $groupId = $request->input('group_id');

            // Bloqueamos la fila del grupo para evitar que dos personas
            // tomen el último lugar al mismo tiempo (race condition).
            $group = Group::where('id', $groupId)
                ->where('current_members_count', '<', DB::raw('capacity'))
                ->where('is_active', '=', true)
                ->whereNotNull('date_time')
                ->where('date_time', '>=', Carbon::tomorrow())
                ->lockForUpdate()
                ->first();

            if (!$group) {
                // Si el grupo se llenó mientras el usuario decidía.
                return back()->with('error', 'Lo sentimos, el grupo que seleccionaste se acaba de llenar. Por favor, elige otra opción.');
            }

            // Asignación final y definitiva
            $applicant->group_id = $group->id;
            $applicant->confirmation_status = 'confirmed';
            $applicant->save();

            $applicant->attendance()->updateOrCreate(
                ['applicant_id' => $applicant->id],
                [
                    'group_id' => $group->id,
                    'attendance_code' => $applicant->attendance?->attendance_code ?? strtoupper(substr(md5(uniqid($applicant->id, true)), 0, 8)),
                    'status' => AttendanceStatus::Pending,
                ]
            );

            $this->groupService->sendInterviewDetails($applicant);

            return redirect()->route('selection.success', $applicant->id)->with('success', '¡Excelente! Tu lugar en el grupo ha sido confirmado.');
        });
    }

    /**
     * Muestra una página de éxito genérica.
     */
    public function showSuccess(Applicant $applicant)
    {
        $number = config('services.whatsapp.number');
        $whatsAppUrl = "https://wa.me/{$number}";

        $applicant->load('group');

        $qrCode = null;
        if ($applicant->attendance?->attendance_code) {
            $qrCode = (new QRCode)->render($applicant->attendance->attendance_code);
        }

        return view('selection.success', compact('whatsAppUrl', 'applicant', 'qrCode'));
    }

    public function downloadInvitation(Applicant $applicant)
    {
        $applicant->load('group');

        $qrCode = null;
        if ($applicant->attendance?->attendance_code) {
            $qrCode = (new QRCode)->render($applicant->attendance->attendance_code);
        }

        $pdf = Pdf::loadView('pdf.invitation', compact('applicant', 'qrCode'))
            ->setPaper('letter', 'portrait');;

        return $pdf->download("Invitacion_CasasDeEsperanza_{$applicant->id}.pdf");
    }

    /**
     * Muestra una página para enlaces inválidos.
     */
    public function showInvalidLink()
    {
        return view('selection.invalid');
    }
}
