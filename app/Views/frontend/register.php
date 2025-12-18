<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>
<?php
    // Ensure we always have the current validation instance (with session errors after redirect).
    $validation = $validation ?? \Config\Services::validation();
?>
<div class="grid md:grid-cols-[2fr,1.4fr] gap-8">
    <section>
        <h1 class="text-xl font-semibold text-slate-800 mb-1">Player Registration</h1>
        <p class="text-sm text-slate-500 mb-6">
            Register for upcoming cricket trials. Fields marked with <span class="text-rose-500">*</span> are required.
        </p>

        <?php if (! empty($error)): ?>
            <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <?= esc($error) ?>
            </div>
        <?php endif; ?>
        <?php if (! empty($success)): ?>
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <?= esc($success) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('register') ?>" method="post" class="space-y-5">
            <?= csrf_field() ?>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Full Name <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" name="full_name" value="<?= old('full_name') ?>"
                           class="w-full rounded-md border <?= $validation->hasError('full_name') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php if ($validation->hasError('full_name')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= esc($validation->getError('full_name')) ?></p>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Age <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="age" min="5" max="60" value="<?= old('age') ?>"
                           class="w-full rounded-md border <?= $validation->hasError('age') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <?php if ($validation->hasError('age')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= esc($validation->getError('age')) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Mobile Number (India) <span class="text-rose-500">*</span>
                    </label>
                    <div class="flex">
                        <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-xs text-slate-500">+91</span>
                        <input type="text" name="mobile" maxlength="10" value="<?= old('mobile') ?>"
                               class="w-full rounded-r-md border <?= $validation->hasError('mobile') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    </div>
                    <?php if ($validation->hasError('mobile')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= esc($validation->getError('mobile')) ?></p>
                    <?php else: ?>
                        <p class="mt-1 text-[11px] text-slate-500">10 digit mobile starting with 6–9. This will be used to check your status.</p>
                    <?php endif; ?>
                </div>

                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Player Type <span class="text-rose-500">*</span>
                    </label>
                    <select name="player_type" id="player_type"
                            class="w-full rounded-md border <?= $validation->hasError('player_type') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">Select type</option>
                        <option value="batsman" <?= old('player_type') === 'batsman' ? 'selected' : '' ?>>Batsman</option>
                        <option value="bowler" <?= old('player_type') === 'bowler' ? 'selected' : '' ?>>Bowler</option>
                        <option value="all_rounder" <?= old('player_type') === 'all_rounder' ? 'selected' : '' ?>>All-Rounder</option>
                        <option value="wicket_keeper" <?= old('player_type') === 'wicket_keeper' ? 'selected' : '' ?>>Wicket-Keeper</option>
                    </select>
                    <?php if ($validation->hasError('player_type')): ?>
                        <p class="mt-1 text-xs text-rose-600"><?= esc($validation->getError('player_type')) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Player State
                    </label>
                    <input type="text" name="player_state" value="<?= old('player_state') ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-700 mb-1">
                        Player City
                    </label>
                    <input type="text" name="player_city" value="<?= old('player_city') ?>"
                           class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Trial City / Batch <span class="text-rose-500">*</span>
                </label>
                <select name="trial_id"
                        class="w-full rounded-md border <?= $validation->hasError('trial_id') ? 'border-rose-300' : 'border-slate-300' ?> px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <option value="">Select active trial</option>
                    <?php foreach ($trials as $trial): ?>
                        <option value="<?= (int) $trial['id'] ?>" <?= (string) old('trial_id') === (string) $trial['id'] ? 'selected' : '' ?>>
                            <?= esc($trial['city'] . ' - ' . $trial['name'] . ' (' . date('d M Y', strtotime($trial['trial_date'])) . ')') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($validation->hasError('trial_id')): ?>
                    <p class="mt-1 text-xs text-rose-600"><?= esc($validation->getError('trial_id')) ?></p>
                <?php else: ?>
                    <p class="mt-1 text-[11px] text-slate-500">Only active trials are shown here.</p>
                <?php endif; ?>
            </div>

            <div class="flex items-center justify-between pt-4">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    Submit Registration
                </button>
                <a href="<?= site_url('status') ?>" class="text-xs text-slate-500 hover:text-emerald-600">
                    Already registered? Check your status
                </a>
            </div>
        </form>
    </section>

    <aside class="space-y-4">
        <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-4">
            <h2 class="text-sm font-semibold text-slate-800 mb-3">Fee Details</h2>
            <div class="space-y-2 text-xs">
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Selected Player Type</span>
                    <span id="feePlayerType" class="font-medium text-slate-800">-</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-slate-500">Total Fee</span>
                    <span id="feeAmount" class="font-semibold text-emerald-600 text-sm">₹0</span>
                </div>
            </div>
            <p class="mt-3 text-[11px] text-slate-500">
                Fee will be payable offline at the venue or as per instructions shared by organizers.
            </p>
        </div>

        <div class="rounded-2xl bg-slate-900 text-slate-50 p-4">
            <h2 class="text-sm font-semibold mb-2">Important Instructions</h2>
            <ul class="list-disc list-inside text-[11px] space-y-1 text-slate-200">
                <li>Use a valid mobile number. This will be your primary reference for status.</li>
                <li>Arrive at the venue at least 30 minutes before reporting time.</li>
                <li>Carry your ID proof and basic cricket kit, if available.</li>
            </ul>
        </div>
    </aside>
</div>

<script>
    const fees = <?= json_encode($fees, JSON_THROW_ON_ERROR) ?>;

    function updateFee() {
        const select = document.getElementById('player_type');
        const type = select.value;
        const feePlayerType = document.getElementById('feePlayerType');
        const feeAmount = document.getElementById('feeAmount');

        if (!type || !fees[type]) {
            feePlayerType.textContent = '-';
            feeAmount.textContent = '₹0';
            return;
        }

        const labels = {
            batsman: 'Batsman',
            bowler: 'Bowler',
            all_rounder: 'All-Rounder',
            wicket_keeper: 'Wicket-Keeper'
        };

        feePlayerType.textContent = labels[type] ?? type;
        feeAmount.textContent = '₹' + fees[type];
    }

    document.getElementById('player_type').addEventListener('change', updateFee);
    updateFee();
</script>

<?= $this->endSection() ?>


