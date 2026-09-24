<?php

namespace App\Livewire;

use App\Enums\AttendanceStatus;
use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Group;
use App\Services\Attendance\AttendanceService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class AttendancePage extends Component
{
    public Group $group;

    public $scanCode = '';

    public ?Applicant $lastScannedApplicant = null;

    public ?AttendanceStatus $lastScanStatus = null;

    public $groupMembers = [];

    public ?string $scanResult = null; // 'success', 'warning', 'danger'

    public ?string $scanMessage = null;

    public function mount(Group $group)
    {
        $this->group = $group;
        $this->loadGroupMembers();
    }

    public function loadGroupMembers()
    {
        $this->groupMembers = Applicant::where('group_id', $this->group->id)
            ->orWhereHas('currentAttendance', fn ($query) => $query->where('group_id', $this->group->id))
            ->with(['currentAttendance', 'responses'])
            ->get()
            ->sortBy(fn ($m) => $m->currentAttendance?->scanned_at?->timestamp)
            ->values();
    }

    public function toggleAttendance(AttendanceService $attendanceService, int $memberId): void
    {
        $attendance = Attendance::where('applicant_id', $memberId)
            ->where('group_id', $this->group->id)
            ->first();

        if (! $attendance) {
            return;
        }

        $attendanceService->toggleAttended($attendance);

        $this->loadGroupMembers();
    }

    public function processCode(AttendanceService $attendanceService)
    {
        $code = trim($this->scanCode);

        if (! $code) {
            $this->resetScanField();

            return;
        }

        if ($this->group->attendance_closed_at) {
            $this->scanResult = 'danger';
            $this->scanMessage = 'Este grupo ya fue cerrado.';
            $this->lastScannedApplicant = null;
            $this->resetScanField();

            return;
        }

        $attendance = Attendance::where('attendance_code', $code)->first();

        if (! $attendance) {
            $this->lastScannedApplicant = null;
            $this->lastScanStatus = null;
            $this->scanResult = 'danger';
            $this->scanMessage = "El código '{$code}' no es válido para este grupo.";
            $this->resetScanField();

            return;
        }

        $applicant = $attendance->applicant()->with('responses')->first();
        $this->lastScannedApplicant = $applicant;

        if ($applicant && $applicant->group_id != $this->group->id) {
            $this->lastScanStatus = null;
            $this->scanResult = 'warning';
            $this->scanMessage = 'Este código pertenece a otro grupo, asegurate de escanear el código correcto.';
            $this->resetScanField();

            return;
        }

        $result = $attendanceService->checkIn($attendance);

        $this->lastScanStatus = match ($result['reason']) {
            'success' => AttendanceStatus::Present,
            'already' => $attendance->status,
            default => null,
        };

        $this->scanResult = match ($result['reason']) {
            'success' => 'success',
            'already' => 'warning',
            default => 'danger',
        };

        $this->scanMessage = match ($result['reason']) {
            'success' => 'Asistencia registrada correctamente.',
            'already' => 'Esta persona ya había marcado asistencia.',
            'closed' => 'Este grupo ya fue cerrado.',
            default => 'No se pudo registrar la asistencia.',
        };

        $this->loadGroupMembers();
        $this->resetScanField();
    }

    public function closeAttendance(AttendanceService $attendanceService)
    {
        $attendanceService->close($this->group);

        $this->group->refresh();
        $this->loadGroupMembers();
    }

    public function reOpenAttendance()
    {
        $this->group->update(['attendance_closed_at' => null]);
        $this->group->refresh();
        $this->loadGroupMembers();
    }

    protected function resetScanField()
    {
        $this->scanCode = '';
        $this->dispatch('focus-scan-input');
    }

    public function render()
    {
        return view('livewire.attendance-page')
            ->with(['title' => $this->group->name]);
    }
}
