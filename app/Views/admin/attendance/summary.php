<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Attendance Summary</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Trial: <?= esc($trial['name']) ?> · <?= esc($trial['city']) ?>, <?= esc($trial['state']) ?> (<?= date('d M Y', strtotime($trial['trial_date'])) ?>)
            </p>
        </div>
        <a href="<?= site_url('admin/attendance') ?>" class="text-xs text-slate-500 hover:text-slate-700">
            ← Back to trials
        </a>
    </div>

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-emerald-800">Present Players (<?= count($present) ?>)</h2>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-emerald-100 bg-emerald-50/50">
                <table class="min-w-full text-xs">
                    <thead class="bg-emerald-50 text-emerald-800">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Player</th>
                        <th class="px-3 py-2 text-left font-medium">Mobile</th>
                        <th class="px-3 py-2 text-left font-medium">Type</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-emerald-100">
                    <?php if (empty($present)): ?>
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-emerald-700/70">
                                No present players recorded.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($present as $player): ?>
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900"><?= esc($player['full_name']) ?></div>
                                    <div class="text-[11px] text-slate-500">
                                        Reg: <span class="font-mono"><?= esc($player['registration_id']) ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    <?= esc($player['mobile']) ?>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    <?= esc(ucfirst(str_replace('_', ' ', $player['player_type']))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-rose-800">Absent Players (<?= count($absent) ?>)</h2>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-rose-100 bg-rose-50/50">
                <table class="min-w-full text-xs">
                    <thead class="bg-rose-50 text-rose-800">
                    <tr>
                        <th class="px-3 py-2 text-left font-medium">Player</th>
                        <th class="px-3 py-2 text-left font-medium">Mobile</th>
                        <th class="px-3 py-2 text-left font-medium">Type</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-rose-100">
                    <?php if (empty($absent)): ?>
                        <tr>
                            <td colspan="3" class="px-3 py-4 text-center text-rose-700/70">
                                No absent players recorded.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($absent as $player): ?>
                            <tr>
                                <td class="px-3 py-2">
                                    <div class="font-medium text-slate-900"><?= esc($player['full_name']) ?></div>
                                    <div class="text-[11px] text-slate-500">
                                        Reg: <span class="font-mono"><?= esc($player['registration_id']) ?></span>
                                    </div>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    <?= esc($player['mobile']) ?>
                                </td>
                                <td class="px-3 py-2 text-xs text-slate-700">
                                    <?= esc(ucfirst(str_replace('_', ' ', $player['player_type']))) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>


