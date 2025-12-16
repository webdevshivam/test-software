<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PlayerModel;
use App\Models\TrialModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $playerModel = new PlayerModel();
        $trialModel  = new TrialModel();

        $totalPlayers = $playerModel->where('deleted_at', null)->countAllResults();
        $totalTrials  = $trialModel->where('deleted_at', null)->countAllResults();
        $activeTrials = $trialModel->where('status', 'active')->where('deleted_at', null)->countAllResults();

        return view('admin/dashboard', [
            'title'        => 'Admin Dashboard',
            'totalPlayers' => $totalPlayers,
            'totalTrials'  => $totalTrials,
            'activeTrials' => $activeTrials,
        ]);
    }
}


