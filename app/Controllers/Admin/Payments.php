<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
use App\Models\PlayerModel;
use App\Models\TrialModel;

class Payments extends BaseController
{
    public function index()
    {
        $filters = [
            'type'       => $this->request->getGet('type'),      // on_spot, partial
            'from_date'  => $this->request->getGet('from_date'),
            'to_date'    => $this->request->getGet('to_date'),
            'trial_name' => $this->request->getGet('trial_name'),
        ];

        $paymentModel = new PaymentModel();
        $playerModel  = new PlayerModel();
        $trialModel   = new TrialModel();

        $builder = $paymentModel
            ->select('payments.*, players.full_name, players.mobile, players.player_type, players.payment_status, players.registration_id, trials.name as trial_name, trials.city as trial_city, trials.trial_date')
            ->join('players', 'players.id = payments.player_id', 'left')
            ->join('trials', 'trials.id = payments.trial_id', 'left');

        if ($filters['from_date']) {
            $builder->where('payments.paid_on >=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $builder->where('payments.paid_on <=', $filters['to_date']);
        }

        if ($filters['type'] === 'on_spot') {
            $builder->where('payments.source', 'on_spot');
        } elseif ($filters['type'] === 'partial') {
            $builder->groupStart()
                ->where('payments.source', 'attendance')
                ->orWhere('payments.source', 'adjustment')
            ->groupEnd();
        }

        // Filter by trial name or city (case-insensitive LIKE).
        if (! empty($filters['trial_name'])) {
            $builder->groupStart()
                ->like('trials.name', $filters['trial_name'])
                ->orLike('trials.city', $filters['trial_name'])
            ->groupEnd();
        }

        $builder->orderBy('payments.paid_on', 'DESC')->orderBy('payments.id', 'DESC');

        $payments = $builder->paginate(30);
        $pager    = $paymentModel->pager;

        return view('admin/payments/index', [
            'title'    => 'Payments & Collections',
            'payments' => $payments,
            'pager'    => $pager,
            'filters'  => $filters,
        ]);
    }

    public function export()
    {
        $filters = [
            'type'       => $this->request->getGet('type'),
            'from_date'  => $this->request->getGet('from_date'),
            'to_date'    => $this->request->getGet('to_date'),
            'trial_name' => $this->request->getGet('trial_name'),
        ];

        $paymentModel = new PaymentModel();

        $builder = $paymentModel
            ->select('payments.*, players.full_name, players.mobile, players.player_type, players.payment_status, players.registration_id, trials.name as trial_name, trials.city as trial_city, trials.trial_date')
            ->join('players', 'players.id = payments.player_id', 'left')
            ->join('trials', 'trials.id = payments.trial_id', 'left');

        if ($filters['from_date']) {
            $builder->where('payments.paid_on >=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $builder->where('payments.paid_on <=', $filters['to_date']);
        }

        if ($filters['type'] === 'on_spot') {
            $builder->where('payments.source', 'on_spot');
        } elseif ($filters['type'] === 'partial') {
            $builder->groupStart()
                ->where('payments.source', 'attendance')
                ->orWhere('payments.source', 'adjustment')
            ->groupEnd();
        }

        // Filter by trial name or city (case-insensitive LIKE).
        if (! empty($filters['trial_name'])) {
            $builder->groupStart()
                ->like('trials.name', $filters['trial_name'])
                ->orLike('trials.city', $filters['trial_name'])
            ->groupEnd();
        }

        $rows = $builder->orderBy('payments.paid_on', 'DESC')->findAll();

        $filename = 'payments_' . date('Ymd_His') . '.csv';
        $output   = fopen('php://temp', 'w+');

        fputcsv($output, [
            'Paid On',
            'Source',
            'Trial',
            'Trial City',
            'Player Name',
            'Mobile',
            'Player Type',
            'Registration ID',
            'Payment Status',
            'Amount',
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['paid_on'],
                $row['source'],
                $row['trial_name'],
                $row['trial_city'],
                $row['full_name'],
                $row['mobile'],
                $row['player_type'],
                $row['registration_id'],
                $row['payment_status'],
                $row['amount'],
            ]);
        }

        rewind($output);
        $csvContent = stream_get_contents($output);
        fclose($output);

        return $this->response
            ->setHeader('Content-Type', 'text/csv')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($csvContent);
    }
}


