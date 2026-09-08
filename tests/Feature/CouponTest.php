<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Module;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CouponTest extends TestCase
{
    use RefreshDatabase;

    public function test_discount_percent_and_fixed_and_cap(): void
    {
        $percent = new Coupon(['type' => 'percent', 'value' => 10, 'active' => true]);
        $this->assertSame(10.0, $percent->discountOn(100));

        $fixed = new Coupon(['type' => 'fixed', 'value' => 15, 'active' => true]);
        $this->assertSame(15.0, $fixed->discountOn(100));
        $this->assertSame(10.0, $fixed->discountOn(10)); // nunca mayor que el subtotal
    }

    public function test_validation_rules(): void
    {
        $this->assertFalse((new Coupon(['type' => 'percent', 'value' => 10, 'active' => false]))->validateFor(100, [1])[0]);
        $this->assertFalse((new Coupon(['type' => 'percent', 'value' => 10, 'active' => true, 'expires_at' => now()->subDay()]))->validateFor(100, [1])[0]);
        $this->assertFalse((new Coupon(['type' => 'percent', 'value' => 10, 'active' => true, 'min_total' => 200]))->validateFor(100, [1])[0]);
        $this->assertFalse((new Coupon(['type' => 'percent', 'value' => 10, 'active' => true, 'max_uses' => 5, 'uses' => 5]))->validateFor(100, [1])[0]);
        $this->assertTrue((new Coupon(['type' => 'percent', 'value' => 10, 'active' => true]))->validateFor(100, [1])[0]);
    }

    public function test_module_scope(): void
    {
        $shopA = Module::create(['name' => 'A', 'slug' => 'a', 'type' => 'tienda', 'fields' => []]);
        $shopB = Module::create(['name' => 'B', 'slug' => 'b', 'type' => 'tienda', 'fields' => []]);

        $coupon = new Coupon(['type' => 'percent', 'value' => 10, 'active' => true, 'module_id' => $shopB->id]);

        $this->assertFalse($coupon->validateFor(100, [$shopA->id])[0]);
        $this->assertTrue($coupon->validateFor(100, [$shopB->id])[0]);
    }

    public function test_redeem_increments_uses(): void
    {
        $coupon = Coupon::create(['code' => 'HOLA10', 'type' => 'percent', 'value' => 10, 'active' => true, 'uses' => 0]);

        Coupon::redeem('hola10'); // sin distinguir mayúsculas

        $this->assertSame(1, $coupon->fresh()->uses);
    }
}
