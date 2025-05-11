<?php

namespace PinIndia\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UninstallCommand extends Command
{
    protected $signature = 'pinindia:uninstall';

    protected $description = 'Uninstall PinIndia library';

    public function handle()
    {
        $confirm = $this->confirm('⚠️ Are you sure? This will delete all PinIndia related data.');

        if ($confirm) {
            $this->warn("⚠️ Uninstalling PinIndia.");

            $table_prefix = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_' : '';

            // Drop related tables
            $this->dropTables([
                $table_prefix.'states',
                $table_prefix.'districts',
                $table_prefix.'circles',
                $table_prefix.'regions',
                $table_prefix.'divisions',
                $table_prefix.'pincodes',
                $table_prefix.'post_offices',
            ]);

            // Remove PinIndia-related records from migration tables
            $this->removeMigrationRecords($table_prefix);

            // Delete config file
            $this->deleteConfigFile(config_path('pinindia.php'));

            // Optional: delete published migration files
            $this->deletePublishedMigrations($table_prefix);

            // Uninstall via Composer
            $this->uninstallViaComposer();

            $this->info('✅ PinIndia uninstalled successfully.');
        }
    }

    protected function dropTables(array $tables)
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("Dropped table: $table");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }

    protected function removeMigrationRecords(string $table_prefix)
    {
        $migrations = DB::table('migrations')
            ->where('migration', 'like', '%' . $table_prefix . '%')
            ->delete();
    }

    protected function deleteConfigFile(string $path)
    {
        if (File::exists($path)) {
            File::delete($path);
            $this->line("Deleted config file: $path");
        }
    }

    protected function deletePublishedMigrations(string $table_prefix)
    {
        $files = File::files(database_path('migrations'));

        foreach ($files as $file) {
            if (str_contains($file->getFilename(), $table_prefix)) {
                File::delete($file->getPathname());
                $this->line("Deleted migration: " . $file->getFilename());
            }
        }
    }

    protected function uninstallViaComposer()
    {
        $this->warn('⚠️ Removing the package via Composer.');

        $process = Process::fromShellCommandline('composer remove dev-giri/pin-india');
        // Optional: disable timeout completely
        $process->setTimeout(null); // No timeout

        // Optional: set TTY if supported (Unix only)
        if (Process::isTtySupported()) {
            $process->setTty(true);
        }

        try {
            $process->run(function ($type, $buffer) {
                echo $buffer;
            });

            if ($process->isSuccessful()) {
                $this->info('✅ Package successfully removed via Composer.');
            } else {
                $this->error('⚠️ Composer remove command failed.');
            }
        } catch (ProcessFailedException $exception) {
            $this->error('⚠️ Failed to execute Composer remove: ' . $exception->getMessage());
        }
    }
}
