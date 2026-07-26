<?php

use App\Platform\Backup\BackupFailedException;
use App\Platform\Backup\BackupService;
use App\Platform\Backup\DatabaseDumper;
use App\Platform\Backup\MysqlDumper;
use App\Platform\Console\BackupCommand;
use App\Platform\Notifications\BackupFailed;
use App\Platform\Support\AlertRecipient;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Notification;

/** Lažni dumper — testovi ne pokreću stvarni `mysqldump`. */
class FakeDumper implements DatabaseDumper
{
    public function __construct(public bool $shouldFail = false) {}

    public function dump(string $targetFile): void
    {
        if ($this->shouldFail) {
            throw new BackupFailedException('baza nedostupna');
        }

        File::put($targetFile, "-- dump\n");
    }
}

beforeEach(function () {
    $this->backupPath = storage_path('framework/testing/backups');
    File::deleteDirectory($this->backupPath);
    config()->set('homeos.backup.path', $this->backupPath);
    config()->set('homeos.backup.keep_days', 14);
});

afterEach(function () {
    File::deleteDirectory($this->backupPath);
});

it('writes a database dump and an archive of user uploads', function () {
    File::ensureDirectoryExists(storage_path('app/documents'));
    File::put(storage_path('app/documents/skener.txt'), 'sadržaj priloga');

    $created = (new BackupService(new FakeDumper))->run(Carbon::parse('2026-07-26 03:15'));

    expect(basename($created['database']))->toBe('baza_2026-07-26_031500.sql');
    expect(File::exists($created['database']))->toBeTrue();

    expect($created['files'])->not->toBeNull();
    expect(File::exists($created['files']))->toBeTrue();

    // Arhiva stvarno sadrži prilog, ne samo prazan zip.
    $zip = new ZipArchive;
    $zip->open($created['files']);
    expect($zip->locateName('documents/skener.txt'))->not->toBeFalse();
    $zip->close();

    File::delete(storage_path('app/documents/skener.txt'));
});

it('skips the uploads archive when there is nothing to archive', function () {
    File::deleteDirectory(storage_path('app/documents'));

    $created = (new BackupService(new FakeDumper))->run();

    // Prazna arhiva bi samo zauzimala mjesto i lažno djelovala kao backup.
    expect($created['files'])->toBeNull();
})->skip(fn () => ! empty(File::allFiles(storage_path('app'))), 'storage/app nije prazan u ovom okruženju');

it('deletes backups older than the retention window and keeps the rest', function () {
    $service = new BackupService(new FakeDumper);
    File::ensureDirectoryExists($this->backupPath);

    $old = $this->backupPath.'/baza_2026-01-01_030000.sql';
    $fresh = $this->backupPath.'/baza_2026-07-25_030000.sql';
    $foreign = $this->backupPath.'/tudji-fajl.txt';

    foreach ([$old, $fresh, $foreign] as $file) {
        File::put($file, 'x');
    }

    touch($old, Carbon::parse('2026-07-01')->timestamp);
    touch($fresh, Carbon::parse('2026-07-25')->timestamp);
    touch($foreign, Carbon::parse('2026-01-01')->timestamp);

    $deleted = $service->prune(Carbon::parse('2026-07-26'));

    expect($deleted)->toBe(1);
    expect(File::exists($old))->toBeFalse();
    expect(File::exists($fresh))->toBeTrue();
    // Tuđe fajlove u folderu ne diramo — brišemo samo svoje backupe.
    expect(File::exists($foreign))->toBeTrue();
});

it('emails the alert address when the backup fails', function () {
    Notification::fake();
    config()->set('homeos.alert_email', 'admin@example.com');
    app()->bind(DatabaseDumper::class, fn () => new FakeDumper(shouldFail: true));

    test()->artisan('homeos:backup')->assertFailed();

    Notification::assertSentOnDemand(
        BackupFailed::class,
        fn (BackupFailed $n, array $channels, object $notifiable) => $notifiable->routes['mail'] === 'admin@example.com',
    );
});

it('falls back to the first household owner when no alert address is configured', function () {
    config()->set('homeos.alert_email', null);
    [, $owner] = makeHousehold();

    expect(AlertRecipient::email())->toBe($owner->user->email);
});

it('builds a mysqldump command that is safe and consistent', function () {
    $command = (new MysqlDumper)->command([
        'host' => 'host.docker.internal',
        'port' => 3306,
        'username' => 'homeos',
        'password' => 'tajna',
        'database' => 'homeosdb',
    ], '/tmp/baza.sql');

    expect($command)->toContain('--single-transaction');   // konzistentan snimak
    expect($command)->toContain('--no-tablespaces');       // radi bez PROCESS privilegije
    expect($command)->toContain('--host=host.docker.internal');
    // Lozinka NIKAD u argumentima — vidljiva bi bila u `ps` svima na serveru.
    expect(implode(' ', $command))->not->toContain('tajna');
});

it('resolves the real dumper from the container, without any test binding', function () {
    // Regresija: interfejs je postojao, produkcijska implementacija nije bila
    // vezana — komanda je pucala tek na serveru ("Target [DatabaseDumper] is not
    // instantiable"), jer su testovi vezali svoju lažnu.
    expect(app(DatabaseDumper::class))->toBeInstanceOf(MysqlDumper::class);
    expect(app(BackupService::class))->toBeInstanceOf(BackupService::class);
    expect(app(BackupCommand::class))->not->toBeNull();
});

it('runs the backup end to end through the artisan command', function () {
    app()->bind(DatabaseDumper::class, fn () => new FakeDumper);

    test()->artisan('homeos:backup')->assertSuccessful();

    expect(File::files($this->backupPath))->not->toBeEmpty();
});
