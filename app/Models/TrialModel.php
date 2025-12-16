<?php

namespace App\Models;

use CodeIgniter\Model;

class TrialModel extends Model
{
    protected $table         = 'trials';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'name',
        'city',
        'state',
        'venue',
        'trial_date',
        'reporting_time',
        'status',
    ];

    public function getActiveTrials()
    {
        return $this->where('status', 'active')
            ->where('deleted_at', null)
            ->orderBy('trial_date', 'ASC')
            ->findAll();
    }
}


