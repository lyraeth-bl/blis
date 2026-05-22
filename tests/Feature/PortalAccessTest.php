<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Employee;
use App\Models\PicketSchedule;
use App\Models\Unit;
use App\Models\User;
use App\Models\Website;
use App\Models\Wifi;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

class PortalAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_can_access_public_home(): void
    {
        $this->get('/')
            ->assertOk();
    }

    public function test_guest_only_sees_public_websites_and_wifis(): void
    {
        Website::factory()->create([
            'name' => 'Public Website',
            'is_private' => false,
        ]);

        Website::factory()->create([
            'name' => 'Private Website',
            'is_private' => true,
        ]);

        Wifi::factory()->create([
            'ssid' => 'Public WiFi',
            'is_private' => false,
        ]);

        Wifi::factory()->create([
            'ssid' => 'Private WiFi',
            'is_private' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('Public Website')
            ->assertDontSee('Private Website');

        $this->get(route('wifi.index'))
            ->assertOk()
            ->assertSee('Public WiFi')
            ->assertDontSee('Private WiFi');
    }

    public function test_authenticated_employee_sees_private_items_for_their_unit(): void
    {
        $userUnit = Unit::create([
            'code' => 'TEST_SMA',
            'name' => 'SMA Test',
            'campus' => 'Test Campus',
        ]);

        $otherUnit = Unit::create([
            'code' => 'TEST_SMK',
            'name' => 'SMK Test',
            'campus' => 'Test Campus',
        ]);

        $user = $this->createEmployeeUser('unit-staff@budiluhur.sch.id', UserRole::Staff, $userUnit);

        Website::factory()->create([
            'name' => 'Private User Unit Website',
            'unit_id' => $userUnit->id,
            'is_private' => true,
        ]);

        Website::factory()->create([
            'name' => 'Private Other Unit Website',
            'unit_id' => $otherUnit->id,
            'is_private' => true,
        ]);

        Wifi::factory()->create([
            'ssid' => 'Private User Unit WiFi',
            'unit_id' => $userUnit->id,
            'is_private' => true,
        ]);

        Wifi::factory()->create([
            'ssid' => 'Private Other Unit WiFi',
            'unit_id' => $otherUnit->id,
            'is_private' => true,
        ]);

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Private User Unit Website')
            ->assertDontSee('Private Other Unit Website');

        $this->actingAs($user)
            ->get(route('wifi.index'))
            ->assertOk()
            ->assertSee('Private User Unit WiFi')
            ->assertDontSee('Private Other Unit WiFi');
    }

    public function test_registered_employee_can_login_to_staff_portal_with_google(): void
    {
        $email = 'guru@budiluhur.sch.id';

        Employee::create([
            'nip' => '1001',
            'name' => 'Guru Budi',
            'email' => $email,
        ]);

        $this->mockSocialiteUser($email);

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('home'));

        $this->assertAuthenticated();
    }

    public function test_student_prefixed_email_cannot_login_even_when_registered(): void
    {
        $email = 'blsma.12345@budiluhur.sch.id';

        Employee::create([
            'nip' => '1002',
            'name' => 'Blocked Student',
            'email' => $email,
        ]);

        $this->mockSocialiteUser($email);

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_user_without_employee_email_cannot_login_to_staff_portal(): void
    {
        $this->mockSocialiteUser('unknown@budiluhur.sch.id');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_google_login_auto_creates_staff_user_when_email_exists_in_employees(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
        ]);

        Employee::create([
            'nip' => '1003',
            'name' => 'Google Staff',
            'email' => 'google.staff@budiluhur.sch.id',
        ]);

        $this->mockSocialiteUser('google.staff@budiluhur.sch.id');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('home'));

        $user = User::where('email', 'google.staff@budiluhur.sch.id')->first();

        $this->assertNotNull($user);
        $this->assertSame(UserRole::Staff, $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_rejects_email_that_is_not_registered_as_employee(): void
    {
        config([
            'services.google.client_id' => 'google-client-id',
            'services.google.client_secret' => 'google-client-secret',
        ]);

        $this->mockSocialiteUser('unknown@budiluhur.sch.id');

        $this->get(route('login.google.callback'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseMissing(User::class, [
            'email' => 'unknown@budiluhur.sch.id',
        ]);
    }

    public function test_staff_user_cannot_access_qr_attendance(): void
    {
        $user = $this->createEmployeeUser('staff@budiluhur.sch.id', UserRole::Staff);

        $this->actingAs($user)
            ->get(route('qr-attendance.index'))
            ->assertForbidden();

        $this->actingAs($user)
            ->postJson(route('qr-attendance.scan'), ['token' => '12345'])
            ->assertForbidden();
    }

    public function test_scheduled_staff_user_can_access_qr_attendance(): void
    {
        Carbon::setTestNow('2026-05-22 07:00:00');

        $unit = Unit::create([
            'code' => 'TEST_PIKET',
            'name' => 'SMA Piket',
            'campus' => 'Test Campus',
        ]);

        $user = $this->createEmployeeUser('piket@budiluhur.sch.id', UserRole::Staff, $unit);
        $employee = Employee::where('email', 'piket@budiluhur.sch.id')->firstOrFail();

        PicketSchedule::create([
            'employee_id' => $employee->id,
            'unit_id' => $unit->id,
            'day_of_week' => 5,
            'starts_at' => '06:00',
            'ends_at' => '15:00',
            'effective_from' => '2026-01-01',
            'effective_until' => '2026-06-30',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('qr-attendance.index'))
            ->assertOk();
    }

    private function createEmployeeUser(string $email, UserRole $role, ?Unit $unit = null): User
    {
        Employee::create([
            'nip' => fake()->unique()->numerify('####'),
            'name' => fake()->name(),
            'email' => $email,
            'unit_id' => $unit?->id,
        ]);

        return User::factory()->create([
            'email' => $email,
            'role' => $role,
        ]);
    }

    private function mockSocialiteUser(string $email): void
    {
        $socialiteUser = Mockery::mock(SocialiteUser::class);
        $socialiteUser->shouldReceive('getEmail')
            ->andReturn($email);

        $provider = Mockery::mock();
        $provider->shouldReceive('user')
            ->andReturn($socialiteUser);

        Socialite::shouldReceive('driver')
            ->with('google')
            ->andReturn($provider);
    }
}
