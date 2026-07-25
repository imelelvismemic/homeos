<?php

namespace App\Platform\Notifications;

use App\Platform\Digest\DigestSection;
use App\Platform\Enums\DigestFrequency;
use Illuminate\Notifications\Messages\MailMessage;

/**
 * Digest email (Faza 6, kategorije `digest_daily`/`digest_weekly`). Šalje ga
 * scheduler članovima koji su odabrali ritam. Isključivo email (nije in-app) —
 * digest je po prirodi sažetak koji stiže na mail.
 */
class DigestNotification extends HouseholdNotification
{
    /**
     * @param  array<int, DigestSection>  $sections
     */
    public function __construct(
        public array $sections,
        public DigestFrequency $frequency,
    ) {}

    public function category(): string
    {
        return $this->frequency === DigestFrequency::Weekly ? 'digest_weekly' : 'digest_daily';
    }

    /**
     * Digest je čisto email (ne dupliramo ga kao in-app obavještenje).
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $isWeekly = $this->frequency === DigestFrequency::Weekly;

        $mail = (new MailMessage)
            ->subject(__($isWeekly ? 'platform.digest.subject_weekly' : 'platform.digest.subject_daily'))
            ->greeting(__('platform.digest.greeting', ['name' => $notifiable->user?->name ?? '']))
            ->line(__($isWeekly ? 'platform.digest.intro_weekly' : 'platform.digest.intro_daily'));

        foreach ($this->sections as $section) {
            $mail->line('**'.$section->title.'**');

            foreach ($section->lines as $line) {
                $mail->line($line);
            }
        }

        return $mail->line(__('platform.digest.outro'));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'category' => $this->category(),
        ];
    }
}
