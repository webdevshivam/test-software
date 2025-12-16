<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Mark Attendance</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Trial: <?= esc($trial['name']) ?> · <?= esc($trial['city']) ?>, <?= esc($trial['state']) ?> (<?= date('d M Y', strtotime($trial['trial_date'])) ?>)
            </p>
        </div>
        <a href="<?= site_url('admin/attendance') ?>" class="text-xs text-slate-500 hover:text-slate-700">
            ← Back to trials
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-3 text-xs text-slate-600 mb-2 space-y-2">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <div class="font-medium text-slate-900"><?= esc($trial['venue']) ?></div>
                <?php if (! empty($trial['reporting_time'])): ?>
                    <div class="text-[11px] text-slate-500">Reporting Time: <?= esc($trial['reporting_time']) ?></div>
                <?php endif; ?>
            </div>
            <div class="text-[11px] text-slate-500">
                Players registered for this trial: <span class="font-semibold"><?= count($players) ?></span>
            </div>
        </div>
        <div class="grid sm:grid-cols-4 gap-2 text-[11px]">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-2.5 py-1.5">
                <div class="text-emerald-700 font-medium mb-0.5">Total Collected</div>
                <div class="text-emerald-900 font-semibold">
                    ₹<?= number_format((float)($collectionSummary['total'] ?? 0), 2) ?>
                </div>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1.5">
                <div class="text-slate-700 mb-0.5">On-spot Registrations</div>
                <div class="text-slate-900 font-semibold">
                    ₹<?= number_format((float)($collectionSummary['on_spot'] ?? 0), 2) ?>
                </div>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1.5">
                <div class="text-slate-700 mb-0.5">Attendance Collections</div>
                <div class="text-slate-900 font-semibold">
                    ₹<?= number_format((float)($collectionSummary['attendance'] ?? 0), 2) ?>
                </div>
            </div>
            <div class="rounded-lg bg-slate-50 border border-slate-200 px-2.5 py-1.5">
                <div class="text-slate-700 mb-0.5">Registration (Pre)</div>
                <div class="text-slate-900 font-semibold">
                    ₹<?= number_format((float)($collectionSummary['registration'] ?? 0), 2) ?>
                </div>
            </div>
        </div>
    </div>

    <form action="<?= site_url('admin/attendance/save/' . (int) $trial['id']) ?>" method="post">
        <?= csrf_field() ?>
        <div class="mb-3 flex items-center justify-between gap-2 text-[11px]">
            <div class="flex items-center gap-2">
                <input type="text" id="attendanceSearch" placeholder="Search by name or mobile"
                       class="w-56 rounded-md border border-slate-300 px-2 py-1.5 text-[11px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                <span class="text-slate-400">Type to filter players while marking attendance.</span>
            </div>
            <div class="flex items-center gap-1">
                <button type="button" data-filter="all"
                        class="attendance-filter inline-flex items-center rounded-md border border-slate-200 bg-white px-2 py-1 text-[11px] text-slate-700 hover:bg-slate-50">
                    All
                </button>
                <button type="button" data-filter="present"
                        class="attendance-filter inline-flex items-center rounded-md border border-emerald-200 bg-emerald-50 px-2 py-1 text-[11px] text-emerald-800 hover:bg-emerald-100">
                    Present
                </button>
                <button type="button" data-filter="absent"
                        class="attendance-filter inline-flex items-center rounded-md border border-rose-200 bg-rose-50 px-2 py-1 text-[11px] text-rose-800 hover:bg-rose-100">
                    Absent
                </button>
            </div>
        </div>

        <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full text-xs">
                <thead class="bg-slate-50 text-slate-600">
                <tr>
                    <th class="px-3 py-2 text-left font-medium">Player</th>
                    <th class="px-3 py-2 text-left font-medium">Payment</th>
                    <th class="px-3 py-2 text-center font-medium">Present</th>
                    <th class="px-3 py-2 text-left font-medium">Collect Now (₹)</th>
                    <th class="px-3 py-2 text-left font-medium">Remarks</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                <?php if (empty($players)): ?>
                    <tr>
                        <td colspan="4" class="px-3 py-6 text-center text-slate-500">
                            No players registered for this trial.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($players as $player): ?>
                        <?php
                        $attendance = $attendanceByPlayer[$player['id']] ?? null;
                        $isPresent  = $attendance['is_present'] ?? 0;

                        $status = $player['payment_status'];
                        $badgeClass = match ($status) {
                            'unpaid' => 'bg-red-50 text-red-700 border-red-200',
                            'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200',
                            'paid' => 'bg-sky-50 text-sky-700 border-sky-200',
                            'fully_paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                            default => 'bg-slate-50 text-slate-700 border-slate-200',
                        };
                        $statusLabel = match ($status) {
                            'unpaid' => 'Unpaid',
                            'partially_paid' => 'Partially Paid',
                            'paid' => 'Paid',
                            'fully_paid' => 'Fully Paid',
                            default => ucfirst((string) $status),
                        };
                        ?>
                        <tr class="attendance-row">
                            <td class="px-3 py-2 align-top">
                                <div class="font-medium text-slate-900"><?= esc($player['full_name']) ?></div>
                                <div class="text-[11px] text-slate-500">
                                    Reg: <span class="font-mono"><?= esc($player['registration_id']) ?></span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Mobile: <?= esc($player['mobile']) ?>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Player Type: <?= esc(ucfirst(str_replace('_', ' ', $player['player_type']))) ?>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-slate-700">
                                <span class="inline-flex items-center rounded-full border <?= $badgeClass ?> px-2 py-0.5 text-[10px] font-medium">
                                    <?= esc($statusLabel) ?>
                                </span>
                                <div class="mt-1 text-[11px] text-slate-500">
                                    Total: ₹<?= number_format((float)$player['total_fee'], 2) ?><br>
                                    Paid: ₹<?= number_format((float)$player['paid_amount'], 2) ?><br>
                                    Due: ₹<?= number_format((float)$player['due_amount'], 2) ?>
                                </div>
                            </td>
                            <td class="px-3 py-2 align-top text-center">
                                <input type="checkbox"
                                       name="attendance[<?= (int) $player['id'] ?>][is_present]"
                                       value="1"
                                       <?= (int)$isPresent === 1 ? 'checked' : '' ?>
                                       class="present-toggle h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500">
                            </td>
                            <td class="px-3 py-2 align-top text-xs text-slate-700">
                                <?php if ((float)$player['due_amount'] > 0): ?>
                                    <input type="number" step="0.01" min="0"
                                           name="attendance[<?= (int) $player['id'] ?>][collect_amount]"
                                           placeholder="0.00"
                                           class="w-24 rounded-md border border-slate-300 px-2 py-1.5 text-[11px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                    <div class="mt-1 text-[10px] text-slate-500">
                                        Due: ₹<?= number_format((float)$player['due_amount'], 2) ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-[11px] text-emerald-700 font-medium">No Due</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-3 py-2 align-top">
                                <input type="text"
                                       name="attendance[<?= (int) $player['id'] ?>][remarks]"
                                       value="<?= esc($attendance['remarks'] ?? '') ?>"
                                       placeholder="Optional note"
                                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-[11px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($players)): ?>
            <div class="mt-3 flex items-center justify-end">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                    Save Attendance
                </button>
            </div>
        <?php endif; ?>
    </form>
</div>

<script>
    const searchInput = document.getElementById('attendanceSearch');
    const rows = document.querySelectorAll('.attendance-row');

    function applyFilters() {
        const term = (searchInput?.value || '').toLowerCase();
        const activeFilterBtn = document.querySelector('.attendance-filter.active');
        const filter = activeFilterBtn ? activeFilterBtn.getAttribute('data-filter') : 'all';

        rows.forEach(function (row) {
            const text = row.innerText.toLowerCase();
            const matchesSearch = text.includes(term);

            const checkbox = row.querySelector('.present-toggle');
            const isPresent = checkbox && checkbox.checked;

            let matchesFilter = true;
            if (filter === 'present') {
                matchesFilter = isPresent;
            } else if (filter === 'absent') {
                matchesFilter = !isPresent;
            }

            row.style.display = matchesSearch && matchesFilter ? '' : 'none';
        });
    }

    if (searchInput) {
        searchInput.addEventListener('input', applyFilters);
    }

    document.querySelectorAll('.attendance-filter').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.querySelectorAll('.attendance-filter').forEach(function (b) {
                b.classList.remove('active');
            });
            this.classList.add('active');
            applyFilters();
        });
    });

    document.querySelectorAll('.present-toggle').forEach(function (cb) {
        cb.addEventListener('change', applyFilters);
    });

    // default filter = All
    const defaultFilter = document.querySelector('.attendance-filter[data-filter="all"]');
    if (defaultFilter) {
        defaultFilter.classList.add('active');
    }
</script>

<?= $this->endSection() ?>


