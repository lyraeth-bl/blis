<?php

namespace App\Http\Controllers\Auth;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthenticatedSessionController extends Controller
{
    public function create(): View|RedirectResponse
    {
        if (Auth::user()?->canAccessStaffPortal()) {
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->with([
            'hd' => 'budiluhur.sch.id',
            'prompt' => 'select_account',
        ])->redirect();
    }

    public function callback(): RedirectResponse
    {
        $googleUser = Socialite::driver('google')->user();

        $email = Str::lower((string) $googleUser->getEmail());

        if (! $this->isAllowedStaffEmail($email)) {
            return $this->reject('Akun Google ini tidak terdaftar sebagai guru atau staff Budi Luhur.');
        }

        $employee = Employee::query()
            ->where('email', $email)
            ->firstOrFail();

        $user = User::query()->firstOrNew(['email' => $email]);

        if ($user->exists && ! $user->is_active) {
            return $this->reject('Akun ini sedang nonaktif.');
        }

        if (! $user->exists) {
            $user->forceFill([
                'name' => $employee->name,
                'password' => Hash::make(Str::random(64)),
                'role' => UserRole::Staff,
                'is_active' => true,
            ]);
        } else {
            $user->forceFill([
                'name' => $user->name ?: $employee->name,
            ]);
        }

        $user->save();

        Auth::login($user, remember: true);

        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    public function destroy(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function reject(string $message): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'email' => $message,
        ]);
    }

    private function isAllowedStaffEmail(string $email): bool
    {
        if (! Str::endsWith($email, '@budiluhur.sch.id')) {
            return false;
        }

        if (Str::startsWith($email, ['blsma.', 'blsmk.'])) {
            return false;
        }

        return Employee::query()
            ->where('email', $email)
            ->exists();
    }
}
