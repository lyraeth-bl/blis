<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Student;
use App\Models\Unit;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\EmployeePolicy;
use App\Policies\StudentPolicy;
use App\Policies\UnitPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(Attendance::class, AttendancePolicy::class);
        Gate::policy(Employee::class, EmployeePolicy::class);
        Gate::policy(Student::class, StudentPolicy::class);
        Gate::policy(Unit::class, UnitPolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
