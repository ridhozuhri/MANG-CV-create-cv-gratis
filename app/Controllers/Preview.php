<?php

namespace App\Controllers;

use App\Libraries\SessionManager;
use App\Libraries\TemplateManager;
use App\Models\CvDataModel;

class Preview extends BaseController
{
    public function index(): string
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return view('preview/index', [
                'html' => '<p>Sesi tidak valid.</p>',
                'csrf' => csrf_hash(),
            ]);
        }

        $sections = [];
        $rows = (new CvDataModel())->where('session_id', $sessionRow['id'])->findAll();
        foreach ($rows as $row) {
            $sections[$row['section_name']] = json_decode((string) $row['data_json'], true);
        }

        $template = $this->request->getGet('template') ?? $sessionRow['selected_template'] ?? 'classic';

        $html = (new TemplateManager())->renderToHtml($sections, $template, [
            'partial' => true,
            'embed_images' => false,
        ]);

        return view('preview/index', [
            'html' => $html,
            'csrf' => csrf_hash(),
            'selected_template' => $template,
        ]);
    }
}