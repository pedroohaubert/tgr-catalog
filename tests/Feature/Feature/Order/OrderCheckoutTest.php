<?php

namespace Tests\Feature\Order;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_creates_order_successfully(): void
    {
        $user = User::factory()->create(['role' => UserRole::Client]);
        $product1 = Product::factory()->create(['price' => 10.00, 'stock' => 5, 'is_active' => true]);
        $product2 = Product::factory()->create(['price' => 20.00, 'stock' => 3, 'is_active' => true]);

        session()->put('cart.items', [
            $product1->id => [
                'product_id' => $product1->id,
                'name' => $product1->name,
                'unit_price' => 10.00,
                'quantity' => 2,
            ],
            $product2->id => [
                'product_id' => $product2->id,
                'name' => $product2->name,
                'unit_price' => 20.00,
                'quantity' => 1,
            ],
        ]);

        $response = $this->actingAs($user)
            ->withoutMiddleware()
            ->post('/checkout', [], ['Accept' => 'application/json']);

        $response->assertStatus(201)
            ->assertJson([
                'ok' => true,
                'data' => [
                    'order' => [
                        'total' => 40.00,
                        'status' => 'pending',
                    ],
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'total' => 40.00,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product1->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product2->id,
            'quantity' => 1,
            'unit_price' => 20.00,
        ]);
    }
}
