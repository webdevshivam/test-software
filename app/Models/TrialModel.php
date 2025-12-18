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

    /**
     * Trials with trial_date today or in the future.
     */
    public function getUpcomingTrials(): array
    {
        return $this->where('deleted_at', null)
            ->where('trial_date >=', date('Y-m-d'))
            ->orderBy('trial_date', 'ASC')
            ->findAll();
    }

    /**
     * Trials with trial_date in the past.
     */
    public function getPastTrials(): array
    {
        return $this->where('deleted_at', null)
            ->where('trial_date <', date('Y-m-d'))
            ->orderBy('trial_date', 'DESC')
            ->findAll();
    }
}


