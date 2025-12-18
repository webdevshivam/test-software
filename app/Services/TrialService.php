<?php

namespace App\Services;

use App\Models\TrialModel;
use Config\Services;

class TrialService
{
    public function __construct(private TrialModel $trialModel)
    {
    }

    /**
     * Get all non-deleted trials ordered by trial date (newest first).
     */
    public function getAll(): array
    {
        return $this->trialModel
            ->where('deleted_at', null)
            ->orderBy('trial_date', 'DESC')
            ->findAll();
    }

    /**
     * Find a single trial by id, ensuring it is not soft-deleted.
     */
    public function findActive(int $id): ?array
    {
        $trial = $this->trialModel->find($id);

        if (! $trial || $trial['deleted_at'] !== null) {
            return null;
        }

        return $trial;
    }

    public function create(array $data): int
    {
        $id = (int) $this->trialModel->insert($data, true);

        // Invalidate cached active trials so frontend registration sees new trial.
        $this->clearActiveTrialsCache();

        return $id;
    }

    public function update(int $id, array $data): bool
    {
        $updated = $this->trialModel->update($id, $data);

        // Clear cache since status/date/city can change.
        $this->clearActiveTrialsCache();

        return $updated;
    }

    public function delete(int $id): bool
    {
        $deleted = $this->trialModel->delete($id);

        // Clear cache so removed trials disappear from registration form.
        $this->clearActiveTrialsCache();

        return $deleted;
    }

    /**
     * Clear the cached list of active trials used by the registration form.
     */
    private function clearActiveTrialsCache(): void
    {
        $cache = Services::cache();
        $cache->delete('active_trials_list');
    }
}
