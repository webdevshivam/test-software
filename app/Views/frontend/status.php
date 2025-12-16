<?= $this->extend('layouts/frontend') ?>

<?= $this->section('content') ?>
<div class="grid md:grid-cols-[1.4fr,2fr] gap-8 items-start">
    <section class="order-2 md:order-1">
        <h1 class="text-xl font-semibold text-slate-800 mb-1">Check Registration Status</h1>
        <p class="text-sm text-slate-500 mb-6">
            Enter your registered mobile number to view your payment and trial details.
        </p>

        <?php if (session()->getFlashdata('error')): ?>
            <div class="mb-4 rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                <?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>
        <?php if (! empty($message)): ?>
            <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                <?= esc($message) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('status/check') ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-medium text-slate-700 mb-1">
                    Registered Mobile Number <span class="text-rose-500">*</span>
                </label>
                <div class="flex">
                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-slate-300 bg-slate-50 px-2 text-xs text-slate-500">+91</span>
                    <input type="text" name="mobile" maxlength="10" value="<?= old('mobile') ?>"
                           class="w-full rounded-r-md border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <p class="mt-1 text-[11px] text-slate-500">
                    This is the same number you used while registering.
                </p>
            </div>

            <?php if (! empty($registrationId)): ?>
                <div class="rounded-md bg-slate-50 border border-slate-200 px-3 py-2 text-xs text-slate-600">
                    Your latest Registration ID: <span class="font-mono text-slate-900"><?= esc($registrationId) ?></span>
                </div>
            <?php endif; ?>

            <button type="submit"
                    class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                Check Status
            </button>
        </form>

        <div class="mt-8 rounded-2xl bg-white shadow-sm border border-slate-200 p-4 text-xs text-slate-600">
            <h2 class="text-sm font-semibold text-slate-800 mb-2">What you can see here</h2>
            <ul class="list-disc list-inside space-y-1">
                <li>Your current payment status and due amount (if any).</li>
                <li>Trial venue, date, city and reporting time (if payment is at least partially done).</li>
                <li>Guidance on what to do next based on your status.</li>
            </ul>
        </div>
    </section>

    <aside class="order-1 md:order-2">
        <?php if ($player && $statusCardData): ?>
            <?php
            $status = $player['payment_status'];
            $badgeClass = $statusCardData['badge_class'];
            $statusLabel = $statusCardData['status_label'];
            $step = $statusCardData['progress_step'];
            $showTrial = $statusCardData['show_trial_info'];
            $nextMessage = $statusCardData['next_message'];
            ?>
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-5 space-y-5">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs uppercase tracking-wide text-slate-500 mb-1">Player</div>
                        <div class="text-sm font-semibold text-slate-900">
                            <?= esc($player['full_name']) ?>
                        </div>
                        <div class="text-[11px] text-slate-500 mt-0.5">
                            Reg. ID: <span class="font-mono"><?= esc($player['registration_id']) ?></span>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs text-slate-500 mb-1">Payment Status</div>
                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-[11px] font-medium <?= $badgeClass ?>">
                            <?= esc($statusLabel) ?>
                        </span>
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between text-[11px] text-slate-500 mb-1">
                        <span>Progress</span>
                        <span>
                            <?= $step ?>/3 steps
                        </span>
                    </div>
                    <div class="h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full bg-emerald-500 transition-all" style="width: <?= (int) ($step / 3 * 100) ?>%"></div>
                    </div>
                    <div class="mt-1 flex justify-between text-[10px] text-slate-400">
                        <span>Unpaid</span>
                        <span>Partially Paid</span>
                        <span>Paid</span>
                    </div>
                </div>

                <div class="grid sm:grid-cols-3 gap-3 text-xs">
                    <div class="rounded-lg bg-slate-50 border border-slate-200 px-3 py-2">
                        <div class="text-[10px] uppercase tracking-wide text-slate-500 mb-1">Total Fee</div>
                        <div class="text-sm font-semibold text-slate-900">₹<?= number_format((float) $player['total_fee'], 2) ?></div>
                    </div>
                    <div class="rounded-lg bg-emerald-50 border border-emerald-200 px-3 py-2">
                        <div class="text-[10px] uppercase tracking-wide text-emerald-700 mb-1">Paid Amount</div>
                        <div class="text-sm font-semibold text-emerald-800">₹<?= number_format((float) $player['paid_amount'], 2) ?></div>
                    </div>
                    <div class="rounded-lg bg-amber-50 border border-amber-200 px-3 py-2">
                        <div class="text-[10px] uppercase tracking-wide text-amber-700 mb-1">Due Amount</div>
                        <div class="text-sm font-semibold text-amber-800">₹<?= number_format((float) $player['due_amount'], 2) ?></div>
                    </div>
                </div>

                <div class="rounded-xl bg-slate-900 text-slate-50 px-4 py-3 text-xs">
                    <div class="font-semibold mb-1">What to do next?</div>
                    <p class="text-slate-200"><?= esc($nextMessage) ?></p>
                    <?php if (in_array($status, ['unpaid', 'partially_paid'], true)): ?>
                        <p class="mt-2 text-[11px] text-amber-200">
                            Please complete your remaining payment to avoid disqualification.
                        </p>
                    <?php endif; ?>
                </div>

                <?php if ($showTrial && $trial): ?>
                    <div class="rounded-xl bg-slate-50 border border-slate-200 px-4 py-3 text-xs">
                        <div class="flex items-center justify-between mb-2">
                            <div>
                                <div class="text-[10px] uppercase tracking-wide text-slate-500 mb-0.5">Trial Details</div>
                                <div class="text-sm font-semibold text-slate-900"><?= esc($trial['name']) ?></div>
                            </div>
                            <span class="inline-flex items-center rounded-full bg-sky-100 px-2 py-0.5 text-[10px] font-medium text-sky-800">
                                <?= esc($trial['city']) ?>, <?= esc($trial['state']) ?>
                            </span>
                        </div>
                        <dl class="space-y-1.5 text-[11px] text-slate-600">
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Date</dt>
                                <dd class="font-medium"><?= date('d M Y', strtotime($trial['trial_date'])) ?></dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-slate-500">Venue</dt>
                                <dd class="font-medium text-right"><?= esc($trial['venue']) ?></dd>
                            </div>
                            <?php if (! empty($trial['reporting_time'])): ?>
                                <div class="flex justify-between">
                                    <dt class="text-slate-500">Reporting Time</dt>
                                    <dd class="font-medium"><?= esc($trial['reporting_time']) ?></dd>
                                </div>
                            <?php endif; ?>
                        </dl>
                    </div>
                <?php else: ?>
                    <div class="rounded-xl bg-slate-50 border border-dashed border-slate-300 px-4 py-3 text-xs text-slate-500">
                        Trial details will be visible once you have at least partially completed your payment.
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="rounded-2xl bg-white shadow-sm border border-slate-200 p-5 text-sm text-slate-600">
                <div class="font-semibold text-slate-800 mb-2">No record loaded yet</div>
                <p>Enter your registered mobile number and click <span class="font-medium">Check Status</span> to view your current status.</p>
                <p class="mt-3 text-xs text-slate-500">
                    If you see "No registration found", please verify your mobile number or contact the organizers.
                </p>
            </div>
        <?php endif; ?>
    </aside>
</div>

<?= $this->endSection() ?>


