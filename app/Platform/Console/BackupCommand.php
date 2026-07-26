<?php

namespace App\Platform\Console;

use App\Platform\Backup\BackupService;
use App\Platform\Notifications\BackupFailed;
use App\Platform\Support\AlertRecipient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * Dnevni backup (ROADMAP Faza 8). Pokreće ga centralni scheduler; može se
 * pozvati i ručno prije rizične izmjene:
 *
 *     docker compose -f docker-compose.prod.yml exec app php artisan homeos:backup
 *
 * Neuspjeh se NE gubi u logu — ide i email na adresu za tehnička upozorenja,
 * jer tihi pad backupa se primijeti tek kad zatreba.
 */
class BackupCommand extends Command
{
    protected $signature = 'homeos:backup';

    protected $description = 'Napravi dnevni backup baze i priloga, pa obriši stare';

    public function handle(BackupService $backups): int
    {
        try {
            $created = $backups->run();
        } catch (Throwable $e) {
            Log::error('Backup nije uspio', ['exception' => $e->getMessage()]);

            Notification::route('mail', AlertRecipient::email())
                ->notify(new BackupFailed($e->getMessage()));

            $this->error("Backup nije uspio: {$e->getMessage()}");

            return self::FAILURE;
        }

        $this->info('Baza: '.basename($created['database']));
        $this->info('Prilozi: '.($created['files'] ? basename($created['files']) : 'nema priloga'));
        $this->info('Obrisano starih fajlova: '.$backups->prune());

        return self::SUCCESS;
    }
}
