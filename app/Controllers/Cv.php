<?php

namespace App\Controllers;

use App\Libraries\SessionManager;
use App\Models\CvDataModel;

class Cv extends BaseController
{
    public function wizard()
    {
        return redirect()->to('/buat-cv/step/1');
    }

    public function step(int $step = 1): string
    {
        if ($step < 1 || $step > 5) {
            $step = 1;
        }

        $sm = new SessionManager();
        $sessionRow = $sm->currentSession();
        $data = (new CvDataModel())
            ->where('session_id', $sessionRow['id'] ?? 0)
            ->findAll();

        $sections = [];
        foreach ($data as $row) {
            $sections[$row['section_name']] = json_decode((string) $row['data_json'], true);
        }
        // Also include selected_template from session so JS knows which template is active
        if (!empty($sessionRow['selected_template'])) {
            $sections['_template'] = $sessionRow['selected_template'];
        }

        return view('cv/step', [
            'step' => $step,
            'sections' => $sections,
            'csrf' => csrf_hash(),
        ]);
    }
}
