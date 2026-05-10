<?php

namespace App\Models;

use CodeIgniter\Model;

class CvSessionModel extends Model
{
    protected $table = 'cv_sessions';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_token',
        'fingerprint_hash',
        'ip_address',
        'user_agent_hash',
        'current_step',
        'selected_template',
        'is_flagged',
        'flag_reason',
        'pdf_generated_count',
        'last_activity_at',
        'expires_at',
    ];
    protected $useTimestamps = true;
}

