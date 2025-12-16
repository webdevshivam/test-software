<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Players</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                View registrations, filter, update payments and export data.
            </p>
        </div>
        <div class="flex items-center space-x-2">
            <a href="<?= current_url() . '?' . http_build_query($filters) ?>&export=1"
               class="hidden"></a>
            <a href="<?= site_url('admin/players/export/csv') . '?' . http_build_query($filters) ?>"
               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                Export CSV
            </a>
            <a href="<?= site_url('admin/players/export/excel') . '?' . http_build_query($filters) ?>"
               class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
                Export Excel
            </a>
        </div>
    </div>

    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm p-3 text-xs space-y-3">
        <div class="grid md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Name</label>
                <input type="text" name="name" value="<?= esc($filters['name'] ?? '') ?>"
                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Mobile</label>
                <input type="text" name="mobile" value="<?= esc($filters['mobile'] ?? '') ?>"
                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Payment Status</label>
                <select name="payment_status"
                        class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Any</option>
                    <?php
                    $statusFilter = $filters['payment_status'] ?? '';
                    $opts = [
                        'unpaid' => 'Unpaid',
                        'partially_paid' => 'Partially Paid',
                        'paid' => 'Paid',
                        'fully_paid' => 'Fully Paid',
                    ];
                    foreach ($opts as $key => $label): ?>
                        <option value="<?= $key ?>" <?= $statusFilter === $key ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Trial City</label>
                <select name="trial_city"
                        class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">Any</option>
                    <?php
                    $cityFilter = $filters['trial_city'] ?? '';
                    foreach ($trialCities as $cityRow):
                        $city = $cityRow['city'];
                        ?>
                        <option value="<?= esc($city) ?>" <?= $cityFilter === $city ? 'selected' : '' ?>>
                            <?= esc($city) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">From</label>
                    <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>"
                           class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-[11px] font-medium text-slate-600 mb-1">To</label>
                    <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>"
                           class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                </div>
            </div>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-[11px] text-slate-500">Filters apply to table and exports.</p>
            <div class="space-x-2">
                <a href="<?= site_url('admin/players') ?>"
                   class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1 text-[11px] text-slate-600 hover:bg-slate-50">
                    Clear
                </a>
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-3 py-1 text-[11px] font-medium text-white hover:bg-emerald-700">
                    Apply Filters
                </button>
            </div>
        </div>
    </form>

    <div class="overflow-x-auto rounded-2xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full text-xs">
            <thead class="bg-slate-50 text-slate-600">
            <tr>
                <th class="px-3 py-2 text-left font-medium">Player</th>
                <th class="px-3 py-2 text-left font-medium">Trial</th>
                <th class="px-3 py-2 text-left font-medium">Fees</th>
                <th class="px-3 py-2 text-left font-medium">Payment</th>
                <th class="px-3 py-2 text-left font-medium">Registered</th>
                <th class="px-3 py-2 text-right font-medium">Update</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($players)): ?>
                <tr>
                    <td colspan="6" class="px-3 py-6 text-center text-slate-500">
                        No players found for the selected filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($players as $player): ?>
                    <?php
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
                    <tr>
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
                        <td class="px-3 py-2 align-top">
                            <?php if (! empty($player['trial_name'])): ?>
                                <div class="text-xs font-medium text-slate-800"><?= esc($player['trial_name']) ?></div>
                                <div class="text-[11px] text-slate-500">
                                    <?= esc($player['trial_city']) ?>
                                </div>
                            <?php else: ?>
                                <span class="text-[11px] text-slate-400">No trial linked</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-3 py-2 align-top text-xs text-slate-700">
                            <div>Total: ₹<?= number_format((float)$player['total_fee'], 2) ?></div>
                            <div class="text-[11px] text-slate-500">Due: ₹<?= number_format((float)$player['due_amount'], 2) ?></div>
                        </td>
                        <td class="px-3 py-2 align-top">
                            <span class="inline-flex items-center rounded-full border <?= $badgeClass ?> px-2 py-0.5 text-[10px] font-medium">
                                <?= esc($statusLabel) ?>
                            </span>
                            <div class="mt-1 text-[11px] text-slate-500">
                                Paid: ₹<?= number_format((float)$player['paid_amount'], 2) ?>
                            </div>
                        </td>
                        <td class="px-3 py-2 align-top text-[11px] text-slate-500">
                            <?= date('d M Y, h:i A', strtotime($player['created_at'])) ?>
                        </td>
                        <td class="px-3 py-2 align-top text-right">
                            <form action="<?= site_url('admin/players/update-payment/' . (int)$player['id']) ?>" method="post" class="space-y-1 inline-block text-[11px] text-left">
                                <?= csrf_field() ?>
                                <div class="flex items-center space-x-1">
                                    <select name="payment_status"
                                            class="rounded-md border border-slate-300 bg-white px-1.5 py-1 text-[11px] focus:outline-none focus:ring-1 focus:ring-emerald-500">
                                        <?php foreach ($opts as $key => $label): ?>
                                            <option value="<?= $key ?>" <?= $status === $key ? 'selected' : '' ?>><?= $label ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="number" step="0.01" name="paid_amount"
                                           value="<?= esc($player['paid_amount']) ?>"
                                           class="w-20 rounded-md border border-slate-300 px-1.5 py-1 text-[11px] focus:outline-none focus:ring-1 focus:ring-emerald-500"
                                           placeholder="Paid">
                                </div>
                                <button type="submit"
                                        class="mt-1 w-full inline-flex items-center justify-center rounded-md bg-slate-900 px-2 py-1 text-[10px] font-medium text-white hover:bg-slate-800">
                                    Save
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (! empty($pager)): ?>
        <div class="mt-2 text-xs text-slate-500">
            <?= $pager->links() ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>


