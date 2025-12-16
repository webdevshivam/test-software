<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\AttendanceModel;
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

        $present = [];
        $absent  = [];

        foreach ($players as $player) {
            $att = $attendanceByPlayer[$player['id']] ?? null;
            if ($att && (int) $att['is_present'] === 1) {
                $present[] = $player;
            } else {
                $absent[] = $player;
            }
        }

        return view('admin/attendance/summary', [
            'title'   => 'Attendance Summary - ' . $trial['name'],
            'trial'   => $trial,
            'present' => $present,
            'absent'  => $absent,
        ]);
    }
}


