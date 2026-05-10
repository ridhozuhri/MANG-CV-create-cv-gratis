<?php

namespace App\Controllers;

use App\Libraries\SessionManager;
use App\Models\CvDataModel;

class Media extends BaseController
{
    public function photo()
    {
        $sessionRow = (new SessionManager())->currentSession();
        if (! $sessionRow) {
            return $this->response->setStatusCode(404);
        }

        $row = (new CvDataModel())
            ->where('session_id', $sessionRow['id'])
            ->where('section_name', 'personal')
            ->first();

        $personal = $row ? json_decode((string) $row['data_json'], true) : [];
        $photoRel = (string) ($personal['photo_path'] ?? '');
        if ($photoRel === '') {
            return $this->response->setStatusCode(404);
        }

        // Stored as "writable/uploads/photos/<file>".
        $prefix = 'writable/uploads/photos/';
        $file = str_starts_with($photoRel, $prefix) ? substr($photoRel, strlen($prefix)) : basename($photoRel);
        $abs = WRITEPATH . 'uploads/photos/' . $file;
        if (! is_file($abs)) {
            return $this->response->setStatusCode(404);
        }

        return $this->response
            ->setHeader('Content-Type', 'image/jpeg')
            ->setHeader('Cache-Control', 'no-store')
            ->setBody(file_get_contents($abs));
    }
}

