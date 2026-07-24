<?php

namespace App\Platform\Notifications;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Generičko obavještenje: nešto je podijeljeno sa vama (DATA_MODEL.md §5
 * kategorija `shared_with_you`). Radi za BILO KOJI Shareable objekat, pa svaki
 * modul dobija ovo ponašanje bez svog koda — okidač je Shared event.
 */
class SharedWithYou extends HouseholdNotification
{
    public function __construct(public Model $shareable) {}

    public function category(): string
    {
        return 'shared_with_you';
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('platform.notifications.shared_with_you.subject'))
            ->line(__('platform.notifications.shared_with_you.line', ['title' => $this->title()]));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category(),
            'shareable_type' => $this->shareable->getMorphClass(),
            'shareable_id' => $this->shareable->getKey(),
            'title' => $this->title(),
        ];
    }

    protected function title(): string
    {
        return $this->shareable->title ?? class_basename($this->shareable);
    }
}
