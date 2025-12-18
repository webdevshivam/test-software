<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">Payments & Collections</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                View on-spot registrations and partial payments with dates.
            </p>
        </div>
        <a href="<?= site_url('admin/payments/export') . '?' . http_build_query($filters) ?>"
           class="inline-flex items-center rounded-md border border-slate-200 bg-white px-3 py-1.5 text-[11px] font-medium text-slate-700 hover:bg-slate-50">
            Export CSV
        </a>
    </div>

    <form method="get" class="rounded-2xl bg-white border border-slate-200 shadow-sm p-3 text-xs space-y-3">
        <div class="grid md:grid-cols-5 gap-3">
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Type</label>
                <select name="type"
                        class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
                    <option value="">All</option>
                    <option value="on_spot" <?= ($filters['type'] ?? '') === 'on_spot' ? 'selected' : '' ?>>On-spot Registrations</option>
                    <option value="partial" <?= ($filters['type'] ?? '') === 'partial' ? 'selected' : '' ?>>Partial / Adjusted Payments</option>
                </select>
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">From Date</label>
                <input type="date" name="from_date" value="<?= esc($filters['from_date'] ?? '') ?>"
                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">To Date</label>
                <input type="date" name="to_date" value="<?= esc($filters['to_date'] ?? '') ?>"
                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-[11px] font-medium text-slate-600 mb-1">Trial Name / City</label>
                <input type="text" name="trial_name" value="<?= esc($filters['trial_name'] ?? '') ?>"
                       placeholder="Search by trial or city"
                       class="w-full rounded-md border border-slate-300 px-2 py-1.5 text-xs focus:outline-none focus:ring-1 focus:ring-emerald-500">
            </div>
        </div>
        <div class="flex items-center justify-between">
            <p class="text-[11px] text-slate-500">Filters apply to table and export.</p>
            <div class="space-x-2">
                <a href="<?= site_url('admin/payments') ?>"
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
                <th class="px-3 py-2 text-left font-medium">Paid On</th>
                <th class="px-3 py-2 text-left font-medium">Type</th>
                <th class="px-3 py-2 text-left font-medium">Trial</th>
                <th class="px-3 py-2 text-left font-medium">Player</th>
                <th class="px-3 py-2 text-left font-medium">Mobile</th>
                <th class="px-3 py-2 text-left font-medium">Amount</th>
            </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
            <?php if (empty($payments)): ?>
                <tr>
                    <td colspan="6" class="px-3 py-6 text-center text-slate-500">
                        No payments found for selected filters.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($payments as $p): ?>
                    <?php
                    $typeLabel = match ($p['source']) {
                        'on_spot'     => 'On-spot Registration',
                        'attendance'  => 'Attendance Collection',
                        'adjustment'  => 'Admin Adjustment',
                        'registration'=> 'Registration T-shirt',
                        default       => ucfirst((string)$p['source']),
                    };
                    ?>
                    <tr>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            <?= esc($p['paid_on']) ?>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            <?= esc($typeLabel) ?>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            <div class="font-medium text-slate-900"><?= esc($p['trial_name']) ?></div>
                            <div class="text-[11px] text-slate-500">
                                <?= esc($p['trial_city']) ?>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            <div class="font-medium text-slate-900"><?= esc($p['full_name']) ?></div>
                            <div class="text-[11px] text-slate-500">
                                Reg: <span class="font-mono"><?= esc($p['registration_id']) ?></span>
                            </div>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            <?= esc($p['mobile']) ?>
                        </td>
                        <td class="px-3 py-2 text-xs text-slate-700">
                            ₹<?= number_format((float)$p['amount'], 2) ?>
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


