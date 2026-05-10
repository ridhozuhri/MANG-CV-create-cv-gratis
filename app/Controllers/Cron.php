<?php

namespace App\Controllers;

use App\Libraries\CleanupService;

class Cron extends BaseController
{
    public function cleanup()
    {
        $secret = $this->request->getGet('secret') ?? '';

        if (! $this->validateSecret($secret)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'ok' => false,
                    'error' => 'forbidden',
                    'message' => 'Secret key tidak valid.',
                ]);
        }

        $startTime = microtime(true);

        $cleanup = new CleanupService();
        $results = $cleanup->forceCleanup();

        $executionTime = round((microtime(true) - $startTime) * 1000, 2);

        return $this->response->setJSON([
            'ok' => true,
            'message' => 'Cleanup berhasil.',
            'results' => $results,
            'execution_time_ms' => $executionTime,
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    public function status()
    {
        $secret = $this->request->getGet('secret') ?? '';

        if (! $this->validateSecret($secret)) {
            return $this->response
                ->setStatusCode(403)
                ->setJSON([
                    'ok' => false,
                    'error' => 'forbidden',
                    'message' => 'Secret key tidak valid.',
                ]);
        }

        $cleanup = new CleanupService();

        return $this->response->setJSON([
            'ok' => true,
            'status' => $cleanup->getStatus(),
            'timestamp' => date('Y-m-d H:i:s'),
        ]);
    }

    private function validateSecret(string $secret): bool
    {
        $envSecret = env('CCG_CRON_SECRET', '');
        if (empty($envSecret)) {
            log_message('warning', '[Cron] CCG_CRON_SECRET tidak dikonfigurasi');
            return false;
        }
        return hash_equals($envSecret, $secret);
    }
}
