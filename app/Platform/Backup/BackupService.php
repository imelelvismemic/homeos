<?php

namespace App\Platform\Backup;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Throwable;
use ZipArchive;

/**
 * Dnevni backup (ROADMAP Faza 8): dump baze + arhiva korisničkih priloga, pa
 * brisanje starijih od `keep_days`.
 *
 * Prilozi (skenovi dokumenata, profilne slike) su svjesno DIO backupa: bez njih
 * bi vraćena baza imala zapise koji pokazuju na nepostojeće fajlove.
 *
 * Pokreće ga centralni scheduler kroz `homeos:backup`, ne cron na hostu — cijela
 * strategija je time u repou, pokrivena testovima i preživi seobu servera.
 */
class BackupService
{
    public function __construct(private DatabaseDumper $dumper) {}

    /**
     * Napravi backup i vrati putanje kreiranih fajlova.
     *
     * @return array{database: string, files: ?string}
     *
     * @throws BackupFailedException
     */
    public function run(?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $stamp = $now->format('Y-m-d_His');

        File::ensureDirectoryExists($this->path());

        $database = $this->path().DIRECTORY_SEPARATOR."baza_{$stamp}.sql";
        $this->dumper->dump($database);

        return [
            'database' => $database,
            'files' => $this->archiveUploads($this->path().DIRECTORY_SEPARATOR."prilozi_{$stamp}.zip"),
        ];
    }

    /**
     * Arhivira korisničke priloge. Vraća null ako priloga nema (ne pravimo prazne
     * arhive) ili ako ZIP ekstenzija nije dostupna.
     */
    public function archiveUploads(string $targetFile): ?string
    {
        $source = storage_path('app');

        if (! class_exists(ZipArchive::class) || ! File::isDirectory($source)) {
            return null;
        }

        $files = collect(File::allFiles($source))
            // Privremeni Livewire upload-i nisu korisnički podaci.
            ->reject(fn ($file) => str_contains($file->getRelativePath(), 'livewire-tmp'));

        if ($files->isEmpty()) {
            return null;
        }

        $zip = new ZipArchive;

        if ($zip->open($targetFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new BackupFailedException("Ne mogu kreirati arhivu priloga: {$targetFile}");
        }

        foreach ($files as $file) {
            $zip->addFile($file->getPathname(), $file->getRelativePathname());
        }

        $zip->close();

        return $targetFile;
    }

    /**
     * Obriši backupe starije od `keep_days`. Vraća broj obrisanih fajlova.
     */
    public function prune(?Carbon $now = null): int
    {
        $now ??= Carbon::now();
        $keepDays = max(1, (int) config('homeos.backup.keep_days', 14));
        $cutoff = $now->copy()->subDays($keepDays);

        if (! File::isDirectory($this->path())) {
            return 0;
        }

        $deleted = 0;

        foreach (File::files($this->path()) as $file) {
            if (! preg_match('/^(baza|prilozi)_/', $file->getFilename())) {
                continue;
            }

            if (Carbon::createFromTimestamp($file->getMTime())->lt($cutoff)) {
                try {
                    File::delete($file->getPathname());
                    $deleted++;
                } catch (Throwable) {
                    // Fajl koji se ne da obrisati ne smije oboriti backup.
                }
            }
        }

        return $deleted;
    }

    public function path(): string
    {
        return (string) config('homeos.backup.path', storage_path('backups'));
    }
}
