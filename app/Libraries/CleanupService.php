<?php

namespace App\Libraries;

use App\Models\CvSessionModel;
use App\Models\CvDataModel;
use App\Models\RateLimitModel;

class CleanupService
{
    private const CLEANUP_INTERVAL = 3600;
    private const CLEANUP_LOCK_TTL = 3500;
    private const LOCK_FILE = 'cleanup.lock';

    private string $lockPath;

    public function __construct()
    {
        $this->lockPath = WRITEPATH . 'cache/' . self::LOCK_FILE;
    }

    public function autoCleanup(): bool
    {
        if (! $this->shouldRun()) {
            return false;
        }

        if (! $this->acquireLock()) {
            return false;
        }

        try {
            $this->runCleanup();
            $this->releaseLock();
            log_message('info', '[CleanupService] Auto-cleanup completed at ' . date('Y-m-d H:i:s'));
            return true;
        } catch (\Throwable $e) {
            $this->releaseLock();
            log_message('error', '[CleanupService] Cleanup failed: ' . $e->getMessage());
            return false;
        }
    }

    private function shouldRun(): bool
    {
        return (time() - $this->getLastRunTime()) >= self::CLEANUP_INTERVAL;
    }

    private function getLastRunTime(): int
    {
        if (! is_file($this->lockPath)) {
            return 0;
        }
        $content = @file_get_contents($this->lockPath);
        if (! $content) {
            return 0;
        }
        $data = @json_decode($content, true);
        return isset($data['last_run']) ? (int) $data['last_run'] : 0;
    }

    private function acquireLock(): bool
    {
        $dir = dirname($this->lockPath);
        if (! is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        $existing = null;
        $lockContent = @file_get_contents($this->lockPath);
        if ($lockContent) {
            $existing = @json_decode($lockContent, true);
        }
        $lockedAt = $existing['locked_at'] ?? 0;

        if ($lockedAt > 0 && (time() - $lockedAt) < self::CLEANUP_LOCK_TTL) {
            return false;
        }

        $ok = @file_put_contents(
            $this->lockPath,
            json_encode([
                'locked_at' => time(),
                'last_run' => $existing['last_run'] ?? 0,
            ], JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        return $ok !== false;
    }

    private function releaseLock(): void
    {
        $existing = null;
        $content = @file_get_contents($this->lockPath);
        if ($content) {
            $existing = @json_decode($content, true);
        }
        @file_put_contents(
            $this->lockPath,
            json_encode([
                'locked_at' => 0,
                'last_run' => $existing['last_run'] ?? time(),
            ], JSON_UNESCAPED_UNICODE)
        );
    }

    public function runCleanup(): array
    {
        return [
            'expired_sessions' => $this->cleanupExpiredSessions(),
            'orphan_photos' => $this->cleanupOrphanPhotos(),
            'expired_rate_limits' => $this->cleanupExpiredRateLimits(),
            'orphan_pdf_cache' => $this->cleanupOrphanPdfCache(),
            'old_abuse_reports' => $this->cleanupOldAbuseReports(),
        ];
    }

    private function cleanupExpiredSessions(): int
    {
        $model = new CvSessionModel();
        $cutoff = date('Y-m-d H:i:s');

        $sessions = $model->where('expires_at <', $cutoff)->findAll();
        $count = count($sessions);

        if ($count === 0) {
            return 0;
        }

        $sessionIds = array_column($sessions, 'id');

        $dataModel = new CvDataModel();
        $dataModel->whereIn('session_id', $sessionIds)->delete();

        $db = db_connect();
        $db->table('export_logs')->whereIn('session_id', $sessionIds)->delete();
        $db->table('rate_limits')->whereIn('session_id', $sessionIds)->delete();

        $model->where('expires_at <', $cutoff)->delete();

        log_message('info', '[CleanupService] Deleted ' . $count . ' expired sessions');
        return $count;
    }

    private function cleanupOrphanPhotos(): int
    {
        $photoDir = WRITEPATH . 'uploads/photos/';
        if (! is_dir($photoDir)) {
            return 0;
        }

        $validPaths = $this->getValidPhotoPaths();
        $files = glob($photoDir . '*');
        $deleted = 0;

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }
            $relativePath = basename($file);
            if (! in_array($relativePath, $validPaths, true)) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            log_message('info', '[CleanupService] Deleted ' . $deleted . ' orphan photo files');
        }

        return $deleted;
    }

    private function getValidPhotoPaths(): array
    {
        $db = db_connect();
        $rows = $db->table('cv_data')
            ->select('data_json')
            ->where('section_name', 'personal')
            ->get()
            ->getResultArray();

        $paths = [];
        foreach ($rows as $row) {
            $data = @json_decode((string) $row['data_json'], true);
            if (! empty($data['photo_path'])) {
                $parts = explode('/', (string) $data['photo_path']);
                $paths[] = end($parts);
            }
        }

        return $paths;
    }

    private function cleanupExpiredRateLimits(): int
    {
        $model = new RateLimitModel();
        $cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));

        $count = $model->where('last_hit_at <', $cutoff)->countAllResults();
        if ($count > 0) {
            $model->where('last_hit_at <', $cutoff)->delete();
            log_message('info', '[CleanupService] Deleted ' . $count . ' expired rate limit records');
        }

        return $count;
    }

    private function cleanupOrphanPdfCache(): int
    {
        $cacheDir = WRITEPATH . 'cache/pdf/';
        if (! is_dir($cacheDir)) {
            return 0;
        }

        $files = glob($cacheDir . '*.cache');
        $deleted = 0;

        foreach ($files as $file) {
            if (! is_file($file)) {
                continue;
            }
            $mtime = @filemtime($file);
            if ($mtime && (time() - $mtime) > 3600) {
                if (@unlink($file)) {
                    $deleted++;
                }
            }
        }

        if ($deleted > 0) {
            log_message('info', '[CleanupService] Deleted ' . $deleted . ' orphan PDF cache files');
        }

        return $deleted;
    }

    private function cleanupOldAbuseReports(): int
    {
        $db = db_connect();
        $cutoff = date('Y-m-d H:i:s', strtotime('-30 days'));

        $count = $db->table('abuse_reports')
            ->where('created_at <', $cutoff)
            ->countAllResults();

        if ($count > 0) {
            $db->table('abuse_reports')
                ->where('created_at <', $cutoff)
                ->delete();
            log_message('info', '[CleanupService] Deleted ' . $count . ' old abuse reports');
        }

        return $count;
    }

    public function forceCleanup(): array
    {
        $this->acquireLock();
        try {
            $results = $this->runCleanup();
            $this->releaseLock();
            return $results;
        } catch (\Throwable $e) {
            $this->releaseLock();
            throw $e;
        }
    }

    public function getStatus(): array
    {
        $lastRun = $this->getLastRunTime();
        return [
            'last_run' => $lastRun,
            'last_run_human' => $lastRun > 0 ? date('Y-m-d H:i:s', $lastRun) : 'never',
            'next_run_in_seconds' => max(0, self::CLEANUP_INTERVAL - (time() - $lastRun)),
            'interval_seconds' => self::CLEANUP_INTERVAL,
        ];
    }
}
