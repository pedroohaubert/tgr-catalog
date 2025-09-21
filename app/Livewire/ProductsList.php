<?php

namespace App\Livewire;

use App\Services\ProductService;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsList extends Component
{
    use WithPagination;

    public string $query = '';

    public bool $showInactive = false;

    public int $perPage = 12;

    protected $queryString = [
        'query' => ['as' => 'q', 'except' => ''],
        'showInactive' => ['as' => 'show_inactive', 'except' => false],
    ];

    protected $listeners = [
        'headerSearch' => 'applySearch',
    ];

    public function updatingQuery(): void
    {
        $this->resetPage();
    }

    public function updatingShowInactive(): void
    {
        $this->resetPage();
    }

    public function setActiveFilter(bool $value): void
    {
        $this->showInactive = $value;
    }

    public function applySearch(?string $q = null, ?bool $showInactive = null): void
    {
        Log::debug('ProductsList.applySearch', [
            'q' => $q ?? $this->query,
            'showInactive' => $showInactive ?? $this->showInactive,
            'componentId' => $this->getId(),
        ]);
        if ($q !== null) {
            $this->query = $q;
        }
        if ($showInactive !== null) {
            $this->showInactive = $showInactive;
        }
        $this->resetPage();
    }

    public function render()
    {
        Log::debug('ProductsList.render', [
            'query' => $this->query,
            'showInactive' => $this->showInactive,
            'page' => $this->getPage(),
            'perPage' => $this->perPage,
        ]);

        $products = app(ProductService::class)->paginate(
            query: $this->query !== '' ? $this->query : null,
            onlyActive: ! $this->showInactive,
            perPage: $this->perPage,
        );

        return view('livewire.products-list', compact('products'));
    }
}
