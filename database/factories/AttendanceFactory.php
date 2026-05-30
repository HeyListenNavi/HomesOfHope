<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\Applicant;
use App\Models\Group;
use App\Enums\AttendanceStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    protected $model = Attendance::class;

    public function definition(): array
    {
        return [
            'applicant_id' => Applicant::factory(),
            'group_id' => Group::factory(),
            'attendance_code' => strtoupper(substr(md5(uniqid(rand(), true)), 0, 8)),
            'status' => AttendanceStatus::Pending,
            'scanned_at' => null,
        ];
    }

    public function present(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::Present,
            'scanned_at' => now(),
        ]);
    }

    public function absent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AttendanceStatus::Absent,
        ]);
    }
}
