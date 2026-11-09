<?php

namespace App\Livewire;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Shop extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $category = '';

    #[Url(except: 'featured')]
    public string $sort = 'featured';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'category', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset('search', 'category', 'sort');
        $this->resetPage();
    }

    #[Computed]
    public function categories(): Collection
    {
        return Product::where('published', true)->select('category')->distinct()->orderBy('category')->pluck('category');
    }

    #[Computed]
    public function products(): LengthAwarePaginator
    {
        $query = Product::where('published', true);
        $search = mb_substr(trim($this->search), 0, 100);
        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('name', 'like', '%'.$search.'%')->orWhere('tagline', 'like', '%'.$search.'%')->orWhere('description', 'like', '%'.$search.'%');
            });
        }
        if ($this->category !== '') {
            $query->where('category', mb_substr($this->category, 0, 80));
        }
        match ($this->sort) {
            'price_asc' => $query->orderBy('price_cents')->orderBy('name'),
            'price_desc' => $query->orderByDesc('price_cents')->orderBy('name'),
            'name' => $query->orderBy('name'),
            default => $query->orderByDesc('featured')->orderBy('name'),
        };

        return $query->paginate(12);
    }

    public function render(): View
    {
        return view('livewire.shop')->layout('components.layouts.app');
    }
}
