<?php

namespace App\Services;

use App\Models\PlayerModel;
use App\Models\TrialModel;
use App\Models\PaymentModel;

class DashboardService
{
    public function __construct(
        private PlayerModel $playerModel,
        private TrialModel $trialModel,
        private PaymentModel $paymentModel,
    ) {
    }

    /**
     * Return key dashboard statistics for the admin panel.
     */
    public function getSummaryStats(): array
    {
        $totalPlayers = $this->playerModel
            ->where('deleted_at', null)
            ->countAllResults();

        $totalTrials = $this->trialModel
            ->where('deleted_at', null)
            ->countAllResults();

        $activeTrials = $this->trialModel
            ->where('status', 'active')
            ->where('deleted_at', null)
            ->countAllResults();

        $upcomingTrials = $this->trialModel
            ->where('deleted_at', null)
            ->where('trial_date >=', date('Y-m-d'))
            ->orderBy('trial_date', 'ASC')
            ->limit(5)
            ->findAll();

        $recentPlayers = $this->playerModel
            ->where('deleted_at', null)
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Registrations over last 7 days
        $registrationChart = $this->getRegistrationTrend(7);

        // Collections over last 7 days
        $collectionChart = $this->getCollectionTrend(7);

        return [
            'totalPlayers'       => $totalPlayers,
            'totalTrials'        => $totalTrials,
            'activeTrials'       => $activeTrials,
            'upcomingTrials'     => $upcomingTrials,
            'recentPlayers'      => $recentPlayers,
            'registrationChart'  => $registrationChart,
            'collectionChart'    => $collectionChart,
        ];
    }

    /**
     * Get registrations per day for the last $days days (including today).
     */
    private function getRegistrationTrend(int $days): array
    {
        $end   = new \DateTimeImmutable('today');
        $start = $end->sub(new \DateInterval('P' . max($days - 1, 0) . 'D'));

        $builder = $this->playerModel
            ->select('DATE(created_at) as day, COUNT(*) as total')
            ->where('deleted_at', null)
            ->where('created_at >=', $start->format('Y-m-d 00:00:00'))
            ->where('created_at <=', $end->format('Y-m-d 23:59:59'))
            ->groupBy('DATE(created_at)')
            ->orderBy('day', 'ASC');

        $rows = $builder->findAll();

        $map = [];
        foreach ($rows as $row) {
            $map[$row['day']] = (int) $row['total'];
        }

        $labels = [];
        $values = [];

        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->add(new \DateInterval('P1D')));
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            $values[] = $map[$day] ?? 0;
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Get collection totals per day for last $days days, split by source.
     */
    private function getCollectionTrend(int $days): array
    {
        $end   = new \DateTimeImmutable('today');
        $start = $end->sub(new \DateInterval('P' . max($days - 1, 0) . 'D'));

        $builder = $this->paymentModel
            ->select('paid_on, source, SUM(amount) as total')
            ->where('paid_on >=', $start->format('Y-m-d'))
            ->where('paid_on <=', $end->format('Y-m-d'))
            ->groupBy('paid_on, source')
            ->orderBy('paid_on', 'ASC');

        $rows = $builder->findAll();

        // Known sources
        $sources = ['registration', 'on_spot', 'attendance', 'adjustment'];
        $data    = [];

        $period = new \DatePeriod($start, new \DateInterval('P1D'), $end->add(new \DateInterval('P1D')));
        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $data[$day] = array_fill_keys($sources, 0.0);
        }

        foreach ($rows as $row) {
            $day = $row['paid_on'];
            $src = $row['source'];
            if (! isset($data[$day])) {
                $data[$day] = array_fill_keys($sources, 0.0);
            }
            if (! isset($data[$day][$src])) {
                $data[$day][$src] = 0.0;
            }
            $data[$day][$src] += (float) $row['total'];
        }

        $labels = [];
        $datasets = [];
        foreach ($sources as $src) {
            $datasets[$src] = [];
        }

        foreach ($period as $date) {
            $day = $date->format('Y-m-d');
            $labels[] = $date->format('d M');
            foreach ($sources as $src) {
                $datasets[$src][] = $data[$day][$src] ?? 0.0;
            }
        }

        return [
            'labels'   => $labels,
            'datasets' => $datasets,
        ];
    }
}
