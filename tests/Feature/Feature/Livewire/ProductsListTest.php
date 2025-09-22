<?php

namespace Tests\Feature\Livewire;

use App\Livewire\ProductsList;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductsListTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_filters_products_by_name(): void
    {
        Product::factory()->create(['name' => 'Café Premium', 'is_active' => true]);
        Product::factory()->create(['name' => 'Chá Verde', 'is_active' => true]);
        Product::factory()->create(['name' => 'Café Expresso', 'is_active' => true]);

        Livewire::test(ProductsList::class)
            ->set('query', 'Café')
            ->assertSee('Café Premium')
            ->assertSee('Café Expresso')
            ->assertDontSee('Chá Verde');
    }

    public function test_only_active_filter_works(): void
    {
        Product::factory()->create(['name' => 'Produto Ativo', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produto Inativo', 'is_active' => false]);

        // Show only active products
        Livewire::test(ProductsList::class)
            ->set('showInactive', false)
            ->assertSee('Produto Ativo')
            ->assertDontSee('Produto Inativo');

        // Show all products
        Livewire::test(ProductsList::class)
            ->set('showInactive', true)
            ->assertSee('Produto Ativo')
            ->assertSee('Produto Inativo');
    }

    public function test_search_and_active_filter_work_together(): void
    {
        Product::factory()->create(['name' => 'Café Ativo', 'is_active' => true]);
        Product::factory()->create(['name' => 'Café Inativo', 'is_active' => false]);
        Product::factory()->create(['name' => 'Chá Ativo', 'is_active' => true]);

        // Search for "Café" and show only active
        Livewire::test(ProductsList::class)
            ->set('query', 'Café')
            ->set('showInactive', false)
            ->assertSee('Café Ativo')
            ->assertDontSee('Café Inativo')
            ->assertDontSee('Chá Ativo');
    }

    public function test_empty_search_shows_all_active_products(): void
    {
        Product::factory()->create(['name' => 'Produto 1', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produto 2', 'is_active' => true]);
        Product::factory()->create(['name' => 'Produto Inativo', 'is_active' => false]);

        Livewire::test(ProductsList::class)
            ->set('query', '')
            ->set('showInactive', false)
            ->assertSee('Produto 1')
            ->assertSee('Produto 2')
            ->assertDontSee('Produto Inativo');
    }

    public function test_header_search_triggers_product_list_update(): void
    {
        Product::factory()->create(['name' => 'Café Especial', 'is_active' => true]);
        Product::factory()->create(['name' => 'Chá Mate', 'is_active' => true]);

        Livewire::test(ProductsList::class)
            ->call('applySearch', 'Café', false)
            ->assertSee('Café Especial')
            ->assertDontSee('Chá Mate');
    }
}
