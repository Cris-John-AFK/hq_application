<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DailyBackup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a daily database backup directly using pg_dump';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');

        $filename = "backup-" . date('Y-m-d-His') . ".sql";
        $storagePath = storage_path("app/backups");
        
        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $path = "{$storagePath}/{$filename}";
        
        // Credentials from .env
        $dbHost = config('database.connections.pgsql.host');
        $dbPort = config('database.connections.pgsql.port');
        $dbName = config('database.connections.pgsql.database');
        $dbUser = config('database.connections.pgsql.username');
        $dbPass = config('database.connections.pgsql.password');

        // Note: For pg_dump to work, the password usually needs to be in .pgpass file or environment variable.
        // We set PGPASSWORD environment variable for this process scope.
        putenv("PGPASSWORD={$dbPass}");

        // Construct pg_dump command (Add path to PATH if necessary, or assume it's globally available)
        $command = "pg_dump -h {$dbHost} -p {$dbPort} -U {$dbUser} -F c -b -v -f \"{$path}\" {$dbName}";

        // Execute
        $output = null;
        $resultCode = null;
        exec($command, $output, $resultCode);

        // Clear password from env
        putenv("PGPASSWORD=");

        if ($resultCode === 0) {
            $this->info("Backup successful: {$filename}");
            
            // Prune old backups (keep 7 days)
            $this->pruneBackups($storagePath);
        } else {
            $this->error("Backup failed.");
            $this->error(implode("\n", $output));
        }
    }

    private function pruneBackups($path)
    {
        $files = glob("{$path}/*.sql");
        $now = time();
        $retention = 7 * 24 * 60 * 60; // 7 days

        foreach ($files as $file) {
            if ($now - filemtime($file) > $retention) {
                unlink($file);
                $this->info("Pruned old backup: " . basename($file));
            }
        }
    }
}
