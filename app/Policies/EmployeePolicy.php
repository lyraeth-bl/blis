<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;

class EmployeePolicy
{
    public function before(User $user): ?bool
    {
        return $user->isAdmin() ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->canManageEmployees();
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->isHrd()
            && $employee->accessibleUnitIds()
                ->contains(fn (int $unitId): bool => $user->canAccessUnit($unitId));
    }

    public function create(User $user): bool
    {
        return $user->canManageEmployees();
    }

    public function update(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $this->view($user, $employee);
    }

    public function deleteAny(User $user): bool
    {
        return $user->canManageEmployees();
    }
}
