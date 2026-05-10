<?php

namespace App\Controllers;

use App\Libraries\ImageProcessor;
use App\Libraries\SessionManager;
use App\Libraries\TemplateManager;
use App\Models\CvDataModel;

class Api extends BaseController
{
    public function autosave()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Sesi tidak valid.']);
        }

        if ($this->isHoneypotTriggered()) {
            $this->handleHoneypot();
            return $this->response->setStatusCode(200)->setJSON(['ok' => true, 'saved_at' => date('Y-m-d H:i:s')]);
        }

        $sectionsPayload = $this->request->getPost('sections');
        if (! is_array($sectionsPayload)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Payload tidak valid.']);
        }

        $model = new CvDataModel();
        $now = date('Y-m-d H:i:s');

        $validSections = ['personal', 'education', 'experience', 'skills', 'languages'];
        foreach ($validSections as $section) {
            if (! array_key_exists($section, $sectionsPayload)) {
                continue;
            }
            $payload = $sectionsPayload[$section];
            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                $payload = is_array($decoded) ? $decoded : [];
            }
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE);
            $existing = $model->where('session_id', $sessionRow['id'])->where('section_name', $section)->first();

            $row = [
                'session_id' => $sessionRow['id'],
                'section_name' => $section,
                'data_json' => $json,
                'data_hash' => md5((string) $json),
                'character_count' => mb_strlen((string) strip_tags((string) $json)),
                'updated_at' => $now,
            ];

            if ($existing) {
                $model->update($existing['id'], $row);
            } else {
                $model->insert($row);
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'saved_at' => $now,
            'csrf' => csrf_hash(),
        ]);
    }

    public function preview()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false]);
        }

        $sections = [];
        $rows = (new CvDataModel())->where('session_id', $sessionRow['id'])->findAll();
        foreach ($rows as $row) {
            $sections[$row['section_name']] = json_decode((string) $row['data_json'], true);
        }

        $template = $this->request->getGet('template') ?? 'classic';
        $html = (new TemplateManager())->renderToHtml($sections, $template, [
            'partial' => true,
            'embed_images' => false,
        ]);
        return $this->response->setJSON(['ok' => true, 'html' => $html, 'csrf' => csrf_hash()]);
    }

    public function previewDraft()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false]);
        }

        $sectionsPayload = $this->request->getPost('sections');
        if (! is_array($sectionsPayload)) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'Payload tidak valid.']);
        }

        $sections = [];
        $validSections = ['personal', 'education', 'experience', 'skills', 'languages'];
        foreach ($validSections as $section) {
            if (! array_key_exists($section, $sectionsPayload)) {
                continue;
            }
            $payload = $sectionsPayload[$section];
            if (is_string($payload)) {
                $decoded = json_decode($payload, true);
                $payload = is_array($decoded) ? $decoded : [];
            }
            $sections[$section] = $payload;
        }

        $template = $this->request->getPost('template') ?? 'classic';
        $html = (new TemplateManager())->renderToHtml($sections, $template, [
            'partial' => true,
            'embed_images' => false,
        ]);
        return $this->response->setJSON(['ok' => true, 'html' => $html, 'csrf' => csrf_hash()]);
    }

    public function uploadPhoto()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Sesi tidak valid.']);
        }

        $file = $this->request->getFile('photo');
        if (! $file) {
            return $this->response->setStatusCode(422)->setJSON(['ok' => false, 'message' => 'File foto tidak ditemukan.']);
        }

        $path = (new ImageProcessor())->processProfilePhoto($file, (string) $sessionRow['session_token']);
        if (! $path) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Upload ditolak. Pastikan file JPG/PNG/WEBP dan maksimal 2MB.',
                'csrf' => csrf_hash(),
            ]);
        }

        $model = new CvDataModel();
        $existing = $model->where('session_id', $sessionRow['id'])->where('section_name', 'personal')->first();
        $personal = $existing ? json_decode((string) $existing['data_json'], true) : [];
        $personal['photo_path'] = $path;
        $json = json_encode($personal, JSON_UNESCAPED_UNICODE);

        $row = [
            'session_id' => $sessionRow['id'],
            'section_name' => 'personal',
            'data_json' => $json,
            'data_hash' => md5((string) $json),
            'character_count' => mb_strlen((string) strip_tags((string) $json)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $model->update($existing['id'], $row);
        } else {
            $model->insert($row);
        }

        return $this->response->setJSON(['ok' => true, 'photo_path' => $path, 'csrf' => csrf_hash()]);
    }

    public function deletePhoto()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Sesi tidak valid.', 'csrf' => csrf_hash()]);
        }

        $model = new CvDataModel();
        $existing = $model->where('session_id', $sessionRow['id'])->where('section_name', 'personal')->first();
        $personal = $existing ? json_decode((string) $existing['data_json'], true) : [];

        $photoRel = (string) ($personal['photo_path'] ?? '');
        if ($photoRel !== '') {
            $prefix = 'writable/uploads/photos/';
            $file = str_starts_with($photoRel, $prefix) ? substr($photoRel, strlen($prefix)) : basename($photoRel);
            $abs = WRITEPATH . 'uploads/photos/' . $file;
            if (is_file($abs)) {
                @unlink($abs);
            }
        }

        unset($personal['photo_path']);
        $json = json_encode($personal, JSON_UNESCAPED_UNICODE);
        $row = [
            'session_id' => $sessionRow['id'],
            'section_name' => 'personal',
            'data_json' => $json,
            'data_hash' => md5((string) $json),
            'character_count' => mb_strlen((string) strip_tags((string) $json)),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $model->update($existing['id'], $row);
        } else {
            $model->insert($row);
        }

        return $this->response->setJSON(['ok' => true, 'csrf' => csrf_hash()]);
    }

    public function switchTemplate()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setJSON(['ok' => false, 'message' => 'Sesi tidak valid.']);
        }

        $template = $this->request->getPost('template') ?? 'classic';
        $validTemplates = ['classic', 'modern', 'sidebar', 'minimalist', 'professional'];

        if (! in_array($template, $validTemplates, true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Template tidak valid.',
                'csrf' => csrf_hash(),
            ]);
        }

        // Update session
        $sessionModel = new \App\Models\CvSessionModel();
        $sessionModel->update($sessionRow['id'], ['selected_template' => $template]);

        // Persist to cv_data so CvDataModel picks it up on next page load
        $dataModel = new CvDataModel();
        $existingTemplateRow = $dataModel->where('session_id', $sessionRow['id'])->where('section_name', '_template')->first();
        $templateRow = [
            'session_id' => $sessionRow['id'],
            'section_name' => '_template',
            'data_json' => json_encode($template),
            'updated_at' => date('Y-m-d H:i:s'),
        ];
        if ($existingTemplateRow) {
            $dataModel->update($existingTemplateRow['id'], $templateRow);
        } else {
            $dataModel->insert($templateRow);
        }

        return $this->response->setJSON([
            'ok' => true,
            'template' => $template,
            'csrf' => csrf_hash(),
        ]);
    }

    public function getTemplates()
    {
        $templates = [
            'classic' => [
                'name' => 'Classic',
                'description' => 'Template klasik profesional',
                'preview' => '/assets/images/templates/classic-preview.png',
            ],
            'modern' => [
                'name' => 'Modern',
                'description' => 'Design modern dengan aksen warna',
                'preview' => '/assets/images/templates/modern-preview.png',
            ],
            'sidebar' => [
                'name' => 'Sidebar',
                'description' => 'Layout dengan sidebar di kiri',
                'preview' => '/assets/images/templates/sidebar-preview.png',
            ],
            'minimalist' => [
                'name' => 'Minimalist',
                'description' => 'Clean dan sederhana',
                'preview' => '/assets/images/templates/minimalist-preview.png',
            ],
            'professional' => [
                'name' => 'Professional',
                'description' => 'Format formal tradisional',
                'preview' => '/assets/images/templates/professional-preview.png',
            ],
        ];

        $sessionRow = (new SessionManager())->currentSession();
        $selected = 'classic';
        if ($sessionRow && ! empty($sessionRow['selected_template'])) {
            $selected = $sessionRow['selected_template'];
        }

        return $this->response->setJSON([
            'ok' => true,
            'templates' => $templates,
            'selected' => $selected,
            'csrf' => csrf_hash(),
        ]);
    }

    public function checkOverflow()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'message' => 'Sesi tidak valid.']);
        }

        $sections = [];
        $rows = (new CvDataModel())->where('session_id', $sessionRow['id'])->findAll();
        foreach ($rows as $row) {
            $sections[$row['section_name']] = json_decode((string) $row['data_json'], true);
        }

        $template = $this->request->getGet('template') ?? $sessionRow['selected_template'] ?? 'classic';

        $analyzer = new \App\Libraries\ContentAnalyzer();
        $analysis = $analyzer->analyze($sections, $template);
        $analysis['recommended_template'] = $analyzer->getRecommendedTemplate($sections);
        $analysis['template_info'] = $analyzer->getTemplateInfo($template);

        return $this->response->setJSON([
            'ok' => true,
            'analysis' => $analysis,
            'csrf' => csrf_hash(),
        ]);
    }

    public function getCapacity()
    {
        $template = $this->request->getGet('template') ?? 'classic';
        $analyzer = new \App\Libraries\ContentAnalyzer();

        return $this->response->setJSON([
            'ok' => true,
            'capacity' => $analyzer->getCapacity($template),
            'template' => $template,
            'csrf' => csrf_hash(),
        ]);
    }

    private function isHoneypotTriggered(): bool
    {
        $honeypot = $this->request->getPost('website_url');
        return ! empty($honeypot);
    }

    private function handleHoneypot(): void
    {
        $sessionRow = (new SessionManager())->currentSession();
        $reportModel = new \App\Models\AbuseReportModel();
        $reportModel->report(
            $sessionRow['id'] ?? null,
            service('request')->getIPAddress(),
            'autosave',
            'Honeypot triggered - possible bot'
        );
        log_message('warning', '[Security] Honeypot triggered from IP: ' . service('request')->getIPAddress());
    }
}
