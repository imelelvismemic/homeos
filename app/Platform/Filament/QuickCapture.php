<?php

namespace App\Platform\Filament;

use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

/**
 * Topbar "Brzo dodaj" launcher (ROADMAP Faza 2.4). Renderuje se na svakoj
 * stranici preko render hooka (HomePanelProvider). Otvara modal sa opcijama
 * brzog dodavanja koje moduli registruju (QuickCaptureRegistry). Sa 0 modula —
 * prazno stanje, bez greške.
 *
 * VAŽNO: URL-ove razrješavamo u mount() dok je Filament tenant kontekst još
 * dostupan (inicijalni render stranice). Ovo je zaseban Livewire komponent
 * montiran preko render hooka, pa njegovi kasniji update zahtjevi (otvaranje
 * modala) NE prolaze kroz panel tenant middleware — `Filament::getTenant()` bi
 * tada bio null, a `route(...)` za panel rutu bi ispao bez `{tenant}` segmenta
 * i vodio na 404. Zato hrefove računamo jednom i čuvamo u stanju komponente.
 */
class QuickCapture extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    /**
     * @var array<int, array{key: string, label: string, icon: ?string, href: string}>
     */
    public array $items = [];

    public function mount(): void
    {
        $tenant = Filament::getTenant();

        $this->items = app(QuickCaptureRegistry::class)->items()
            ->map(function (array $item) use ($tenant): array {
                $item['href'] = Str::startsWith($item['url'], ['http', '/'])
                    ? $item['url']
                    : route($item['url'], $tenant ? ['tenant' => $tenant] : []);

                return $item;
            })
            ->all();
    }

    public function captureAction(): Action
    {
        return Action::make('capture')
            ->label(__('platform.quick_capture.button'))
            ->icon('heroicon-m-plus')
            ->button()
            ->color('primary')
            ->modalHeading(__('platform.quick_capture.heading'))
            ->modalContent(view('filament.platform.quick-capture-options', [
                'items' => collect($this->items),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('platform.quick_capture.close'));
    }

    public function render(): View
    {
        return view('filament.platform.quick-capture');
    }
}
