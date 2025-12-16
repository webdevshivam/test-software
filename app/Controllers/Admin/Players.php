<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PlayerModel;
use App\Models\TrialModel;

class Players extends BaseController
{
    public function index()
    {
        $filters = [
            'name'          => $this->request->getGet('name'),
            'mobile'        => $this->request->getGet('mobile'),
            'payment_status'=> $this->request->getGet('payment_status'),
            'trial_city'    => $this->request->getGet('trial_city'),
            'from_date'     => $this->request->getGet('from_date'),
            'to_date'       => $this->request->getGet('to_date'),
        ];

        $playerModel = new PlayerModel();
        $trialModel  = new TrialModel();

        $builder = $playerModel->filteredPlayers($filters);
        $players = $builder->paginate(20);
        $pager   = $builder->pager ?? $playerModel->pager;

        // Use distinct() helper to avoid treating DISTINCT as a column.
        $trialCities = $trialModel
            ->distinct()
            ->select('city')
            ->where('deleted_at', null)
            ->orderBy('city', 'ASC')
            ->findAll();

        return view('admin/players/index', [
            'title'       => 'Manage Players',
            'players'     => $players,
            'pager'       => $pager,
            'filters'     => $filters,
            'trialCities' => $trialCities,
        ]);
    }

    public function updatePayment(int $id)
    {
        $rules = [
            'payment_status' => 'required|in_list[unpaid,partially_paid,paid,fully_paid]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid payment details submitted.');
        }

        $playerModel = new PlayerModel();
        $player      = $playerModel->find($id);

        if (! $player || $player['deleted_at'] !== null) {
            return redirect()->back()->with('error', 'Player not found.');
        }

        $totalFee = (float) $player['total_fee'];
        $status   = (string) $this->request->getPost('payment_status');

        // Business rules:
        // - unpaid:       cricket fee + T-shirt (nothing paid)
        // - partially_paid: only T-shirt fee (₹199) paid, cricket fee due
        // - paid / fully_paid: all fees (cricket + T-shirt) paid
        $tshirtFee  = 199.00;
        $cricketFee = max($totalFee - $tshirtFee, 0.00);

        if ($status === 'unpaid') {
            $paidAmount = 0.00;
        } elseif ($status === 'partially_paid') {
            $paidAmount = min($tshirtFee, $totalFee);
        } else {
            // 'paid' or 'fully_paid' both mean everything paid
            $paidAmount = $totalFee;
            $status     = 'paid';
        }

        $dueAmount = max($totalFee - $paidAmount, 0.00);

        $playerModel->update($id, [
            'paid_amount'     => $paidAmount,
            'due_amount'      => $dueAmount,
            'payment_status'  => $status,
            'status_updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->back()->with('message', 'Payment status updated successfully.');
    }

    public function exportCsv()
    {
        return $this->export('csv');
    }

    public function exportExcel()
    {
        // For now, generate CSV content with .xlsx filename so Excel can open it.
        return $this->export('xlsx');
    }

    private function export(string $format)
    {
        $filters = [
            'name'          => $this->request->getGet('name'),
            'mobile'        => $this->request->getGet('mobile'),
            'payment_status'=> $this->request->getGet('payment_status'),
            'trial_city'    => $this->request->getGet('trial_city'),
            'from_date'     => $this->request->getGet('from_date'),
            'to_date'       => $this->request->getGet('to_date'),
        ];

        $playerModel = new PlayerModel();
        $builder     = $playerModel->filteredPlayers($filters);
        $rows        = $builder->findAll();

        $filename = 'players_' . date('Ymd_His') . '.' . $format;

        $output = fopen('php://temp', 'w+');

        fputcsv($output, [
            'Registration ID',
            'Full Name',
            'Mobile',
            'Age',
            'Player Type',
            'Player State',
            'Player City',
            'Trial Name',
            'Trial City',
            'Total Fee',
            'Paid Amount',
            'Due Amount',
            'Payment Status',
            'Registered At',
        ]);

        foreach ($rows as $row) {
            fputcsv($output, [
                $row['registration_id'] ?? '',
                $row['full_name'] ?? '',
                $row['mobile'] ?? '',
                $row['age'] ?? '',
                $row['player_type'] ?? '',
                $row['player_state'] ?? '',
                $row['player_city'] ?? '',
                $row['trial_name'] ?? '',
                $row['trial_city'] ?? '',
                $row['total_fee'] ?? '',
                $row['paid_amount'] ?? '',
                $row['due_amount'] ?? '',
                $row['payment_status'] ?? '',
                $row['created_at'] ?? '',
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


