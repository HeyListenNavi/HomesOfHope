<?php

namespace App\Http\Controllers;

use App\Enums\ApplicantStatus;
use App\Models\Applicant;
use App\Models\Group;
use App\Services\Group\GroupService;
use Barryvdh\DomPDF\Facade\Pdf;
use chillerlan\QRCode\QRCode;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class GroupSelectionController extends Controller
{
    public function __construct(
        protected GroupService $groupService,
    ) {}

    /**
     * Muestra el formulario para que el aplicante elija un grupo.
     */
    public function showSelectionForm(Applicant $applicant)
    {
        // La ruta firmada ya protege contra manipulación de URL.
        if (! in_array($applicant->process_status, [ApplicantStatus::Approved, ApplicantStatus::StaffApproved])) {
            return view('selection.invalid', [
                'message' => 'Este enlace no es válido, tu solicitud está en revisión.',
            ]);
        }

        if ($applicant->group_id !== null) {
            return view('selection.invalid', [
                'message' => 'Este enlace no es válido o ya has seleccionado un grupo.',
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
        if (! in_array($applicant->process_status, [ApplicantStatus::Approved, ApplicantStatus::StaffApproved])) {
            return redirect()->route('selection.invalid')->with('error', 'Acción no permitida.');
        }

        if ($applicant->group_id !== null) {
            return redirect()->route('selection.invalid')->with('error', 'Acción no permitida.');
        }

        $request->validate([
            'group_id' => 'required',
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

            if (! $group) {
                // Si el grupo se llenó mientras el usuario decidía.
                return back()->with('error', 'Lo sentimos, el grupo que seleccionaste se acaba de llenar. Por favor, elige otra opción.');
            }

            // Asignación final y definitiva
            $applicant->group_id = $group->id;
            $applicant->confirmation_status = 'confirmed';
            $applicant->save();

            $this->groupService->sendInterviewDetails($applicant);

            return redirect(URL::temporarySignedRoute('selection.success', now()->addDays(3), ['applicant' => $applicant->id]))->with('success', '¡Excelente! Tu lugar en el grupo ha sido confirmado.');
        });
    }

    /**
     * Muestra una página de éxito genérica.
     */
    public function showSuccess(Applicant $applicant)
    {
        $number = config('services.whatsapp.number');
        $whatsAppUrl = "https://wa.me/{$number}";

        $applicant->load(['group', 'currentAttendance']);

        $qrCode = null;
        if ($applicant->currentAttendance?->attendance_code) {
            $qrCode = (new QRCode)->render($applicant->currentAttendance->attendance_code);
        }

        return view('selection.success', compact('whatsAppUrl', 'applicant', 'qrCode'));
    }

    public function downloadInvitation(Applicant $applicant)
    {
        $applicant->load(['group', 'currentAttendance']);

        $qrCode = null;
        if ($applicant->currentAttendance?->attendance_code) {
            $qrCode = (new QRCode)->render($applicant->currentAttendance->attendance_code);
        }

        $pdf = Pdf::loadView('pdf.invitation', compact('applicant', 'qrCode'))
            ->setPaper('letter', 'portrait');

        return $pdf->download("Invitacion_CasasDeEsperanza_{$applicant->id}.pdf");
    }

    public function showInvitation(Request $request, Applicant $applicant)
    {
        if (! $applicant->group) {
            return view('selection.invalid', [
                'message' => 'No tienes una entrevista agendada.',
            ]);
        }

        if ($request->query('pdf')) {
            return $this->downloadInvitation($applicant);
        }

        $applicant->load(['group', 'currentAttendance']);

        $qrCode = null;
        if ($applicant->currentAttendance?->attendance_code) {
            $qrCode = (new QRCode)->render($applicant->currentAttendance->attendance_code);
        }

        $number = config('services.whatsapp.number');
        $whatsAppUrl = "https://wa.me/{$number}";

        return view('invitation.show', compact('applicant', 'qrCode', 'whatsAppUrl'));
    }

    /**
     * Muestra una página para enlaces inválidos.
     */
    public function showInvalidLink()
    {
        return view('selection.invalid');
    }
}
