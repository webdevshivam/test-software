<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">On-spot Registration</h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Trial: <?= esc($trial['name']) ?> · <?= esc($trial['city']) ?>, <?= esc($trial['state']) ?> (<?= date('d M Y', strtotime($trial['trial_date'])) ?>)
            </p>
        </div>
        <a href="<?= site_url('admin/attendance/manage/' . (int) $trial['id']) ?>" class="text-xs text-slate-500 hover:text-slate-700">
            ← Back to attendance
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800 mb-3">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('admin/attendance/on-spot/' . (int) $trial['id']) ?>" method="post" class="space-y-4 max-w-xl">
        <?= csrf_field() ?>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Full Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="full_name" value="<?= old('full_name') ?>"
                       class="w-full rounded-md border <?= $validation->hasError('full_name') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <?php if ($validation->hasError('full_name')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('full_name')) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Age <span class="text-rose-500">*</span>
                </label>
                <input type="number" name="age" min="5" max="60" value="<?= old('age') ?>"
                       class="w-full rounded-md border <?= $validation->hasError('age') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <?php if ($validation->hasError('age')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('age')) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Mobile Number (India) <span class="text-rose-500">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-xs text-slate-500">+91</span>
                    <input type="text" name="mobile" maxlength="10" value="<?= old('mobile') ?>"
                           class="w-full rounded-r-md border <?= $validation->hasError('mobile') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <?php if ($validation->hasError('mobile')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('mobile')) ?></p>
                <?php else: ?>
                    <p class="mt-1 text-[11px] text-slate-500">10 digit mobile starting with 6–9.</p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Player Type <span class="text-rose-500">*</span>
                </label>
                <select name="player_type"
                        class="w-full rounded-md border <?= $validation->hasError('player_type') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Select type</option>
                    <option value="batsman" <?= old('player_type') === 'batsman' ? 'selected' : '' ?>>Batsman (₹999)</option>
                    <option value="bowler" <?= old('player_type') === 'bowler' ? 'selected' : '' ?>>Bowler (₹999)</option>
                    <option value="all_rounder" <?= old('player_type') === 'all_rounder' ? 'selected' : '' ?>>All-Rounder (₹1199)</option>
                    <option value="wicket_keeper" <?= old('player_type') === 'wicket_keeper' ? 'selected' : '' ?>>Wicket-Keeper (₹1199)</option>
                </select>
                <?php if ($validation->hasError('player_type')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('player_type')) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid md:grid-cols-2 gap-3">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Player State
                </label>
                <input type="text" name="player_state" value="<?= old('player_state') ?>"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Player City
                </label>
                <input type="text" name="player_city" value="<?= old('player_city') ?>"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
        </div>

        <div class="rounded-2xl bg-slate-50 border border-slate-200 px-3 py-3 text-[11px] text-slate-600 space-y-1">
            <div class="font-semibold text-slate-800">On-spot Payment Rule</div>
            <p>Collect <span class="font-semibold">₹199 (T-shirt)</span> + <span class="font-semibold">Cricketer Type Fee</span> now.</p>
            <p>The player will be marked as <span class="font-semibold text-emerald-700">Paid</span> with no due amount and marked <span class="font-semibold">Present</span> for this trial.</p>
        </div>

        <div class="flex items-center justify-end pt-2">
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                Save On-spot Registration
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>


