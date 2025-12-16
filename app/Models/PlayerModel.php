<?php

namespace App\Models;

use CodeIgniter\Model;

class PlayerModel extends Model
{
    protected $table         = 'players';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    protected $allowedFields = [
        'registration_id',
        'full_name',
        'age',
        'mobile',
        'player_type',
        'player_state',
        'player_city',
        'trial_id',
        'total_fee',
        'paid_amount',
        'due_amount',
        'payment_status',
        'status_updated_at',
    ];

    public function findByMobile(string $mobile)
    {
        return $this->where('mobile', $mobile)
            ->where('deleted_at', null)
            ->first();
    }

    public function filteredPlayers(array $filters = [])
    {
        $builder = $this->select('players.*, trials.name as trial_name, trials.city as trial_city')
            ->join('trials', 'trials.id = players.trial_id', 'left')
            ->where('players.deleted_at', null);

        if (! empty($filters['name'])) {
            $builder->like('players.full_name', $filters['name']);
        }

        if (! empty($filters['mobile'])) {
            $builder->like('players.mobile', $filters['mobile']);
        }

        if (! empty($filters['payment_status'])) {
            $builder->where('players.payment_status', $filters['payment_status']);
        }

        if (! empty($filters['trial_city'])) {
            $builder->where('trials.city', $filters['trial_city']);
        }

        if (! empty($filters['from_date'])) {
            $builder->where('players.created_at >=', $filters['from_date'] . ' 00:00:00');
        }

        if (! empty($filters['to_date'])) {
            $builder->where('players.created_at <=', $filters['to_date'] . ' 23:59:59');
        }

        return $builder->orderBy('players.created_at', 'DESC');
    }
}


