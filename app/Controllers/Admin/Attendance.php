<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
use App\Models\PaymentModel;
use App\Models\PlayerModel;
use App\Models\TrialModel;

class Attendance extends BaseController
{
    public function index()
    {
        $trialModel = new TrialModel();

        $trials = $trialModel
            ->where('deleted_at', null)
            ->orderBy('trial_date', 'DESC')
            ->findAll();

        return view('admin/attendance/index', [
            'title'  => 'Attendance by Trial',
            'trials' => $trials,
        ]);
    }

    public function manage(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $playerModel = new PlayerModel();

        $players = $playerModel
            ->where('trial_id', $trialId)
            ->where('deleted_at', null)
            ->orderBy('full_name', 'ASC')
            ->findAll();

        $attendanceModel = new AttendanceModel();
        $attendanceRows  = $attendanceModel
            ->where('trial_id', $trialId)
            ->findAll();

        $attendanceByPlayer = [];
        foreach ($attendanceRows as $row) {
            $attendanceByPlayer[$row['player_id']] = $row;
        }

        // Daily collection summary for this trial date.
        $paymentModel = new PaymentModel();
        $summary      = $paymentModel->getSummaryForTrialDay(
            $trialId,
            $trial['trial_date']
        );

        return view('admin/attendance/manage', [
            'title'             => 'Mark Attendance - ' . $trial['name'],
            'trial'             => $trial,
            'players'           => $players,
            'attendanceByPlayer'=> $attendanceByPlayer,
            'collectionSummary' => $summary,
        ]);
    }

    public function save(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $attendanceData = (array) $this->request->getPost('attendance');

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

        $players = $playerModel
            ->where('trial_id', $trialId)
            ->where('deleted_at', null)
            ->findAll();

        foreach ($players as $player) {
            $playerId  = (int) $player['id'];
            $isPresent = isset($attendanceData[$playerId]['is_present']) ? 1 : 0;
            $remarks   = $attendanceData[$playerId]['remarks'] ?? null;
            $collect   = isset($attendanceData[$playerId]['collect_amount'])
                ? (float) $attendanceData[$playerId]['collect_amount']
                : 0.0;

            $existing = $attendanceModel
                ->where('trial_id', $trialId)
                ->where('player_id', $playerId)
                ->first();

            $saveData = [
                'trial_id'  => $trialId,
                'player_id' => $playerId,
                'is_present'=> $isPresent,
                'remarks'   => $remarks,
            ];

            if ($existing) {
                $attendanceModel->update($existing['id'], $saveData);
            } else {
                $attendanceModel->insert($saveData);
            }

            // On-spot collection when taking attendance.
            if ($isPresent && $collect > 0) {
                $currentPaid = (float) $player['paid_amount'];
                $totalFee    = (float) $player['total_fee'];

                $newPaid   = min($currentPaid + $collect, $totalFee);
                $newDue    = max($totalFee - $newPaid, 0.0);
                $newStatus = $newDue <= 0.0 ? 'paid' : $player['payment_status'];

                $playerModel->update($playerId, [
                    'paid_amount'     => $newPaid,
                    'due_amount'      => $newDue,
                    'payment_status'  => $newStatus,
                    'status_updated_at' => date('Y-m-d H:i:s'),
                ]);

                $paymentModel->insert([
                    'player_id' => $playerId,
                    'trial_id'  => $trialId,
                    'amount'    => $collect,
                    'source'    => 'attendance',
                    'paid_on'   => $trial['trial_date'],
                ]);
            }
        }

        return redirect()->back()->with('message', 'Attendance saved successfully.');
    }

    public function onSpotForm(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        return view('admin/attendance/on_spot_form', [
            'title' => 'On-spot Registration - ' . $trial['name'],
            'trial' => $trial,
            'validation' => \Config\Services::validation(),
        ]);
    }

    public function onSpotStore(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $rules = [
            'full_name'   => 'required|min_length[3]|max_length[150]',
            'age'         => 'required|integer|greater_than_equal_to[5]|less_than_equal_to[60]',
            'mobile'      => 'required|regex_match[/^[6-9][0-9]{9}$/]|is_unique[players.mobile]',
            'player_type' => 'required|in_list[batsman,bowler,all_rounder,wicket_keeper]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors below.');
        }

        $playerType = (string) $this->request->getPost('player_type');

        $fees = [
            'batsman'       => 999.00,
            'bowler'        => 999.00,
            'all_rounder'   => 1199.00,
            'wicket_keeper' => 1199.00,
        ];
        $tshirtFee  = 199.00;
        $cricketFee = $fees[$playerType] ?? 0.00;
        $totalFee   = $cricketFee + $tshirtFee;

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

        $registrationId = sprintf('TRIAL-%s-%s', date('Y'), random_int(1000, 9999));

        $playerData = [
            'registration_id' => $registrationId,
            'full_name'       => $this->request->getPost('full_name'),
            'age'             => (int) $this->request->getPost('age'),
            'mobile'          => $this->request->getPost('mobile'),
            'player_type'     => $playerType,
            'player_state'    => $this->request->getPost('player_state'),
            'player_city'     => $this->request->getPost('player_city'),
            'trial_id'        => $trialId,
            'total_fee'       => $totalFee,
            'paid_amount'     => $totalFee,
            'due_amount'      => 0.00,
            'payment_status'  => 'paid',
        ];

        $playerId = $playerModel->insert($playerData);

        if ($playerId) {
            // Mark present by default with remark.
            $attendanceModel->insert([
                'trial_id'  => $trialId,
                'player_id' => $playerId,
                'is_present'=> 1,
                'remarks'   => 'On-spot registration',
            ]);

            // Log full payment as on-spot.
            $paymentModel->insert([
                'player_id' => $playerId,
                'trial_id'  => $trialId,
                'amount'    => $totalFee,
                'source'    => 'on_spot',
                'paid_on'   => $trial['trial_date'],
            ]);
        }

        return redirect()->to('/admin/attendance/manage/' . $trialId)
            ->with('message', 'On-spot player registered and marked present.');
    }

    public function export(int $trialId)
    {
        $trialModel = new TrialModel();
        $trial      = $trialModel->find($trialId);

        if (! $trial || $trial['deleted_at'] !== null) {
            return redirect()->to('/admin/attendance')->with('error', 'Trial not found.');
        }

        $playerModel     = new PlayerModel();
        $attendanceModel = new AttendanceModel();
        $paymentModel    = new PaymentModel();

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

        $filename = 'attendance_trial_' . $trialId . '_' . date('Ymd_His') . '.csv';

        $output = fopen('php://temp', 'w+');

        fputcsv($output, [
            'Registration ID',
            'Full Name',
            'Mobile',
            'Player Type',
            'Present/Absent',
            'On-spot Registration',
            'Total Fee',
            'Paid Amount',
            'Due Amount',
            'Payment Status',
        ]);

        foreach ($players as $player) {
            $attendance = $attendanceByPlayer[$player['id']] ?? null;
            $isPresent  = $attendance && (int) $attendance['is_present'] === 1 ? 'Present' : 'Absent';

            // Determine if this is on-spot registration via payments table.
            $onSpot = $paymentModel
                ->where('player_id', $player['id'])
                ->where('trial_id', $trialId)
                ->where('source', 'on_spot')
                ->where('paid_on', $trial['trial_date'])
                ->first();

            $isOnSpot = $onSpot ? 'Yes' : 'No';

            fputcsv($output, [
                $player['registration_id'],
                $player['full_name'],
                $player['mobile'],
                $player['player_type'],
                $isPresent,
                $isOnSpot,
                $player['total_fee'],
                $player['paid_amount'],
                $player['due_amount'],
                $player['payment_status'],
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


