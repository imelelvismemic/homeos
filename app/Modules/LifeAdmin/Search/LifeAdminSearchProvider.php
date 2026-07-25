<?php

namespace App\Modules\LifeAdmin\Search;

use App\Modules\LifeAdmin\Filament\Resources\ContactResource;
use App\Modules\LifeAdmin\Filament\Resources\DocumentResource;
use App\Modules\LifeAdmin\Filament\Resources\ShoppingListResource;
use App\Modules\LifeAdmin\Models\Contact;
use App\Modules\LifeAdmin\Models\Document;
use App\Modules\LifeAdmin\Models\ShoppingList;
use App\Platform\Contracts\SearchProviderContract;
use App\Platform\Models\Household;
use App\Platform\Search\SearchResult;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class LifeAdminSearchProvider implements SearchProviderContract
{
    public function search(string $query, Household $household): Collection
    {
        $documents = Document::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('title', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (Document $document) => new SearchResult(
                type: 'document',
                id: $document->id,
                title: $document->title,
                url: DocumentResource::getUrl('edit', ['record' => $document, 'tenant' => $household]),
                icon: $document->type->icon(),
            ));

        $contacts = Contact::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where(fn (Builder $q) => $q->where('name', 'like', "%{$query}%")
                ->orWhere('relationship', 'like', "%{$query}%"))
            ->limit(5)
            ->get()
            ->map(fn (Contact $contact) => new SearchResult(
                type: 'contact',
                id: $contact->id,
                title: $contact->name,
                url: ContactResource::getUrl('edit', ['record' => $contact, 'tenant' => $household]),
                icon: 'heroicon-o-user-circle',
            ));

        $lists = ShoppingList::query()
            ->where('household_id', $household->id)
            ->visibleTo(auth()->user())
            ->where('name', 'like', "%{$query}%")
            ->limit(5)
            ->get()
            ->map(fn (ShoppingList $list) => new SearchResult(
                type: 'shopping_list',
                id: $list->id,
                title: $list->name,
                url: ShoppingListResource::getUrl('edit', ['record' => $list, 'tenant' => $household]),
                icon: 'heroicon-o-shopping-cart',
            ));

        return $documents->concat($contacts)->concat($lists);
    }

    public function type(): string
    {
        return 'lifeadmin';
    }
}
