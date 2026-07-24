<?php

namespace App\Platform\Filament;

use App\Platform\QuickCapture\QuickCaptureRegistry;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Topbar "Brzo dodaj" launcher (ROADMAP Faza 2.4). Renderuje se na svakoj
 * stranici preko render hooka (HomePanelProvider). Otvara modal sa opcijama
 * brzog dodavanja koje moduli registruju (QuickCaptureRegistry). Sa 0 modula —
 * prazno stanje, bez greške.
 */
class QuickCapture extends Component implements HasActions, HasForms
{
    use InteractsWithActions;
    use InteractsWithForms;

    public function captureAction(): Action
    {
        return Action::make('capture')
            ->label(__('platform.quick_capture.button'))
            ->icon('heroicon-m-plus')
            ->button()
            ->color('primary')
            ->modalHeading(__('platform.quick_capture.heading'))
            ->modalContent(view('filament.platform.quick-capture-options', [
                'items' => app(QuickCaptureRegistry::class)->items(),
            ]))
            ->modalSubmitAction(false)
            ->modalCancelActionLabel(__('platform.quick_capture.close'));
    }

    public function render(): View
    {
        return view('filament.platform.quick-capture');
    }
}
