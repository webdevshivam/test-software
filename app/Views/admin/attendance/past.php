<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Past Trials - Attendance</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Trials that have already finished. You can open reports or exports from here.
            </p>
        </div>
        <a href="<?= site_url('admin/attendance') ?>" class="text-xs text-slate-500 hover:text-slate-700">
            860 Back to upcoming trials
        </a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-3 py-2 text-left font-medium">Trial</th>
                <th class="px-3 py-2 text-left font-medium">City</th>
                <th class="px-3 py-2 text-left font-medium">Date</th>
                <th class="px-3 py-2 text-left font-medium">Status</th>
                <th class="px-3 py-2 text-right font-medium">Actions</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($trials)): ?>
                <tr>
                    <td colspan="5" class="px-3 py-6 text-center text-slate-500">
                        No past trials found.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($trials as $trial): ?>
                    <tr>
                        <td class="px-3 py-2 align-top">
                            <div class="font-medium text-slate-900"><?= esc($trial['name']) ?></div>
                            <div class="text-[11px] text-slate-500"><?= esc($trial['venue']) ?></div>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <div class="font-medium"><?= esc($trial['city']) ?></div>
                            <div class="text-[11px] text-slate-500"><?= esc($trial['state']) ?></div>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <?= date('d M Y', strtotime($trial['trial_date'])) ?>
                            <?php if (! empty($trial['reporting_time'])): ?>
                                <div class="text-[11px] text-slate-500">Reporting: <?= esc($trial['reporting_time']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <?php if ($trial['status'] === 'active'): ?>
                                <span class="inline-flex items-center rounded-full bg-emerald-50 px-2 py-0.5 text-[10px] font-medium text-emerald-700">
                                    Active
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center rounded-full bg-slate-100 px-2 py-0.5 text-[10px] font-medium text-slate-600">
                                    Inactive
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 align-top text-right space-y-1">
                            <div>
                                <a href="<?= site_url('admin/attendance/summary/' . (int) $trial['id']) ?>"
                                   class="inline-flex items-center rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-[11px] font-medium text-blue-800 hover:bg-blue-100">
                                    View Full Report
                                </a>
                            </div>
                            <div>
                                <a href="<?= site_url('admin/attendance/export/' . (int) $trial['id']) ?>"
                                   class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                                    Export Attendance
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?= $this->endSection() ?>
