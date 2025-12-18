<?php

namespace App\Controllers;

use App\Models\PlayerModel;
use App\Models\TrialModel;

class PlayerStatus extends BaseController
{
    public function index()
    {
        return view('frontend/status', [
            'title'          => 'Check Player Status',
            'validation'     => \Config\Services::validation(),
            'player'         => null,
            'trial'          => null,
            'statusCardData' => null,
            'loading'        => false,
            'message'        => session('success'),
            'registrationId' => session('registration_id'),
        ]);
    }

    public function check()
    {
        $rules = [
            'mobile' => 'required|regex_match[/^[6-9][0-9]{9}$/]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please enter a valid registered mobile number.');
        }

        $mobile      = $this->request->getPost('mobile');
        $playerModel = new PlayerModel();
        $player      = $playerModel->findByMobile($mobile);

        if (! $player) {
            return redirect()->back()->withInput()->with('error', 'No registration found for this mobile number.');
        }

        $trial = null;
        if (! empty($player['trial_id'])) {
            $trialModel = new TrialModel();
            $trial      = $trialModel->find($player['trial_id']);
        }

        $statusCardData = $this->buildStatusCardData($player, $trial);

        return view('frontend/status', [
            'title'          => 'Check Player Status',
            'validation'     => \Config\Services::validation(),
            'player'         => $player,
            'trial'          => $trial,
            'statusCardData' => $statusCardData,
            'loading'        => false,
            'message'        => null,
            'registrationId' => $player['registration_id'] ?? null,
        ]);
    }

    private function buildStatusCardData(array $player, ?array $trial): array
    {
        $status = $player['payment_status'];

        $badgeClasses = [
            'unpaid'         => 'bg-red-100 text-red-800',
            'partially_paid' => 'bg-yellow-100 text-yellow-800',
            'paid'           => 'bg-green-100 text-green-800',
        ];

        $labelMap = [
            'unpaid'         => 'Unpaid',
            'partially_paid' => 'Partially Paid',
            'paid'           => 'Paid',
        ];

        $step = match ($status) {
            'unpaid'         => 1,
            'partially_paid' => 2,
            'paid'           => 3,
            default          => 1,
        };

        $showTrialInfo = in_array($status, ['partially_paid', 'paid'], true);

        $nextMessage = match ($status) {
            'unpaid'         => 'Please pay your T-shirt fee (₹199) and cricket trial fee to confirm your slot.',
            'partially_paid' => 'Your T-shirt fee is paid. Please pay the remaining cricket trial fee before the reporting date.',
            'paid'           => 'All fees are paid. Please report on time with your ID proof and kit.',
            default          => '',
        };

        return [
            'status_label' => $labelMap[$status] ?? ucfirst((string) $status),
            'badge_class'  => $badgeClasses[$status] ?? 'bg-gray-100 text-gray-800',
            'progress_step' => $step,
            'show_trial_info' => $showTrialInfo && $trial !== null,
            'next_message'    => $nextMessage,
        ];
    }
}


