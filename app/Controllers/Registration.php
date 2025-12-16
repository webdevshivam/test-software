<?php

namespace App\Controllers;

use App\Models\PlayerModel;
use App\Models\TrialModel;
use App\Models\PaymentModel;

class Registration extends BaseController
{
    // Base cricket fees (without T-shirt)
    private const FEES = [
        'batsman'       => 999.00,
        'bowler'        => 999.00,
        'all_rounder'   => 1199.00,
        'wicket_keeper' => 1199.00,
    ];

    private const TSHIRT_FEE = 199.00;

    public function index()
    {
        $trialModel = new TrialModel();

        return view('frontend/register', [
            'title'        => 'Player Registration',
            'trials'       => $trialModel->getActiveTrials(),
            'fees'         => self::FEES,
            'validation'   => \Config\Services::validation(),
            'success'      => session('success'),
            'error'        => session('error'),
        ]);
    }

    public function store()
    {
        $playerModel  = new PlayerModel();
        $trialModel   = new TrialModel();
        $paymentModel = new PaymentModel();

        $rules = [
            'full_name' => 'required|min_length[3]|max_length[150]',
            'age'       => 'required|integer|greater_than_equal_to[5]|less_than_equal_to[60]',
            'mobile'    => 'required|regex_match[/^[6-9][0-9]{9}$/]|is_unique[players.mobile]',
            'player_type' => 'required|in_list[batsman,bowler,all_rounder,wicket_keeper]',
            'trial_id'    => 'required|integer',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors below.');
        }

        $playerType = (string) $this->request->getPost('player_type');
        $trialId    = (int) $this->request->getPost('trial_id');

        $trial = $trialModel->find($trialId);
        if (! $trial || $trial['status'] !== 'active') {
            return redirect()->back()->withInput()->with('error', 'Selected trial is not available.');
        }

        $cricketFee = self::FEES[$playerType] ?? 0.00;
        $totalFee   = $cricketFee + self::TSHIRT_FEE;

        $idData = [
            'year' => date('Y'),
            'rand' => random_int(1000, 9999),
        ];
        $registrationId = sprintf('TRIAL-%s-%s', $idData['year'], $idData['rand']);

        // Assume T-shirt fee is collected at registration.
        $data = [
            'registration_id' => $registrationId,
            'full_name'       => $this->request->getPost('full_name'),
            'age'             => (int) $this->request->getPost('age'),
            'mobile'          => $this->request->getPost('mobile'),
            'player_type'     => $playerType,
            'player_state'    => $this->request->getPost('player_state'),
            'player_city'     => $this->request->getPost('player_city'),
            'trial_id'        => $trialId,
            'total_fee'       => $totalFee,
            'paid_amount'     => self::TSHIRT_FEE,
            'due_amount'      => $cricketFee,
            'payment_status'  => 'partially_paid',
        ];

        $playerId = $playerModel->insert($data);

        // Log registration payment (T-shirt fee) for reporting.
        if ($playerId) {
            $paymentModel->insert([
                'player_id' => $playerId,
                'trial_id'  => $trialId,
                'amount'    => self::TSHIRT_FEE,
                'source'    => 'registration',
                'paid_on'   => date('Y-m-d'),
            ]);
        }

        return redirect()->to('/status')
            ->with('success', 'Registration successful! Please use your mobile number to check status.')
            ->with('registration_id', $registrationId);
    }
}


