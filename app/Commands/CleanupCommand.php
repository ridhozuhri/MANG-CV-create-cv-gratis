<?php

namespace App\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\BaseCommand;
use App\Libraries\CleanupService;

class CleanupCommand extends BaseCommand
{
    protected $group = 'MANG-CV';
    protected $name = 'cv:cleanup';
    protected $description = 'Hapus data CV yang expired dan file orphan.';

    public function run(array $params)
    {
        $force = in_array('--force', $params) || in_array('-f', $params);

        CLI::write('Starting MANG-CV cleanup...', 'white');

        try {
            $cleanup = new CleanupService();

            if ($force) {
                CLI::write('Force mode enabled (ignoring interval lock)...', 'yellow');
                $results = $cleanup->forceCleanup();
            } else {
                $status = $cleanup->getStatus();
                CLI::write('Last run: ' . ($status['last_run_human'] ?? 'never'), 'white');
                CLI::write('Next run in: ' . $status['next_run_in_seconds'] . ' seconds', 'white');

                $ran = $cleanup->autoCleanup();
                if (! $ran) {
                    CLI::write('Skipped: interval not reached yet. Use --force to run anyway.', 'yellow');
                    return;
                }
                $results = $cleanup->runCleanup();
            }

            CLI::write('');
            foreach ($results as $key => $value) {
                $label = ucwords(str_replace('_', ' ', $key));
                $msg = "  {$label}: {$value}";
                if ($value > 0) {
                    CLI::write($msg, 'green');
                } else {
                    CLI::write($msg, 'white');
                }
            }

            CLI::write('');
            CLI::write('Cleanup completed successfully.', 'green');

        } catch (\Throwable $e) {
            CLI::write('Error: ' . $e->getMessage(), 'red');
            return 1;
        }
    }
}
