<?php

namespace App\Controllers;

use App\Libraries\PdfGenerator;
use App\Libraries\RateLimiter;
use App\Libraries\SessionManager;
use App\Libraries\TemplateManager;
use App\Models\CvDataModel;

class Export extends BaseController
{
    private const CACHE_TTL = 3600;

    public function pdf()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return redirect()->to('/');
        }

        $rateLimiter = new RateLimiter();
        $check = $rateLimiter->check($sessionRow['id'], RateLimiter::ACTION_EXPORT_PDF);

        if (! $check['allowed']) {
            return $this->rateLimitResponse($check, 'PDF');
        }

        $sections = $this->getSections($sessionRow['id']);
        $template = $this->request->getGet('template') ?? $sessionRow['selected_template'] ?? 'classic';

        $cacheKey = $this->buildCacheKey($sessionRow['id'], $template, $sections);
        $cached = $this->getCachedPdf($cacheKey);

        if ($cached !== null) {
            $this->logExport($sessionRow['id'], 'pdf', $template, strlen($cached), true);
            $filename = 'cv-' . $template . '-' . date('Ymd') . '.pdf';
            return $this->response
                ->setHeader('Content-Type', 'application/pdf')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setHeader('X-Cache', 'HIT')
                ->setBody($cached);
        }

        $html = (new TemplateManager())->renderToHtml($sections, $template, [
            'partial' => false,
            'embed_images' => true,
        ]);

        $startTime = microtime(true);
        $pdfBinary = (new PdfGenerator())->render($html);
        $genTime = (int) ((microtime(true) - $startTime) * 1000);

        $this->saveCachedPdf($cacheKey, $pdfBinary);
        $this->logExport($sessionRow['id'], 'pdf', $template, strlen($pdfBinary), false, $genTime);
        $rateLimiter->hit($sessionRow['id'], RateLimiter::ACTION_EXPORT_PDF);

        $filename = 'cv-' . $template . '-' . date('Ymd') . '.pdf';
        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setHeader('X-Cache', 'MISS')
            ->setBody($pdfBinary);
    }

    public function txt()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return redirect()->to('/');
        }

        $rateLimiter = new RateLimiter();
        $check = $rateLimiter->check($sessionRow['id'], RateLimiter::ACTION_EXPORT_TXT);

        if (! $check['allowed']) {
            return $this->rateLimitResponse($check, 'TXT');
        }

        $sections = $this->getSections($sessionRow['id']);
        $content = $this->renderToTxt($sections);

        $rateLimiter->hit($sessionRow['id'], RateLimiter::ACTION_EXPORT_TXT);
        $this->logExport($sessionRow['id'], 'txt', null, strlen($content), false);

        $filename = 'cv-' . date('Ymd') . '.txt';
        return $this->response
            ->setHeader('Content-Type', 'text/plain; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    public function json()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return redirect()->to('/');
        }

        $rateLimiter = new RateLimiter();
        $check = $rateLimiter->check($sessionRow['id'], RateLimiter::ACTION_EXPORT_JSON);

        if (! $check['allowed']) {
            return $this->rateLimitResponse($check, 'JSON');
        }

        $sections = $this->getSections($sessionRow['id']);
        $content = json_encode([
            'generated_at' => date('Y-m-d H:i:s'),
            'cv' => $sections,
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

        $rateLimiter->hit($sessionRow['id'], RateLimiter::ACTION_EXPORT_JSON);
        $this->logExport($sessionRow['id'], 'json', null, strlen($content), false);

        $filename = 'cv-' . date('Ymd') . '.json';
        return $this->response
            ->setHeader('Content-Type', 'application/json; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    private function rateLimitResponse(array $check, string $format): object
    {
        $resetIn = max(1, $check['reset_at'] - time());
        $minutes = (int) ceil($resetIn / 60);

        return $this->response
            ->setStatusCode(429)
            ->setJSON([
                'ok' => false,
                'error' => 'rate_limit',
                'message' => "Batas download {$format} tercapai. Silakan coba lagi dalam {$minutes} menit.",
                'remaining' => 0,
                'reset_in' => $resetIn,
            ]);
    }

    private function getSections(int $sessionId): array
    {
        $sections = [];
        $rows = (new CvDataModel())->where('session_id', $sessionId)->findAll();
        foreach ($rows as $row) {
            $sections[$row['section_name']] = json_decode((string) $row['data_json'], true);
        }
        return $sections;
    }

    private function buildCacheKey(int $sessionId, string $template, array $sections): string
    {
        $hash = md5(json_encode($sections) . $template);
        return "pdf_{$sessionId}_{$template}_{$hash}";
    }

    private function getCachedPdf(string $key): ?string
    {
        $cacheFile = WRITEPATH . 'cache/pdf/' . $key . '.cache';
        if (! is_file($cacheFile)) {
            return null;
        }

        $data = unserialize((string) file_get_contents($cacheFile));
        if (! is_array($data)) {
            return null;
        }

        if ($data['expires'] < time()) {
            @unlink($cacheFile);
            return null;
        }

        return $data['content'];
    }

    private function saveCachedPdf(string $key, string $content): void
    {
        $cacheDir = WRITEPATH . 'cache/pdf/';
        if (! is_dir($cacheDir)) {
            mkdir($cacheDir, 0755, true);
        }

        $cacheFile = $cacheDir . $key . '.cache';
        $data = [
            'content' => $content,
            'expires' => time() + self::CACHE_TTL,
            'created' => time(),
        ];

        file_put_contents($cacheFile, serialize($data));
    }

    private function logExport(int $sessionId, string $format, ?string $template, int $fileSize, bool $cached, ?int $genTime = null): void
    {
        $logModel = new \App\Models\CvSessionModel();
        $logModel->update($sessionId, [
            'pdf_generated_count' => $sessionId + 1,
        ]);

        $exportLogsTable = 'export_logs';
        $db = db_connect();

        $contentHash = md5((string) microtime(true));
        $db->table($exportLogsTable)->insert([
            'session_id' => $sessionId,
            'export_format' => $format,
            'template_name' => $template,
            'content_hash' => $contentHash,
            'file_size_bytes' => $fileSize,
            'generation_time_ms' => $genTime,
            'ip_address' => service('request')->getIPAddress(),
            'was_cached' => $cached ? 1 : 0,
            'created_at' => date('Y-m-d H:i:s'),
        ]);
    }

    private function renderToTxt(array $sections): string
    {
        $lines = [];
        $lines[] = str_repeat('=', 60);
        $lines[] = 'CURRICULUM VITAE';
        $lines[] = str_repeat('=', 60);
        $lines[] = '';

        $personal = $sections['personal'] ?? [];
        if (! empty($personal['name'])) {
            $lines[] = strtoupper($personal['name']);
        }
        if (! empty($personal['email'])) {
            $lines[] = 'Email: ' . $personal['email'];
        }
        if (! empty($personal['phone'])) {
            $lines[] = 'HP: ' . $personal['phone'];
        }
        if (! empty($personal['location'])) {
            $lines[] = 'Domisili: ' . $personal['location'];
        }
        if (! empty($personal['linkedin'])) {
            $lines[] = 'LinkedIn: ' . $personal['linkedin'];
        }
        if (! empty($personal['website'])) {
            $lines[] = 'Website: ' . $personal['website'];
        }
        if (! empty($personal['summary'])) {
            $lines[] = '';
            $lines[] = 'RINGKASAN';
            $lines[] = $personal['summary'];
        }

        $eduItems = $sections['education']['items'] ?? [];
        if (is_array($eduItems) && count($eduItems)) {
            $lines[] = '';
            $lines[] = str_repeat('-', 60);
            $lines[] = 'PENDIDIKAN';
            $lines[] = str_repeat('-', 60);
            foreach ($eduItems as $it) {
                $line = '- ' . ($it['school'] ?? '') . ' - ' . ($it['degree'] ?? '');
                if (! empty($it['year'])) {
                    $line .= ' (' . $it['year'] . ')';
                }
                $lines[] = $line;
            }
        }

        $expItems = $sections['experience']['items'] ?? [];
        if (is_array($expItems) && count($expItems)) {
            $lines[] = '';
            $lines[] = str_repeat('-', 60);
            $lines[] = 'PENGALAMAN';
            $lines[] = str_repeat('-', 60);
            foreach ($expItems as $it) {
                $line = '- ' . ($it['company'] ?? '') . ' - ' . ($it['role'] ?? '');
                if (! empty($it['year'])) {
                    $line .= ' (' . $it['year'] . ')';
                }
                $lines[] = $line;
                if (! empty($it['desc'])) {
                    $lines[] = '  ' . $it['desc'];
                }
            }
        }

        $skillItems = $sections['skills']['items'] ?? [];
        if (is_array($skillItems) && count($skillItems)) {
            $lines[] = '';
            $lines[] = str_repeat('-', 60);
            $lines[] = 'KEAHLIAN';
            $lines[] = str_repeat('-', 60);
            $skillLabels = [
                'beginner' => 'Dasar',
                'intermediate' => 'Percakapan',
                'advanced' => 'Profesional',
                'expert' => 'Ahli',
            ];
            foreach ($skillItems as $it) {
                $level = $skillLabels[$it['level']] ?? '';
                $line = '- ' . ($it['name'] ?? '');
                if ($level) {
                    $line .= ' (' . $level . ')';
                }
                $lines[] = $line;
            }
        }

        $langItems = $sections['languages']['items'] ?? [];
        if (is_array($langItems) && count($langItems)) {
            $lines[] = '';
            $lines[] = str_repeat('-', 60);
            $lines[] = 'BAHASA';
            $lines[] = str_repeat('-', 60);
            $langLabels = [
                'native' => 'Penutur Asli',
                'fluent' => 'Fasih',
                'advanced' => 'Profesional',
                'intermediate' => 'Percakapan',
                'beginner' => 'Dasar',
            ];
            foreach ($langItems as $it) {
                $level = $langLabels[$it['level']] ?? '';
                $line = '- ' . ($it['name'] ?? '');
                if ($level) {
                    $line .= ' (' . $level . ')';
                }
                $lines[] = $line;
            }
        }

        $lines[] = '';
        $lines[] = str_repeat('=', 60);
        $lines[] = 'Dibuat dengan MANG-CV | ' . date('d-m-Y H:i');
        $lines[] = str_repeat('=', 60);

        return implode("\n", $lines);
    }
}
