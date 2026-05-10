<?php

namespace App\Models;

use CodeIgniter\Model;

class AbuseReportModel extends Model
{
    protected $table = 'abuse_reports';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id',
        'ip_address',
        'action_attempted',
        'reason',
        'request_data',
        'created_at',
    ];
    protected $useTimestamps = false;

    public function report(?int $sessionId, string $ip, string $action, string $reason, ?string $requestData = null): void
    {
        try {
            $this->insert([
                'session_id' => $sessionId,
                'ip_address' => $ip,
                'action_attempted' => $action,
                'reason' => $reason,
                'request_data' => $this->sanitizeRequestData($requestData),
                'created_at' => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', '[AbuseReport] Failed to log: ' . $e->getMessage());
        }
    }

    private function sanitizeRequestData(?string $data): ?string
    {
        if (empty($data)) {
            return null;
        }

        $parsed = @json_decode($data, true);
        if (is_array($parsed)) {
            $safe = [];
            $allowed = ['sections', 'template', 'action'];
            foreach ($parsed as $key => $value) {
                if (in_array($key, $allowed, true)) {
                    $safe[$key] = is_string($value) ? mb_substr($value, 0, 500) : $value;
                }
            }
            return mb_substr(json_encode($safe), 0, 1000);
        }

        return mb_substr($data, 0, 500);
    }

    public function getRecentReports(int $limit = 50): array
    {
        return $this->orderBy('created_at', 'DESC')->limit($limit)->findAll();
    }

    public function countByIp(string $ip, int $hours = 24): int
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$hours} hours"));
        return $this->where('ip_address', $ip)
            ->where('created_at >=', $cutoff)
            ->countAllResults();
    }
}
