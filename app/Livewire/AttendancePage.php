<?php

namespace App\Livewire;

use App\Enums\AttendanceStatus;
use App\Models\Applicant;
use App\Models\Attendance;
use App\Models\Group;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Notification;

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
            ->with(['attendance', 'responses'])
            ->get();
    }

    public function processCode()
    {
        $code = trim($this->scanCode);

        if (!$code) {
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

        if (!$attendance) {
            $this->lastScannedApplicant = null;
            $this->lastScanStatus = null;
            $this->scanResult = 'danger';
            $this->scanMessage = "El código '{$code}' no es válido o no existe.";
            $this->resetScanField();
            return;
        }

        $applicant = $attendance->applicant()->with('responses')->first();
        $this->lastScannedApplicant = $applicant;

        if ($attendance->group_id != $this->group->id) {
            $this->lastScanStatus = null;
            $this->scanResult = 'warning';
            $this->scanMessage = "Pertenece a otro grupo.";
            $this->resetScanField();
            return;
        }

        if ($attendance->status === AttendanceStatus::Present) {
            $this->lastScanStatus = AttendanceStatus::Present;
            $this->scanResult = 'warning';
            $this->scanMessage = 'Esta persona ya había marcado asistencia.';
        } else {
            $attendance->update([
                'status' => AttendanceStatus::Present,
                'scanned_at' => now(),
            ]);

            $this->lastScanStatus = AttendanceStatus::Present;
            $this->scanResult = 'success';
            $this->scanMessage = 'Asistencia registrada correctamente.';
        }

        $this->loadGroupMembers();
        $this->resetScanField();
    }

    public function closeAttendance()
    {
        if ($this->group->attendance_closed_at) return;

        $applicants = Applicant::where('group_id', $this->group->id)->get();
        
        foreach ($applicants as $applicant) {
            $attendance = Attendance::where('applicant_id', $applicant->id)
                ->where('group_id', $this->group->id)
                ->first();

            if (!$attendance) {
                Attendance::create([
                    'applicant_id' => $applicant->id,
                    'group_id' => $this->group->id,
                    'status' => AttendanceStatus::Absent,
                ]);
            } elseif ($attendance->status === AttendanceStatus::Pending) {
                $attendance->update(['status' => AttendanceStatus::Absent]);
            }
        }

        $this->group->update(['attendance_closed_at' => now()]);
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
