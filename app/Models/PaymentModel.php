<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table         = 'payments';
    protected $primaryKey    = 'id';
    protected $useTimestamps = true;

    protected $allowedFields = [
        'player_id',
        'trial_id',
        'amount',
        'source',
        'paid_on',
    ];

    public function getSummaryForTrialDay(int $trialId, string $date): array
    {
        $builder = $this->select('source, SUM(amount) as total')
            ->where('trial_id', $trialId)
            ->where('paid_on', $date)
            ->groupBy('source');

        $rows = $builder->findAll();
        $summary = [
            'registration' => 0.0,
            'on_spot'      => 0.0,
            'attendance'   => 0.0,
            'adjustment'   => 0.0,
            'total'        => 0.0,
        ];

        foreach ($rows as $row) {
            $src = $row['source'];
            $sum = (float) $row['total'];
            if (isset($summary[$src])) {
                $summary[$src] += $sum;
            }
            $summary['total'] += $sum;
        }

        return $summary;
    }
}


