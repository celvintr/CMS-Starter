<?php

namespace Tests\Feature;

use App\Models\Reservation;
use App\Models\SiteSetting;
use App\Support\Features;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ReservationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        SiteSetting::current()->update([
            'features' => ['reservas' => true],
            'reservation_days' => [1, 2, 3, 4, 5],
            'reservation_open' => '09:00',
            'reservation_close' => '11:00',
            'reservation_slot_minutes' => 30,
            'reservation_capacity' => 1,
        ]);
        Features::flush();
    }

    public function test_slots_generated_for_open_day(): void
    {
        $monday = Carbon::parse('next monday');

        $this->assertSame(
            ['09:00', '09:30', '10:00', '10:30'],
            SiteSetting::current()->availableReservationSlots($monday),
        );
    }

    public function test_closed_day_has_no_slots(): void
    {
        $sunday = Carbon::parse('next sunday');

        $this->assertEmpty(SiteSetting::current()->availableReservationSlots($sunday));
    }

    public function test_taken_slot_is_excluded(): void
    {
        $monday = Carbon::parse('next monday');
        Reservation::create([
            'name' => 'X', 'email' => 'x@test.com',
            'starts_at' => $monday->copy()->setTime(9, 0), 'status' => 'pending',
        ]);

        $this->assertNotContains('09:00', SiteSetting::current()->availableReservationSlots($monday));
        $this->assertContains('09:30', SiteSetting::current()->availableReservationSlots($monday));
    }

    public function test_store_rejects_unavailable_time(): void
    {
        $monday = Carbon::parse('next monday')->toDateString();

        $this->post(route('reservation.store'), [
            'name' => 'Ana', 'email' => 'ana@test.com', 'date' => $monday, 'time' => '12:00',
        ])->assertSessionHasErrors('time');

        $this->assertDatabaseCount('reservations', 0);
    }

    public function test_store_accepts_available_time(): void
    {
        $monday = Carbon::parse('next monday')->toDateString();

        $this->post(route('reservation.store'), [
            'name' => 'Ana', 'email' => 'ana@test.com', 'date' => $monday, 'time' => '09:30',
        ]);

        $this->assertDatabaseHas('reservations', ['email' => 'ana@test.com', 'status' => 'pending']);
    }

    public function test_slots_endpoint_returns_json(): void
    {
        $monday = Carbon::parse('next monday')->toDateString();

        $this->getJson(route('reservation.slots', ['date' => $monday]))
            ->assertOk()
            ->assertJsonStructure(['slots']);
    }
}
