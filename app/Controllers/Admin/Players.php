<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PaymentModel;
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

        $isSpamView = (bool) $this->request->getGet('spam');

        $playerModel = new PlayerModel();
        $trialModel  = new TrialModel();

        if ($isSpamView) {
            $builder = $playerModel->spamCandidates($filters);
            $title   = 'Suspicious / Spam Registrations';
        } else {
            $builder = $playerModel->filteredPlayers($filters);
            $title   = 'Manage Players';
        }

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
            'title'       => $title,
            'players'     => $players,
            'pager'       => $pager,
            'filters'     => $filters,
            'trialCities' => $trialCities,
            'isSpamView'  => $isSpamView,
        ]);
    }

    public function bulkDelete()
    {
        $ids = (array) $this->request->getPost('player_ids');

        if (empty($ids)) {
            return redirect()->back()->with('error', 'No players selected for deletion.');
        }

        $playerModel = new PlayerModel();
        $sanitizedIds = array_map('intval', $ids);

        // Use model's soft delete so all related events and timestamp handling stay consistent.
        $playerModel->delete($sanitizedIds);

        return redirect()->back()->with('message', 'Selected players deleted (soft delete).');
    }

    public function updatePayment(int $id)
    {
        $rules = [
            'payment_status' => 'required|in_list[unpaid,partially_paid,paid]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->with('error', 'Invalid payment details submitted.');
        }

        $playerModel  = new PlayerModel();
        $paymentModel = new PaymentModel();
        $player       = $playerModel->find($id);

        if (! $player || $player['deleted_at'] !== null) {
            return redirect()->back()->with('error', 'Player not found.');
        }

        $totalFee = (float) $player['total_fee'];
        $status   = (string) $this->request->getPost('payment_status');

        // Business rules:
        // - unpaid:       nothing paid
        // - partially_paid: only T-shirt fee (₹199) paid, cricket fee due
        // - paid: all fees (cricket + T-shirt) paid
        $tshirtFee  = 199.00;
        $cricketFee = max($totalFee - $tshirtFee, 0.00);

        if ($status === 'unpaid') {
            $paidAmount = 0.00;
        } elseif ($status === 'partially_paid') {
            $paidAmount = min($tshirtFee, $totalFee);
        } else {
            // 'paid' means everything paid
            $paidAmount = $totalFee;
        }

        $oldPaid  = (float) $player['paid_amount'];
        $dueAmount = max($totalFee - $paidAmount, 0.00);

        $playerModel->update($id, [
            'paid_amount'       => $paidAmount,
            'due_amount'        => $dueAmount,
            'payment_status'    => $status,
            'status_updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Log manual admin update as an adjustment payment if amount increased.
        $delta = $paidAmount - $oldPaid;
        if ($delta > 0) {
            $paymentModel->insert([
                'player_id' => $id,
                'trial_id'  => $player['trial_id'],
                'amount'    => $delta,
                'source'    => 'adjustment',
                'paid_on'   => date('Y-m-d'),
            ]);
        }

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

    public function exportPdf()
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

        // Generate HTML for PDF (print-friendly)
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Players Report</title>
    <style>
        @media print {
            @page { margin: 1cm; size: A4 landscape; }
            body { margin: 0; }
        }
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; color: #1f2937; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 4px; text-align: left; font-weight: bold; font-size: 8px; }
        td { border: 1px solid #d1d5db; padding: 4px; font-size: 8px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .footer { margin-top: 15px; font-size: 9px; color: #6b7280; text-align: right; }
        .status-unpaid { color: #dc2626; font-weight: bold; }
        .status-partially { color: #d97706; font-weight: bold; }
        .status-paid { color: #059669; font-weight: bold; }
    </style>
</head>
<body>
    <div>
        <h1>Players Registration Report</h1>
        <div class="meta">Generated on: ' . date('d M Y, h:i A') . '</div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Reg ID</th>
                <th>Name</th>
                <th>Mobile</th>
                <th>Age</th>
                <th>Type</th>
                <th>Trial City</th>
                <th>Trial Name</th>
                <th>Total Fee</th>
                <th>Paid</th>
                <th>Due</th>
                <th>Status</th>
                <th>Registered</th>
            </tr>
        </thead>
        <tbody>';

        foreach ($rows as $row) {
            $status = $row['payment_status'] ?? '';
            $statusLabel = match ($status) {
                'unpaid' => 'Unpaid',
                'partially_paid' => 'Partially Paid',
                'paid' => 'Paid',
                default => ucfirst((string) $status),
            };
            
            $statusClass = match ($status) {
                'unpaid' => 'status-unpaid',
                'partially_paid' => 'status-partially',
                'paid' => 'status-paid',
                default => '',
            };

            $html .= '<tr>
                <td>' . htmlspecialchars($row['registration_id'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['full_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['mobile'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['age'] ?? '') . '</td>
                <td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $row['player_type'] ?? ''))) . '</td>
                <td>' . htmlspecialchars($row['trial_city'] ?? '') . '</td>
                <td>' . htmlspecialchars($row['trial_name'] ?? '') . '</td>
                <td>₹' . number_format((float)($row['total_fee'] ?? 0), 2) . '</td>
                <td>₹' . number_format((float)($row['paid_amount'] ?? 0), 2) . '</td>
                <td>₹' . number_format((float)($row['due_amount'] ?? 0), 2) . '</td>
                <td class="' . $statusClass . '">' . htmlspecialchars($statusLabel) . '</td>
                <td>' . htmlspecialchars(date('d M Y', strtotime($row['created_at'] ?? ''))) . '</td>
            </tr>';
        }

        $html .= '</tbody>
    </table>
    <div class="footer">
        <p>Total Records: ' . count($rows) . '</p>
    </div>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>
</html>';

        $filename = 'players_' . date('Ymd_His') . '.html';

        // Return HTML that opens print dialog for PDF generation
        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($html);
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


