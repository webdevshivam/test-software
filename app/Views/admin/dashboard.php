<?= $this->extend('layouts/admin') ?>

<?= $this->section('content') ?>
<div class="space-y-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-2">
        <div>
            <h1 class="text-xl font-semibold text-slate-900">Dashboard</h1>
            <p class="text-sm text-slate-500 mt-1">
                Live overview of registrations, trials, and collections.
            </p>
        </div>
        <div class="text-[11px] text-slate-500">
            Data as of <span class="font-semibold"><?= date('d M Y, h:i A') ?></span>
        </div>
    </div>

    <!-- Top summary cards -->
    <div class="grid sm:grid-cols-3 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="text-[11px] uppercase tracking-wide text-slate-500 mb-1">Total Players</div>
            <div class="text-2xl font-semibold text-slate-900"><?= (int) $totalPlayers ?></div>
        </div>
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="text-[11px] uppercase tracking-wide text-slate-500 mb-1">Total Trials</div>
            <div class="text-2xl font-semibold text-slate-900"><?= (int) $totalTrials ?></div>
        </div>
        <div class="rounded-2xl bg-emerald-50 border border-emerald-200 shadow-sm p-4">
            <div class="text-[11px] uppercase tracking-wide text-emerald-700 mb-1">Active Trials</div>
            <div class="text-2xl font-semibold text-emerald-900"><?= (int) $activeTrials ?></div>
        </div>
    </div>

    <!-- Charts row -->
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Registrations (Last 7 Days)</h2>
                    <p class="text-[11px] text-slate-500">Daily count of new player registrations.</p>
                </div>
            </div>
            <div class="h-56">
                <canvas id="registrationsChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4">
            <div class="flex items-center justify-between mb-2">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">Collections by Source (Last 7 Days)</h2>
                    <p class="text-[11px] text-slate-500">Trend of registration, on-spot, attendance, and adjustments.</p>
                </div>
            </div>
            <div class="h-56">
                <canvas id="collectionsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Tables row -->
    <div class="grid lg:grid-cols-2 gap-4">
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4 text-xs text-slate-600">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-900">Upcoming Trials</h2>
                <a href="<?= site_url('admin/trials') ?>" class="text-[11px] text-emerald-600 hover:text-emerald-800">
                    Manage trials →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-2 py-1.5 text-left font-medium">Trial</th>
                        <th class="px-2 py-1.5 text-left font-medium">City</th>
                        <th class="px-2 py-1.5 text-left font-medium">Date</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if (empty($upcomingTrials)): ?>
                        <tr>
                            <td colspan="3" class="px-2 py-4 text-center text-slate-500">
                                No upcoming trials scheduled.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($upcomingTrials as $trial): ?>
                            <tr>
                                <td class="px-2 py-1.5">
                                    <div class="font-medium text-slate-900"><?= esc($trial['name']) ?></div>
                                    <div class="text-[11px] text-slate-500"><?= esc($trial['venue']) ?></div>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    <div class="font-medium"><?= esc($trial['city']) ?></div>
                                    <div class="text-[11px] text-slate-500"><?= esc($trial['state']) ?></div>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    <?= date('d M Y', strtotime($trial['trial_date'])) ?>
                                    <?php if (! empty($trial['reporting_time'])): ?>
                                        <div class="text-[11px] text-slate-500">Reporting: <?= esc($trial['reporting_time']) ?></div>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-4 text-xs text-slate-600">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-sm font-semibold text-slate-900">Recent Registrations</h2>
                <a href="<?= site_url('admin/players') ?>" class="text-[11px] text-emerald-600 hover:text-emerald-800">
                    View all players →
                </a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs">
                    <thead class="bg-slate-50 text-slate-600">
                    <tr>
                        <th class="px-2 py-1.5 text-left font-medium">Player</th>
                        <th class="px-2 py-1.5 text-left font-medium">Mobile</th>
                        <th class="px-2 py-1.5 text-left font-medium">Registered</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                    <?php if (empty($recentPlayers)): ?>
                        <tr>
                            <td colspan="3" class="px-2 py-4 text-center text-slate-500">
                                No recent registrations.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($recentPlayers as $player): ?>
                            <tr>
                                <td class="px-2 py-1.5">
                                    <div class="font-medium text-slate-900"><?= esc($player['full_name']) ?></div>
                                    <div class="text-[11px] text-slate-500">
                                        Reg: <span class="font-mono"><?= esc($player['registration_id'] ?? '-') ?></span>
                                    </div>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    <?= esc($player['mobile']) ?>
                                </td>
                                <td class="px-2 py-1.5 text-xs text-slate-700">
                                    <?= ! empty($player['created_at']) ? date('d M Y, h:i A', strtotime($player['created_at'])) : '-' ?>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function () {
        const registrationData = <?= json_encode($registrationChart ?? ['labels' => [], 'values' => []], JSON_THROW_ON_ERROR) ?>;
        const collectionData   = <?= json_encode($collectionChart ?? ['labels' => [], 'datasets' => []], JSON_THROW_ON_ERROR) ?>;

        const registrationCtx = document.getElementById('registrationsChart')?.getContext('2d');
        if (registrationCtx && registrationData.labels.length) {
            new Chart(registrationCtx, {
                type: 'line',
                data: {
                    labels: registrationData.labels,
                    datasets: [{
                        label: 'Registrations',
                        data: registrationData.values,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        tension: 0.35,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#10b981',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 },
                        }
                    }
                }
            });
        }

        const collectionsCtx = document.getElementById('collectionsChart')?.getContext('2d');
        if (collectionsCtx && collectionData.labels.length) {
            const colors = {
                registration: '#0ea5e9',
                on_spot: '#6366f1',
                attendance: '#f97316',
                adjustment: '#22c55e',
            };

            const datasets = Object.keys(collectionData.datasets || {}).map(function (key) {
                return {
                    label: key.replace('_', ' ').replace(/\b\w/g, c => c.toUpperCase()),
                    data: collectionData.datasets[key],
                    borderColor: colors[key] || '#64748b',
                    backgroundColor: (colors[key] || '#64748b') + '33',
                    tension: 0.35,
                    fill: false,
                    pointRadius: 2,
                };
            });

            new Chart(collectionsCtx, {
                type: 'line',
                data: {
                    labels: collectionData.labels,
                    datasets: datasets,
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: { boxWidth: 10, usePointStyle: true },
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                            callbacks: {
                                label: function (ctx) {
                                    const v = ctx.parsed.y || 0;
                                    return ctx.dataset.label + ': ₹' + v.toFixed(2);
                                }
                            }
                        },
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                        },
                        y: {
                            beginAtZero: true,
                        }
                    }
                }
            });
        }
    })();
</script>

<?= $this->endSection() ?>


