<?php

namespace App\Policies;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Student;
use App\Models\User;

class AttendancePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageEmployees() || $user->canManageStudents();
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $this->canAccessAttendable($user, $attendance);
    }

    public function create(User $user): bool
    {
        return $user->canManageEmployees() || $user->canManageStudents();
    }

    public function update(User $user, Attendance $attendance): bool
    {
        return $this->canAccessAttendable($user, $attendance);
    }

    public function delete(User $user, Attendance $attendance): bool
    {
        return $this->canAccessAttendable($user, $attendance);
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageEmployees() || $user->canManageStudents();
    }

    private function canAccessAttendable(User $user, Attendance $attendance): bool
    {
        $attendable = $attendance->attendable;

        if ($attendable instanceof Employee) {
            return $user->isHrd() && $user->canAccessUnit($attendable->unit_id);
        }

        if ($attendable instanceof Student) {
            return $user->isTu() && $user->canAccessUnit($attendable->unit_id);
        }

        return false;
    }
}
