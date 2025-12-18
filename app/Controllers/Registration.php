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
        $trialModel  = new TrialModel();
        $validation  = \Config\Services::validation();
        $cache       = \Config\Services::cache();
        $sessionErrors = session('errors');
        
        if (is_array($sessionErrors)) {
            // Re-populate the validation object with errors stored in session
            foreach ($sessionErrors as $field => $message) {
                if (is_string($field) && is_string($message)) {
                    $validation->setError($field, $message);
                }
            }
        }

        // Cache active trials for 5 minutes to improve performance
        $cacheKey = 'active_trials_list';
        $trials = $cache->get($cacheKey);
        
        if ($trials === null) {
            $trials = $trialModel->getActiveTrials();
            $cache->save($cacheKey, $trials, 300); // Cache for 5 minutes
        }

        return view('frontend/register', [
            'title'        => 'Player Registration',
            'trials'       => $trials,
            'fees'         => self::FEES,
            'validation'   => $validation,
            'success'      => session('success'),
            'error'        => session('error'),
        ]);
    }

    public function store()
    {
        $playerModel  = new PlayerModel();
        $trialModel   = new TrialModel();
        $paymentModel = new PaymentModel();
        $db           = \Config\Database::connect();

        $rules = [
            'full_name' => [
                'rules'  => 'required|min_length[3]|max_length[150]',
                'errors' => [
                    'required'   => 'Full name is required.',
                    'min_length' => 'Full name must be at least 3 characters.',
                ],
            ],
            'age' => [
                'rules'  => 'required|integer|greater_than_equal_to[5]|less_than_equal_to[60]',
                'errors' => [
                    'required' => 'Age is required.',
                ],
            ],
            'mobile' => [
                'rules'  => 'required|regex_match[/^[6-9][0-9]{9}$/]|is_unique[players.mobile]',
                'errors' => [
                    'required'    => 'Mobile number is required.',
                    'regex_match' => 'Please enter a valid 10-digit Indian mobile number starting with 6–9.',
                    'is_unique'   => 'This mobile number is already registered in MPCL. Use a different number or check your status.',
                ],
            ],
            'player_type' => [
                'rules'  => 'required|in_list[batsman,bowler,all_rounder,wicket_keeper]',
                'errors' => [
                    'required' => 'Please select a player type.',
                ],
            ],
            'trial_id' => [
                'rules'  => 'required|integer',
                'errors' => [
                    'required' => 'Please select a trial city / batch.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            // Redirect back with input and field-level errors stored in session.
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $playerType = (string) $this->request->getPost('player_type');
        $trialId    = (int) $this->request->getPost('trial_id');

        // Optimize trial validation: fetch trial details for registration ID generation
        $trial = $trialModel->select('id, status, name, city')
            ->where('id', $trialId)
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->first();

        if (! $trial) {
            return redirect()->back()->withInput()->with('error', 'Selected trial is not available.');
        }

        $cricketFee = self::FEES[$playerType] ?? 0.00;
        $totalFee   = $cricketFee + self::TSHIRT_FEE;

        // Generate unique registration ID with MPCL prefix and trial code
        $registrationId = $this->generateUniqueRegistrationId($playerModel, $trial);

        // Prepare player data
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
            'paid_amount'     => 0.00,
            'due_amount'      => $totalFee,
            'payment_status'  => 'unpaid',
        ];

        // Use database transaction for atomicity and better performance
        $db->transStart();

        try {
            $playerId = $playerModel->insert($data);

            if (! $playerId) {
                throw new \RuntimeException('Failed to insert player record.');
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                throw new \RuntimeException('Transaction failed.');
            }

            return redirect()->to('/status')
                ->with('success', 'Registration successful! Please use your mobile number to check status.')
                ->with('registration_id', $registrationId);
        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'Registration failed: ' . $e->getMessage());

            return redirect()->back()
                ->withInput()
                ->with('error', 'Registration failed. Please try again or contact support if the problem persists.');
        }
    }

    /**
     * Generate a code from trial name
     * Extracts uppercase letters and numbers, takes first 3-4 characters
     *
     * @param string $trialName
     * @param string $city
     * @return string
     */
    private function generateTrialCode(string $trialName, string $city): string
    {
        // Remove special characters and spaces, convert to uppercase
        $cleanName = preg_replace('/[^A-Za-z0-9]/', '', $trialName);
        $cleanName = strtoupper($cleanName);
        
        // If name is too short, use city code as fallback
        if (strlen($cleanName) < 3) {
            $cityCode = preg_replace('/[^A-Za-z0-9]/', '', $city);
            $cityCode = strtoupper($cityCode);
            $cleanName = substr($cityCode, 0, 3) . $cleanName;
        }
        
        // Take first 3-4 characters for code
        $code = substr($cleanName, 0, 4);
        
        // Ensure minimum 3 characters
        if (strlen($code) < 3) {
            $code = str_pad($code, 3, 'X', STR_PAD_RIGHT);
        }
        
        return $code;
    }

    /**
     * Generate a unique registration ID with format: MPCL-[TRIAL_CODE]-[UNIQUE_NUMBER]
     *
     * @param PlayerModel $playerModel
     * @param array $trial Trial data with name and city
     * @return string
     */
    private function generateUniqueRegistrationId(PlayerModel $playerModel, array $trial): string
    {
        $trialCode = $this->generateTrialCode($trial['name'] ?? '', $trial['city'] ?? '');
        $maxAttempts = 20;
        $startNumber = 1000;
        $endNumber = 9999;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $uniqueNumber = random_int($startNumber, $endNumber);
            $registrationId = sprintf('MPCL-%s-%s', $trialCode, $uniqueNumber);

            // Optimized check: only select ID column for faster query
            $exists = $playerModel->select('id')
                ->where('registration_id', $registrationId)
                ->where('deleted_at', null)
                ->first();

            if (! $exists) {
                return $registrationId;
            }
        }

        // Fallback: use timestamp-based number if random collisions occur
        $fallbackNumber = time() % 10000;
        if ($fallbackNumber < 1000) {
            $fallbackNumber += 1000;
        }
        return sprintf('MPCL-%s-%s', $trialCode, $fallbackNumber);
    }
}


