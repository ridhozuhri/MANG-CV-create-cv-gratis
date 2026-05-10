<?php

namespace App\Libraries;

use App\Models\CvSessionModel;
use CodeIgniter\I18n\Time;

class SessionManager
{
    private const KEY = 'cv_session_token';

    public function bootstrap(): array
    {
        $session = session();
        $token = $session->get(self::KEY);
        $model = new CvSessionModel();

        if ($token) {
            $existing = $model->where('session_token', $token)->first();
            if ($existing && strtotime((string) $existing['expires_at']) > time()) {
                $model->update($existing['id'], ['last_activity_at' => Time::now()->toDateTimeString()]);
                return $existing;
            }
        }

        $token = $this->generateToken();
        $data = [
            'session_token' => $token,
            'fingerprint_hash' => hash('sha256', $this->fingerprint()),
            'ip_address' => (string) service('request')->getIPAddress(),
            'user_agent_hash' => hash('sha256', (string) service('request')->getUserAgent()),
            'current_step' => 1,
            'selected_template' => 'classic',
            'last_activity_at' => Time::now()->toDateTimeString(),
            'expires_at' => Time::now()->addDays(30)->toDateTimeString(),
        ];
        $id = $model->insert($data, true);
        $session->set(self::KEY, $token);

        return $model->find($id);
    }

    public function currentSession(): ?array
    {
        $token = session()->get(self::KEY);
        if (! $token) {
            return null;
        }
        return (new CvSessionModel())->where('session_token', $token)->first();
    }

    private function generateToken(): string
    {
        $hex = bin2hex(random_bytes(16));
        return sprintf('%s-%s-%s-%s-%s', substr($hex, 0, 8), substr($hex, 8, 4), substr($hex, 12, 4), substr($hex, 16, 4), substr($hex, 20, 12));
    }

    private function fingerprint(): string
    {
        $request = service('request');
        return implode('|', [
            (string) $request->getUserAgent(),
            (string) $request->getHeaderLine('Accept-Language'),
            (string) $request->getIPAddress(),
        ]);
    }
}

