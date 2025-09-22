<?php

namespace Tests\Feature\Order;

use App\Enums\UserRole;
use App\Events\OrderPaid;
use App\Mail\OrderPaidMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marks_order_as_paid_and_updates_stock_correctly(): void
    {
        Mail::fake();
        Event::fake([OrderPaid::class]);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = User::factory()->create(['role' => UserRole::Client]);
        $product1 = Product::factory()->create(['name' => 'Produto 1', 'price' => 10.00, 'stock' => 5, 'is_active' => true]);
        $product2 = Product::factory()->create(['name' => 'Produto 2', 'price' => 15.00, 'stock' => 3, 'is_active' => true]);

        $order = Order::factory()->create([
            'user_id' => $client->id,
            'status' => 'pending',
            'total' => 60.00,
        ]);
        $order->items()->create([
            'product_id' => $product1->id,
            'quantity' => 3,
            'unit_price' => 10.00,
        ]);
        $order->items()->create([
            'product_id' => $product2->id,
            'quantity' => 2,
            'unit_price' => 15.00,
        ]);

        $response = $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(route('admin.orders.pay', $order), [], [
                'X-CSRF-TOKEN' => 'test-token',
            ]);

        $response->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Pagamento confirmado.',
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'stock' => 2,
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product2->id,
            'stock' => 1,
        ]);
        Event::assertDispatched(OrderPaid::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });
    }

    public function test_cannot_pay_order_with_insufficient_stock(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = User::factory()->create(['role' => UserRole::Client]);
        $product = Product::factory()->create(['name' => 'Produto Escasso', 'price' => 10.00, 'stock' => 1, 'is_active' => true]);

        $order = Order::factory()->create([
            'user_id' => $client->id,
            'status' => 'pending',
            'total' => 30.00,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 3,
            'unit_price' => 10.00,
        ]);
        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(route('admin.orders.pay', $order), [], [
                'X-CSRF-TOKEN' => 'test-token',
            ])
            ->assertStatus(409)
            ->assertJson([
                'ok' => false,
                'error' => [
                    'code' => 'conflict',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }
}
