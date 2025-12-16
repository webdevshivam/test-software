<?php

namespace App\Models;

use CodeIgniter\Model;

class AttendanceModel extends Model
{
    protected $table         = 'trial_attendance';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'trial_id',
        'player_id',
        'is_present',
        'remarks',
    ];

    public function getByTrialWithPlayers(int $trialId): array
    {
        return $this->select('trial_attendance.*, players.full_name, players.registration_id, players.mobile, players.total_fee, players.paid_amount, players.due_amount, players.payment_status, players.player_type')
            ->join('players', 'players.id = trial_attendance.player_id', 'right')
            ->where('players.trial_id', $trialId)
            ->where('players.deleted_at', null)
            ->orderBy('players.full_name', 'ASC')
            ->findAll();
    }
}


