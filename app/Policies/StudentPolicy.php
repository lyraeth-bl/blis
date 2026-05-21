<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;

class StudentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageStudents();
    }

    public function view(User $user, Student $student): bool
    {
        return $user->isTu() && $user->canAccessUnit($student->unit_id);
    }

    public function create(User $user): bool
    {
        return $user->canManageStudents();
    }

    public function update(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $this->view($user, $student);
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageStudents();
    }
}
