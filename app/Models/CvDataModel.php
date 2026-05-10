<?php

namespace App\Models;

use CodeIgniter\Model;

class CvDataModel extends Model
{
    protected $table = 'cv_data';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'session_id',
        'section_name',
        'data_json',
        'data_hash',
        'character_count',
    ];
    protected $useTimestamps = false;
}

