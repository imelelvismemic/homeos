<?php

namespace App\Platform\Backup;

/**
 * Izvoz baze u fajl. Interfejs postoji da se backup može testirati bez stvarnog
 * `mysqldump` procesa (CLAUDE.md §16) — u testu se veže lažna implementacija.
 */
interface DatabaseDumper
{
    /**
     * @throws BackupFailedException ako izvoz ne uspije
     */
    public function dump(string $targetFile): void;
}
