<?php

namespace App\Filters;

use App\Libraries\SessionManager;
use App\Libraries\CleanupService;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class SessionInitFilter implements FilterInterface
{
    private static bool $cleanupRun = false;

    public function before(RequestInterface $request, $arguments = null)
    {
        service('session');
        $manager = new SessionManager();
        $manager->bootstrap();

        $this->tryAutoCleanup();
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }

    private function tryAutoCleanup(): void
    {
        if (self::$cleanupRun) {
            return;
        }

        if (ENVIRONMENT === 'testing') {
            return;
        }

        try {
            $cleanup = new CleanupService();
            $cleanup->autoCleanup();
        } catch (\Throwable $e) {
            log_message('error', '[SessionInitFilter] Auto-cleanup error: ' . $e->getMessage());
        }

        self::$cleanupRun = true;
    }
}
