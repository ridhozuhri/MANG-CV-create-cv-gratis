<?php

namespace App\Models;

use CodeIgniter\Model;

class RateLimitModel extends Model
{
    protected $table = 'rate_limits';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'key_identifier',
        'action_name',
        'hit_count',
        'window_start',
        'window_duration_seconds',
        'ip_address',
        'last_hit_at',
    ];
    protected $useTimestamps = false;
}