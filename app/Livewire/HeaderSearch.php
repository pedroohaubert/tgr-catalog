<?php

namespace App\Livewire;

use Livewire\Component;

class HeaderSearch extends Component
{
    public string $query = '';

    public bool $showInactive = false;

    public bool $onProductsPage = false;

    public function mount(): void
    {
        $params = request()->query();
        $this->query = (string) ($params['q'] ?? '');
        $this->showInactive = isset($params['show_inactive']) ? (bool) (int) $params['show_inactive'] : false;
        $this->onProductsPage = request()->is('produtos');
    }

    public function submit(): void
    {
        if ($this->onProductsPage) {
            $this->dispatch('headerSearch', $this->query, $this->showInactive)->to(ProductsList::class);
        } else {
            $params = http_build_query([
                'q' => $this->query,
                'show_inactive' => $this->showInactive ? 1 : 0,
            ]);
            $this->redirect('/produtos?'.$params, navigate: true);
        }
    }

    public function render()
    {
        return view('livewire.header-search');
    }
}
