<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div>
        <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
        <p class="text-sm text-slate-500 mt-1">
            Quick overview of trials and player registrations.
        </p>
    </div>

    <div class="grid sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Total Players</div>
            <div class="text-2xl font-semibold text-slate-900"><?= (int) $totalPlayers ?></div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Total Trials</div>
            <div class="text-2xl font-semibold text-slate-900"><?= (int) $totalTrials ?></div>
        </div>
        <div class="rounded-2xl bg-white border border-emerald-200 bg-emerald-50 shadow-sm p-4">
            <div class="text-xs uppercase tracking-wide text-emerald-700 mb-1">Active Trials</div>
            <div class="text-2xl font-semibold text-emerald-900"><?= (int) $activeTrials ?></div>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4 text-xs text-slate-600">
        <h2 class="text-sm font-semibold text-slate-900 mb-2">Next Steps</h2>
        <ul class="list-disc list-inside space-y-1">
            <li>Create upcoming trials under the <span class="font-medium">Trials</span> section.</li>
            <li>Monitor new registrations and update payments under <span class="font-medium">Players</span>.</li>
            <li>Use export to generate CSV/Excel reports filtered by city, payment status, and dates.</li>
        </ul>
    </div>
</div>

<?= $this->endSection() ?>


