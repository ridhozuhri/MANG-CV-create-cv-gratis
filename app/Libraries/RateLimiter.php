<?php

namespace App\Libraries;

use App\Models\RateLimitModel;

class RateLimiter
{
    private const WINDOW_1_MINUTE = 60;
    private const WINDOW_1_HOUR = 3600;
    private const WINDOW_1_DAY = 86400;

    public const ACTION_EXPORT_PDF = 'export_pdf';
    public const ACTION_EXPORT_TXT = 'export_txt';
    public const ACTION_EXPORT_JSON = 'export_json';

    public const LIMIT_EXPORT_PER_HOUR = 20;
    public const LIMIT_EXPORT_PER_DAY = 50;

    public function check(int $sessionId, string $action): array
    {
        $ip = service('request')->getIPAddress();
        $now = time();

        $hourly = $this->checkLimit($sessionId, $action, $ip, self::WINDOW_1_HOUR, self::LIMIT_EXPORT_PER_HOUR, $now);
        if (! $hourly['allowed']) {
            return $hourly;
        }

        $daily = $this->checkLimit($sessionId, $action, $ip, self::WINDOW_1_DAY, self::LIMIT_EXPORT_PER_DAY, $now);
        if (! $daily['allowed']) {
            return $daily;
        }

        return ['allowed' => true, 'remaining' => min($hourly['remaining'], $daily['remaining'])];
    }

    public function hit(int $sessionId, string $action): void
    {
        $ip = service('request')->getIPAddress();
        $now = time();

        $this->incrementHit($sessionId, $action, $ip, self::WINDOW_1_HOUR, $now);
        $this->incrementHit($sessionId, $action, $ip, self::WINDOW_1_DAY, $now);
    }

    public function getRemaining(int $sessionId, string $action): int
    {
        $check = $this->check($sessionId, $action);
        return $check['remaining'] ?? 0;
    }

    public function getResetTime(int $sessionId, string $action): int
    {
        $ip = service('request')->getIPAddress();
        $now = time();

        $model = new RateLimitModel();
        $hourlyKey = $this->buildKey($sessionId, $action, $ip, self::WINDOW_1_HOUR);
        $dailyKey = $this->buildKey($sessionId, $action, $ip, self::WINDOW_1_DAY);

        $hourly = $model->where('key_identifier', $hourlyKey)
            ->where('window_start <=', $now)
            ->where('window_start >', $now - self::WINDOW_1_HOUR)
            ->first();

        $daily = $model->where('key_identifier', $dailyKey)
            ->where('window_start <=', $now)
            ->where('window_start >', $now - self::WINDOW_1_DAY)
            ->first();

        $hourlyReset = $hourly ? ((int) $hourly['window_start'] + self::WINDOW_1_HOUR) : ($now + self::WINDOW_1_HOUR);
        $dailyReset = $daily ? ((int) $daily['window_start'] + self::WINDOW_1_DAY) : ($now + self::WINDOW_1_DAY);

        return min($hourlyReset, $dailyReset);
    }

    private function checkLimit(int $sessionId, string $action, string $ip, int $window, int $limit, int $now): array
    {
        $key = $this->buildKey($sessionId, $action, $ip, $window);
        $model = new RateLimitModel();

        $row = $model->where('key_identifier', $key)
            ->where('window_start <=', $now)
            ->where('window_start >', $now - $window)
            ->first();

        $current = $row ? (int) $row['hit_count'] : 0;
        $remaining = max(0, $limit - $current);

        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'limit' => $limit,
            'window' => $window,
            'reset_at' => $now + $window,
        ];
    }

    private function incrementHit(int $sessionId, string $action, string $ip, int $window, int $now): void
    {
        $windowStart = $now - ($now % $window);
        $key = $this->buildKey($sessionId, $action, $ip, $window);
        $model = new RateLimitModel();

        $existing = $model->where('key_identifier', $key)
            ->where('window_start', $windowStart)
            ->first();

        if ($existing) {
            $model->update($existing['id'], [
                'hit_count' => (int) $existing['hit_count'] + 1,
                'last_hit_at' => date('Y-m-d H:i:s', $now),
            ]);
        } else {
            $model->insert([
                'key_identifier' => $key,
                'action_name' => $action,
                'hit_count' => 1,
                'window_start' => $windowStart,
                'window_duration_seconds' => $window,
                'ip_address' => $ip,
                'last_hit_at' => date('Y-m-d H:i:s', $now),
            ]);
        }
    }

    private function buildKey(int $sessionId, string $action, string $ip, int $window): string
    {
        return sprintf('s%d_%s_%s_w%d', $sessionId, $action, substr(md5($ip), 0, 8), $window);
    }

    public function cleanup(int $daysOld = 7): int
    {
        $cutoff = date('Y-m-d H:i:s', time() - ($daysOld * self::WINDOW_1_DAY));
        $model = new RateLimitModel();
        return $model->where('last_hit_at <', $cutoff)->delete();
    }

    public const LIMIT_IP_PER_HOUR = 50;
    public const ACTION_IP_BLOCK = 'ip_block';

    public function checkIpBased(string $ip, string $action): array
    {
        $now = time();
        return $this->checkIpLimit($ip, $action, self::WINDOW_1_HOUR, self::LIMIT_IP_PER_HOUR, $now);
    }

    private function checkIpLimit(string $ip, string $action, int $window, int $limit, int $now): array
    {
        $key = $this->buildIpKey($ip, $action, $window);
        $model = new RateLimitModel();

        $row = $model->where('key_identifier', $key)
            ->where('window_start <=', $now)
            ->where('window_start >', $now - $window)
            ->first();

        $current = $row ? (int) $row['hit_count'] : 0;
        $remaining = max(0, $limit - $current);

        return [
            'allowed' => $remaining > 0,
            'remaining' => $remaining,
            'limit' => $limit,
            'window' => $window,
            'reset_at' => $now + $window,
        ];
    }

    public function hitIpBased(string $ip, string $action): void
    {
        $now = time();
        $window = self::WINDOW_1_HOUR;
        $windowStart = $now - ($now % $window);
        $key = $this->buildIpKey($ip, $action, $window);
        $model = new RateLimitModel();

        $existing = $model->where('key_identifier', $key)
            ->where('window_start', $windowStart)
            ->first();

        if ($existing) {
            $model->update($existing['id'], [
                'hit_count' => (int) $existing['hit_count'] + 1,
                'last_hit_at' => date('Y-m-d H:i:s', $now),
            ]);
        } else {
            $model->insert([
                'key_identifier' => $key,
                'action_name' => $action,
                'hit_count' => 1,
                'window_start' => $windowStart,
                'window_duration_seconds' => $window,
                'ip_address' => $ip,
                'last_hit_at' => date('Y-m-d H:i:s', $now),
            ]);
        }
    }

    private function buildIpKey(string $ip, string $action, int $window): string
    {
        return sprintf('ip_%s_%s_w%d', substr(md5($ip), 0, 12), $action, $window);
    }
}