<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<?php
    $isEdit = ! empty($trial);
?>
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-slate-900">
                <?= $isEdit ? 'Edit Trial' : 'Create Trial' ?>
            </h1>
            <p class="text-xs text-slate-500 mt-0.5">
                Define city, date, venue and status for the trial.
            </p>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-xs text-rose-800 mb-3">
            <?= esc(session()->getFlashdata('error')) ?>
        </div>
    <?php endif; ?>

    <form action="<?= $isEdit ? site_url('admin/trials/update/' . (int) $trial['id']) : site_url('admin/trials/store') ?>" method="post" class="space-y-4 max-w-3xl">
        <?= csrf_field() ?>
        <div class="grid md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Trial Name <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" value="<?= old('name', $trial['name'] ?? '') ?>"
                       class="w-full rounded-md border <?= $validation->hasError('name') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <?php if ($validation->hasError('name')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('name')) ?></p>
                <?php endif; ?>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        City <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="city" value="<?= old('city', $trial['city'] ?? '') ?>"
                           class="w-full rounded-md border <?= $validation->hasError('city') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php if ($validation->hasError('city')): ?>
                        <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('city')) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        State <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="state" value="<?= old('state', $trial['state'] ?? '') ?>"
                           class="w-full rounded-md border <?= $validation->hasError('state') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php if ($validation->hasError('state')): ?>
                        <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('state')) ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-medium text-slate-700 mb-1">
                Venue <span class="text-rose-500">*</span>
            </label>
            <textarea name="venue" rows="2"
                      class="w-full rounded-md border <?= $validation->hasError('venue') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"><?= old('venue', $trial['venue'] ?? '') ?></textarea>
            <?php if ($validation->hasError('venue')): ?>
                <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('venue')) ?></p>
            <?php endif; ?>
        </div>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Trial Date <span class="text-rose-500">*</span>
                </label>
                <input type="date" name="trial_date" value="<?= old('trial_date', $trial['trial_date'] ?? '') ?>"
                       class="w-full rounded-md border <?= $validation->hasError('trial_date') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <?php if ($validation->hasError('trial_date')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('trial_date')) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Reporting Time (optional)
                </label>
                <input type="text" name="reporting_time" placeholder="e.g. 8:30 AM"
                       value="<?= old('reporting_time', $trial['reporting_time'] ?? '') ?>"
                       class="w-full rounded-md border border-slate-300 px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Status <span class="text-rose-500">*</span>
                </label>
                <select name="status"
                        class="w-full rounded-md border <?= $validation->hasError('status') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php $statusValue = old('status', $trial['status'] ?? 'inactive'); ?>
                    <option value="active" <?= $statusValue === 'active' ? 'selected' : '' ?>>Active</option>
                    <option value="inactive" <?= $statusValue === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                </select>
                <?php if ($validation->hasError('status')): ?>
                    <p class="mt-1 text-[11px] text-rose-600"><?= esc($validation->getError('status')) ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="<?= site_url('admin/trials') ?>" class="text-xs text-slate-500 hover:text-slate-700">
                ← Back to list
            </a>
            <button type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-1.5 text-xs font-medium text-white hover:bg-emerald-700">
                <?= $isEdit ? 'Update Trial' : 'Create Trial' ?>
            </button>
        </div>
    </form>
</div>

<?= $this->endSection() ?>


