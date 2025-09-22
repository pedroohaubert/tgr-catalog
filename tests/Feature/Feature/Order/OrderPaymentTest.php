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

        // Create a pending order with items
        $order = Order::factory()->create([
            'user_id' => $client->id,
            'status' => 'pending',
            'total' => 60.00, // 3*10 + 2*15 = 60
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

        // Admin marks order as paid
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

        // Verify order status changed
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        // Verify stock was updated correctly
        $this->assertDatabaseHas('products', [
            'id' => $product1->id,
            'stock' => 2, // 5 - 3 = 2
        ]);

        $this->assertDatabaseHas('products', [
            'id' => $product2->id,
            'stock' => 1, // 3 - 2 = 1
        ]);

        // Verify event was dispatched
        Event::assertDispatched(OrderPaid::class, function ($event) use ($order) {
            return $event->order->id === $order->id;
        });

        // Note: Email queuing is tested separately due to DB::afterCommit behavior in tests
    }

    public function test_cannot_pay_order_with_insufficient_stock(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = User::factory()->create(['role' => UserRole::Client]);
        $product = Product::factory()->create(['name' => 'Produto Escasso', 'price' => 10.00, 'stock' => 1, 'is_active' => true]);

        // Create order requesting more stock than available
        $order = Order::factory()->create([
            'user_id' => $client->id,
            'status' => 'pending',
            'total' => 30.00,
        ]);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 3, // But stock is only 1
            'unit_price' => 10.00,
        ]);

        // Try to pay - should fail
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

        // Verify order status didn't change
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);

        // Verify stock wasn't touched
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock' => 1,
        ]);
    }
}
