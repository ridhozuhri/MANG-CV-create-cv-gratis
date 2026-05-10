<?php

namespace App\Libraries;

use CodeIgniter\HTTP\Files\UploadedFile;

class ImageProcessor
{
    private const MAX_BYTES = 2_097_152; // 2MB

    public function processProfilePhoto(UploadedFile $file, string $sessionToken): ?string
    {
        if (! $file->isValid() || $file->hasMoved()) {
            return null;
        }

        $size = $file->getSize();
        if (! is_int($size) || $size < 1 || $size > self::MAX_BYTES) {
            return null;
        }

        $mime = $file->getMimeType();
        if (! in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
            return null;
        }

        $ext = strtolower((string) $file->getExtension());
        if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            return null;
        }

        $tmp = $file->getTempName();
        $info = @getimagesize($tmp);
        if ($info === false || empty($info[0]) || empty($info[1])) {
            return null;
        }

        $targetDir = WRITEPATH . 'uploads/photos/';
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Always re-encode to JPG to strip metadata and any embedded payloads.
        $filename = $sessionToken . '-' . time() . '-' . bin2hex(random_bytes(4)) . '.jpg';
        $targetPath = $targetDir . $filename;

        $image = \Config\Services::image('gd');
        $image->withFile($tmp)
            ->fit(600, 600, 'center')
            ->save($targetPath, 75);

        if (filesize($targetPath) > 200 * 1024) {
            $image->withFile($targetPath)->save($targetPath, 60);
        }

        return 'writable/uploads/photos/' . $filename;
    }
}
