<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Trial Report</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                <?= esc($trial['name']) ?> · <?= esc($trial['city']) ?>, <?= esc($trial['state']) ?> · <?= date('d M Y', strtotime($trial['trial_date'])) ?>
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?= site_url('admin/attendance/summary/' . $trial['id'] . '/export/csv') . '?' . http_build_query(['attendance' => $filterAttendance, 'payment' => $filterPayment]) ?>"
               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                Export CSV
            </a>
            <a href="<?= site_url('admin/attendance/summary/' . $trial['id'] . '/export/pdf') . '?' . http_build_query(['attendance' => $filterAttendance, 'payment' => $filterPayment]) ?>"
               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                Export PDF
            </a>
            <a href="<?= site_url('admin/attendance') ?>" class="text-xs text-slate-500 hover:text-slate-700">
                ← Back
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <div class="text-[11px] text-slate-500 mb-1">Total Players</div>
            <div class="text-2xl font-bold text-slate-900"><?= count($allPlayers) ?></div>
        </div>
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <div class="text-[11px] text-emerald-600 mb-1">Present</div>
            <div class="text-2xl font-bold text-emerald-700"><?= count($present) ?></div>
        </div>
        <div class="rounded-2xl border border-blue-200 bg-blue-50 p-4 shadow-sm">
            <div class="text-[11px] text-blue-600 mb-1">On-Spot Registration</div>
            <div class="text-2xl font-bold text-blue-700"><?= count($onSpot) ?></div>
        </div>
        <div class="rounded-2xl border border-rose-200 bg-rose-50 p-4 shadow-sm">
            <div class="text-[11px] text-rose-600 mb-1">Absent</div>
            <div class="text-2xl font-bold text-rose-700"><?= count($absent) ?></div>
        </div>
    </div>

    <!-- Payment Status Summary -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 shadow-sm">
            <div class="text-[11px] text-emerald-600 mb-1">Paid</div>
            <div class="text-xl font-bold text-emerald-700"><?= count($paid) ?></div>
        </div>
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-4 shadow-sm">
            <div class="text-[11px] text-amber-600 mb-1">Partially Paid</div>
            <div class="text-xl font-bold text-amber-700"><?= count($partiallyPaid) ?></div>
        </div>
        <div class="rounded-2xl border border-red-200 bg-red-50 p-4 shadow-sm">
            <div class="text-[11px] text-red-600 mb-1">Unpaid</div>
            <div class="text-xl font-bold text-red-700"><?= count($unpaid) ?></div>
        </div>
    </div>

    <!-- Filters -->
    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4 text-xs space-y-3">
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Filter by Attendance</label>
                <select name="attendance"
                        class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="all" <?= $filterAttendance === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="present" <?= $filterAttendance === 'present' ? 'selected' : '' ?>>Present</option>
                    <option value="absent" <?= $filterAttendance === 'absent' ? 'selected' : '' ?>>Absent</option>
                    <option value="on_spot" <?= $filterAttendance === 'on_spot' ? 'selected' : '' ?>>On-Spot Registration</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Filter by Payment Status</label>
                <select name="payment"
                        class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="all" <?= $filterPayment === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="paid" <?= $filterPayment === 'paid' ? 'selected' : '' ?>>Paid</option>
                    <option value="partially_paid" <?= $filterPayment === 'partially_paid' ? 'selected' : '' ?>>Partially Paid</option>
                    <option value="unpaid" <?= $filterPayment === 'unpaid' ? 'selected' : '' ?>>Unpaid</option>
                </select>
            </div>
            <div class="flex items-end">
                <div class="flex space-x-2 w-full">
                    <button type="submit"
                            class="flex-1 inline-flex items-center justify-center rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                        Apply Filters
                    </button>
                    <a href="<?= site_url('admin/attendance/summary/' . $trial['id']) ?>"
                       class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600 hover:bg-slate-50">
                        Clear
                    </a>
                </div>
            </div>
        </div>
    </form>

    <!-- Players Table -->
    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-3 py-2 text-left font-medium">Player</th>
                <th class="px-3 py-2 text-left font-medium">Contact</th>
                <th class="px-3 py-2 text-left font-medium">Type</th>
                <th class="px-3 py-2 text-left font-medium">Attendance</th>
                <th class="px-3 py-2 text-left font-medium">Payment</th>
                <th class="px-3 py-2 text-left font-medium">Fees</th>
                <th class="px-3 py-2 text-left font-medium">Registered</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($players)): ?>
                <tr>
                    <td colspan="7" class="px-3 py-6 text-center text-slate-500">
                        No players found for the selected filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($players as $player): ?>
                    <?php
                    $att = $attendanceByPlayer[$player['id']] ?? null;
                    $isPresent = $att && (int) $att['is_present'] === 1;
                    $isOnSpot = in_array($player['id'], $onSpotPlayerIds, true);

                    $attendanceStatus = 'Absent';
                    $attendanceBadge = 'bg-red-50 text-red-700 border-red-200';
                    if ($isOnSpot) {
                        $attendanceStatus = 'On-Spot';
                        $attendanceBadge = 'bg-blue-50 text-blue-700 border-blue-200';
                    } elseif ($isPresent) {
                        $attendanceStatus = 'Present';
                        $attendanceBadge = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                    }

                    $paymentStatus = $player['payment_status'] ?? 'unpaid';
                    $paymentBadge = match ($paymentStatus) {
                        'paid' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'partially_paid' => 'bg-amber-50 text-amber-700 border-amber-200',
                        default => 'bg-red-50 text-red-700 border-red-200',
                    };
                    $paymentLabel = match ($paymentStatus) {
                        'paid' => 'Paid',
                        'partially_paid' => 'Partially Paid',
                        default => 'Unpaid',
                    };
                    ?>
                    <tr>
                        <td class="px-3 py-2 align-top">
                            <div class="font-medium text-slate-900"><?= esc($player['full_name']) ?></div>
                            <div class="text-[11px] text-slate-500 mt-0.5">
                                Reg: <span class="font-mono"><?= esc($player['registration_id']) ?></span>
                            </div>
                            <?php if (!empty($player['player_state']) || !empty($player['player_city'])): ?>
                                <div class="text-[11px] text-slate-400 mt-0.5">
                                    <?= esc($player['player_city'] ?? '') ?><?= !empty($player['player_state']) ? ', ' . esc($player['player_state']) : '' ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <div>Mobile: <?= esc($player['mobile']) ?></div>
                            <div class="text-[11px] text-slate-500 mt-0.5">Age: <?= esc($player['age']) ?></div>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <?= esc(ucfirst(str_replace('_', ' ', $player['player_type']))) ?>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <span class="inline-flex items-center rounded-full border <?= $attendanceBadge ?> px-2 py-0.5 text-[10px] font-medium">
                                <?= esc($attendanceStatus) ?>
                            </span>
                            <?php if ($isOnSpot): ?>
                                <div class="mt-1 text-[10px] text-blue-600">Registered on trial day</div>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <span class="inline-flex items-center rounded-full border <?= $paymentBadge ?> px-2 py-0.5 text-[10px] font-medium">
                                <?= esc($paymentLabel) ?>
                            </span>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <div>Total: ₹<?= number_format((float)$player['total_fee'], 2) ?></div>
                            <div class="text-[11px] text-slate-500">Paid: ₹<?= number_format((float)$player['paid_amount'], 2) ?></div>
                            <div class="text-[11px] text-slate-500">Due: ₹<?= number_format((float)$player['due_amount'], 2) ?></div>
                        </td>
                        <td class="px-3 py-2 align-top text-[11px] text-slate-500">
                            <?= date('d M Y', strtotime($player['created_at'])) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Payment Summary -->
    <?php if (!empty($paymentSummary)): ?>
        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
            <h2 class="text-sm font-semibold text-slate-900 mb-3">Payment Collection Summary (Trial Day)</h2>
            <div class="grid md:grid-cols-5 gap-4 text-xs">
                <div>
                    <div class="text-[11px] text-slate-500">Registration</div>
                    <div class="text-lg font-bold text-slate-900">₹<?= number_format($paymentSummary['registration'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500">On-Spot</div>
                    <div class="text-lg font-bold text-slate-900">₹<?= number_format($paymentSummary['on_spot'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500">Attendance</div>
                    <div class="text-lg font-bold text-slate-900">₹<?= number_format($paymentSummary['attendance'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500">Adjustment</div>
                    <div class="text-lg font-bold text-slate-900">₹<?= number_format($paymentSummary['adjustment'] ?? 0, 2) ?></div>
                </div>
                <div>
                    <div class="text-[11px] text-slate-500">Total</div>
                    <div class="text-lg font-bold text-emerald-600">₹<?= number_format($paymentSummary['total'] ?? 0, 2) ?></div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
