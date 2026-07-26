<?php

namespace App\Platform\Backup;

use Symfony\Component\Process\Process;

/**
 * `mysqldump` prema hostovom MariaDB-u (CLAUDE.md §3a — baza NIJE u kontejneru,
 * pristupa se preko host.docker.internal).
 *
 * Lozinka ide kroz `MYSQL_PWD` env varijablu procesa, ne kao argument — argumenti
 * su vidljivi u `ps` izlazu svakome na serveru.
 */
class MysqlDumper implements DatabaseDumper
{
    public function dump(string $targetFile): void
    {
        $connection = config('database.default');
        $db = config("database.connections.{$connection}");

        $process = new Process(
            $this->command($db, $targetFile),
            env: ['MYSQL_PWD' => (string) ($db['password'] ?? '')],
            timeout: 600,
        );

        $process->run();

        if (! $process->isSuccessful()) {
            throw new BackupFailedException(trim($process->getErrorOutput()) ?: 'mysqldump nije uspio.');
        }
    }

    /**
     * @param  array<string, mixed>  $db
     * @return array<int, string>
     */
    public function command(array $db, string $targetFile): array
    {
        return [
            'mysqldump',
            '--host='.($db['host'] ?? '127.0.0.1'),
            '--port='.($db['port'] ?? 3306),
            '--user='.($db['username'] ?? ''),
            '--single-transaction',   // konzistentan snimak bez zaključavanja tabela
            '--quick',
            '--default-character-set=utf8mb4',
            '--no-tablespaces',       // dump radi i bez PROCESS privilegije
            '--result-file='.$targetFile,
            (string) ($db['database'] ?? ''),
        ];
    }
}
