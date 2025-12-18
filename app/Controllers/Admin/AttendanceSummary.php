<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\PaymentModel;
use App\Models\PlayerModel;
use App\Models\TrialModel;

class AttendanceSummary extends BaseController
{
    public function index(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

        // Get filter parameters
        $filterAttendance = $this->request->getGet('attendance') ?? 'all'; // all, present, absent, on_spot
        $filterPayment    = $this->request->getGet('payment') ?? 'all'; // all, paid, partially_paid, unpaid

        // Get all players for this trial
        $players = $playerModel
            ->where('trial_id', $trialId)
            ->where('deleted_at', null)
            ->orderBy('full_name', 'ASC')
            ->findAll();

        // Get attendance records
        $attendanceRows = $attendanceModel
            ->where('trial_id', $trialId)
            ->findAll();

        $attendanceByPlayer = [];
        foreach ($attendanceRows as $row) {
            $attendanceByPlayer[$row['player_id']] = $row;
        }

        // Get on-spot registrations (players registered on trial date)
        $onSpotPayments = $paymentModel
            ->where('trial_id', $trialId)
            ->where('source', 'on_spot')
            ->where('paid_on', $trial['trial_date'])
            ->findAll();

        $onSpotPlayerIds = array_column($onSpotPayments, 'player_id');
        $onSpotPlayers = [];

        // Categorize players
        $present = [];
        $absent  = [];
        $onSpot  = [];
        $paid    = [];
        $partiallyPaid = [];
        $unpaid  = [];

        foreach ($players as $player) {
            $att = $attendanceByPlayer[$player['id']] ?? null;
            $isPresent = $att && (int) $att['is_present'] === 1;
            $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

            // Categorize by attendance
            if ($isOnSpot) {
                $onSpot[] = $player;
            } elseif ($isPresent) {
                $present[] = $player;
            } else {
                $absent[] = $player;
            }

            // Categorize by payment status
            $paymentStatus = $player['payment_status'] ?? 'unpaid';
            if ($paymentStatus === 'paid') {
                $paid[] = $player;
            } elseif ($paymentStatus === 'partially_paid') {
                $partiallyPaid[] = $player;
            } else {
                $unpaid[] = $player;
            }
        }

        // Apply filters
        $filteredPlayers = $players;
        if ($filterAttendance !== 'all') {
            $filteredPlayers = match ($filterAttendance) {
                'present' => $present,
                'absent' => $absent,
                'on_spot' => $onSpot,
                default => $players,
            };
        }

        if ($filterPayment !== 'all') {
            $filteredPlayers = array_filter($filteredPlayers, function ($player) use ($filterPayment) {
                return ($player['payment_status'] ?? 'unpaid') === $filterPayment;
            });
        }

        // Get payment summary
        $paymentSummary = $paymentModel->getSummaryForTrialDay($trialId, $trial['trial_date']);

        return view('admin/attendance/summary', [
            'title'          => 'Trial Report - ' . $trial['name'],
            'trial'          => $trial,
            'players'        => $filteredPlayers,
            'allPlayers'     => $players,
            'present'        => $present,
            'absent'         => $absent,
            'onSpot'         => $onSpot,
            'paid'           => $paid,
            'partiallyPaid'  => $partiallyPaid,
            'unpaid'         => $unpaid,
            'attendanceByPlayer' => $attendanceByPlayer,
            'onSpotPlayerIds' => $onSpotPlayerIds,
            'paymentSummary' => $paymentSummary,
            'filterAttendance' => $filterAttendance,
            'filterPayment'  => $filterPayment,
        ]);
    }

    public function exportCsv(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

        // Get filter parameters
        $filterAttendance = $this->request->getGet('attendance') ?? 'all';
        $filterPayment    = $this->request->getGet('payment') ?? 'all';

        $players = $playerModel
            ->where('trial_id', $trialId)
            ->where('deleted_at', null)
            ->orderBy('full_name', 'ASC')
            ->findAll();

        $attendanceRows = $attendanceModel
            ->where('trial_id', $trialId)
            ->findAll();

        $attendanceByPlayer = [];
        foreach ($attendanceRows as $row) {
            $attendanceByPlayer[$row['player_id']] = $row;
        }

        $onSpotPayments = $paymentModel
            ->where('trial_id', $trialId)
            ->where('source', 'on_spot')
            ->where('paid_on', $trial['trial_date'])
            ->findAll();

        $onSpotPlayerIds = array_column($onSpotPayments, 'player_id');

        // Apply filters
        $filteredPlayers = $players;
        if ($filterAttendance !== 'all') {
            $filteredPlayers = array_filter($players, function ($player) use ($filterAttendance, $attendanceByPlayer, $onSpotPlayerIds) {
                $att = $attendanceByPlayer[$player['id']] ?? null;
                $isPresent = $att && (int) $att['is_present'] === 1;
                $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

                return match ($filterAttendance) {
                    'present' => $isPresent && !$isOnSpot,
                    'absent' => !$isPresent && !$isOnSpot,
                    'on_spot' => $isOnSpot,
                    default => true,
                };
            });
        }

        if ($filterPayment !== 'all') {
            $filteredPlayers = array_filter($filteredPlayers, function ($player) use ($filterPayment) {
                return ($player['payment_status'] ?? 'unpaid') === $filterPayment;
            });
        }

        $filename = 'trial_report_' . $trialId . '_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'w+');

        fputcsv($output, [
            'Registration ID',
            'Full Name',
            'Mobile',
            'Age',
            'Player Type',
            'Player State',
            'Player City',
            'Attendance Status',
            'On-Spot Registration',
            'Payment Status',
            'Total Fee',
            'Paid Amount',
            'Due Amount',
            'Registered At',
        ]);

        foreach ($filteredPlayers as $player) {
            $att = $attendanceByPlayer[$player['id']] ?? null;
            $isPresent = $att && (int) $att['is_present'] === 1;
            $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

            $attendanceStatus = 'Absent';
            if ($isOnSpot) {
                $attendanceStatus = 'On-Spot';
            } elseif ($isPresent) {
                $attendanceStatus = 'Present';
            }

            fputcsv($output, [
                $player['registration_id'] ?? '',
                $player['full_name'] ?? '',
                $player['mobile'] ?? '',
                $player['age'] ?? '',
                ucfirst(str_replace('_', ' ', $player['player_type'] ?? '')),
                $player['player_state'] ?? '',
                $player['player_city'] ?? '',
                $attendanceStatus,
                $isOnSpot ? 'Yes' : 'No',
                ucfirst(str_replace('_', ' ', $player['payment_status'] ?? 'unpaid')),
                $player['total_fee'] ?? '0.00',
                $player['paid_amount'] ?? '0.00',
                $player['due_amount'] ?? '0.00',
                $player['created_at'] ?? '',
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

    public function exportPdf(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

        // Get filter parameters
        $filterAttendance = $this->request->getGet('attendance') ?? 'all';
        $filterPayment    = $this->request->getGet('payment') ?? 'all';

        $players = $playerModel
            ->where('trial_id', $trialId)
            ->where('deleted_at', null)
            ->orderBy('full_name', 'ASC')
            ->findAll();

        $attendanceRows = $attendanceModel
            ->where('trial_id', $trialId)
            ->findAll();

        $attendanceByPlayer = [];
        foreach ($attendanceRows as $row) {
            $attendanceByPlayer[$row['player_id']] = $row;
        }

        $onSpotPayments = $paymentModel
            ->where('trial_id', $trialId)
            ->where('source', 'on_spot')
            ->where('paid_on', $trial['trial_date'])
            ->findAll();

        $onSpotPlayerIds = array_column($onSpotPayments, 'player_id');

        // Apply filters
        $filteredPlayers = $players;
        if ($filterAttendance !== 'all') {
            $filteredPlayers = array_filter($players, function ($player) use ($filterAttendance, $attendanceByPlayer, $onSpotPlayerIds) {
                $att = $attendanceByPlayer[$player['id']] ?? null;
                $isPresent = $att && (int) $att['is_present'] === 1;
                $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

                return match ($filterAttendance) {
                    'present' => $isPresent && !$isOnSpot,
                    'absent' => !$isPresent && !$isOnSpot,
                    'on_spot' => $isOnSpot,
                    default => true,
                };
            });
        }

        if ($filterPayment !== 'all') {
            $filteredPlayers = array_filter($filteredPlayers, function ($player) use ($filterPayment) {
                return ($player['payment_status'] ?? 'unpaid') === $filterPayment;
            });
        }

        // Generate HTML for PDF
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Trial Report - ' . htmlspecialchars($trial['name']) . '</title>
    <style>
        @media print {
            @page { margin: 1cm; size: A4 landscape; }
            body { margin: 0; }
        }
        body { font-family: Arial, sans-serif; font-size: 9px; margin: 20px; }
        h1 { font-size: 18px; margin-bottom: 5px; color: #1f2937; }
        .meta { font-size: 10px; color: #6b7280; margin-bottom: 15px; }
        .summary { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 15px; }
        .summary-box { padding: 10px; border: 1px solid #d1d5db; border-radius: 4px; background: #f9fafb; }
        .summary-label { font-size: 8px; color: #6b7280; }
        .summary-value { font-size: 14px; font-weight: bold; color: #1f2937; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { background-color: #f3f4f6; border: 1px solid #d1d5db; padding: 6px 4px; text-align: left; font-weight: bold; font-size: 8px; }
        td { border: 1px solid #d1d5db; padding: 4px; font-size: 8px; }
        tr:nth-child(even) { background-color: #f9fafb; }
        .footer { margin-top: 15px; font-size: 9px; color: #6b7280; text-align: right; }
        .status-present { color: #059669; font-weight: bold; }
        .status-absent { color: #dc2626; font-weight: bold; }
        .status-onspot { color: #2563eb; font-weight: bold; }
        .status-paid { color: #059669; font-weight: bold; }
        .status-partial { color: #d97706; font-weight: bold; }
        .status-unpaid { color: #dc2626; font-weight: bold; }
    </style>
</head>
<body>
    <div>
        <h1>Trial Report: ' . htmlspecialchars($trial['name']) . '</h1>
        <div class="meta">
            <div>City: ' . htmlspecialchars($trial['city']) . ', ' . htmlspecialchars($trial['state']) . '</div>
            <div>Date: ' . date('d M Y', strtotime($trial['trial_date'])) . '</div>
            <div>Venue: ' . htmlspecialchars($trial['venue'] ?? '') . '</div>
            <div>Generated on: ' . date('d M Y, h:i A') . '</div>
        </div>
        <div class="summary">
            <div class="summary-box">
                <div class="summary-label">Total Players</div>
                <div class="summary-value">' . count($players) . '</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Present</div>
                <div class="summary-value">' . count(array_filter($players, function($p) use ($attendanceByPlayer, $onSpotPlayerIds) {
                    $att = $attendanceByPlayer[$p['id']] ?? null;
                    return $att && (int)$att['is_present'] === 1 && !in_array($p['id'], $onSpotPlayerIds, true);
                })) . '</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">On-Spot</div>
                <div class="summary-value">' . count($onSpotPlayerIds) . '</div>
            </div>
            <div class="summary-box">
                <div class="summary-label">Absent</div>
                <div class="summary-value">' . count(array_filter($players, function($p) use ($attendanceByPlayer, $onSpotPlayerIds) {
                    $att = $attendanceByPlayer[$p['id']] ?? null;
                    return (!$att || (int)$att['is_present'] !== 1) && !in_array($p['id'], $onSpotPlayerIds, true);
                })) . '</div>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Reg ID</th>
                    <th>Name</th>
                    <th>Mobile</th>
                    <th>Age</th>
                    <th>Type</th>
                    <th>Attendance</th>
                    <th>On-Spot</th>
                    <th>Payment Status</th>
                    <th>Total Fee</th>
                    <th>Paid</th>
                    <th>Due</th>
                </tr>
            </thead>
            <tbody>';

        foreach ($filteredPlayers as $player) {
            $att = $attendanceByPlayer[$player['id']] ?? null;
            $isPresent = $att && (int) $att['is_present'] === 1;
            $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

            $attendanceStatus = 'Absent';
            $attendanceClass = 'status-absent';
            if ($isOnSpot) {
                $attendanceStatus = 'On-Spot';
                $attendanceClass = 'status-onspot';
            } elseif ($isPresent) {
                $attendanceStatus = 'Present';
                $attendanceClass = 'status-present';
            }

            $paymentStatus = ucfirst(str_replace('_', ' ', $player['payment_status'] ?? 'unpaid'));
            $paymentClass = match ($player['payment_status'] ?? 'unpaid') {
                'paid' => 'status-paid',
                'partially_paid' => 'status-partial',
                default => 'status-unpaid',
            };

            $html .= '<tr>
                <td>' . htmlspecialchars($player['registration_id'] ?? '') . '</td>
                <td>' . htmlspecialchars($player['full_name'] ?? '') . '</td>
                <td>' . htmlspecialchars($player['mobile'] ?? '') . '</td>
                <td>' . htmlspecialchars($player['age'] ?? '') . '</td>
                <td>' . htmlspecialchars(ucfirst(str_replace('_', ' ', $player['player_type'] ?? ''))) . '</td>
                <td class="' . $attendanceClass . '">' . htmlspecialchars($attendanceStatus) . '</td>
                <td>' . ($isOnSpot ? 'Yes' : 'No') . '</td>
                <td class="' . $paymentClass . '">' . htmlspecialchars($paymentStatus) . '</td>
                <td>₹' . number_format((float)($player['total_fee'] ?? 0), 2) . '</td>
                <td>₹' . number_format((float)($player['paid_amount'] ?? 0), 2) . '</td>
                <td>₹' . number_format((float)($player['due_amount'] ?? 0), 2) . '</td>
            </tr>';
        }

        $html .= '</tbody>
        </table>
        <div class="footer">
            <p>Total Records: ' . count($filteredPlayers) . '</p>
        </div>
        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>
</html>';

        $filename = 'trial_report_' . $trialId . '_' . date('Ymd_His') . '.html';

        return $this->response
            ->setHeader('Content-Type', 'text/html; charset=UTF-8')
            ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->setBody($html);
    }
}


