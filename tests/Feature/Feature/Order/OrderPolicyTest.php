<?php

namespace Tests\Feature\Order;

use App\Enums\UserRole;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_can_only_view_own_orders(): void
    {
        $client1 = User::factory()->create(['role' => UserRole::Client]);
        $client2 = User::factory()->create(['role' => UserRole::Client]);

        $order1 = Order::factory()->for($client1)->create(['status' => 'pending']);
        $order2 = Order::factory()->for($client2)->create(['status' => 'pending']);

        // Client1 can view their own orders page
        $this->actingAs($client1)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee($order1->code)
            ->assertDontSee($order2->code);
    }

    public function test_client_can_cancel_own_pending_order(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $order = Order::factory()->for($client)->create(['status' => 'pending']);

        $this->actingAs($client)
            ->withSession(['_token' => 'test-token'])
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(route('orders.cancel', $order), [], [
                'X-CSRF-TOKEN' => 'test-token',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Pedido cancelado com sucesso.',
            ]);

        // Verify order was cancelled
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'canceled',
        ]);
    }

    public function test_client_cannot_cancel_other_clients_order(): void
    {
        $client1 = User::factory()->create(['role' => UserRole::Client]);
        $client2 = User::factory()->create(['role' => UserRole::Client]);
        $order = Order::factory()->for($client2)->create(['status' => 'pending']);

        $this->actingAs($client1)
            ->withoutMiddleware()
            ->postJson(route('orders.cancel', $order))
            ->assertForbidden();

        // Verify order status didn't change
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
        ]);
    }

    public function test_client_cannot_pay_orders(): void
    {
        $client = User::factory()->create(['role' => UserRole::Client]);
        $order = Order::factory()->for($client)->create(['status' => 'pending']);

        $this->actingAs($client)
            ->withoutMiddleware()
            ->postJson(route('admin.orders.pay', $order))
            ->assertForbidden();
    }

    public function test_admin_can_view_all_orders(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client1 = User::factory()->create(['role' => UserRole::Client]);
        $client2 = User::factory()->create(['role' => UserRole::Client]);

        $order1 = Order::factory()->for($client1)->create(['status' => 'pending']);
        $order2 = Order::factory()->for($client2)->create(['status' => 'pending']);

        $this->actingAs($admin)
            ->getJson(route('admin.orders.index'))
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'data' => [
                    'items' => [
                        ['id' => $order2->id], // Should include both orders
                        ['id' => $order1->id],
                    ],
                ],
            ]);
    }

    public function test_admin_can_pay_any_order(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $client = User::factory()->create(['role' => UserRole::Client]);
        $product = \App\Models\Product::factory()->create(['stock' => 5, 'is_active' => true]);

        $order = Order::factory()->for($client)->create(['status' => 'pending']);
        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);

        $this->actingAs($admin)
            ->withSession(['_token' => 'test-token'])
            ->withoutMiddleware(\Illuminate\Auth\Middleware\Authorize::class)
            ->postJson(route('admin.orders.pay', $order), [], [
                'X-CSRF-TOKEN' => 'test-token',
            ])
            ->assertOk()
            ->assertJson([
                'ok' => true,
                'message' => 'Pagamento confirmado.',
            ]);
    }
}
