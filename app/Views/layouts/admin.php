<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Admin - Trial Management') ?></title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/png" href="/favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-100 text-slate-900">
<div class="flex min-h-screen">
    <aside class="w-64 bg-slate-900 text-slate-100 hidden md:flex flex-col">
        <div class="px-5 py-4 border-b border-slate-800 flex items-center space-x-2">
            <div class="h-9 w-9 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold">
                AD
            </div>
            <div>
                <div class="font-semibold tracking-tight text-sm">Admin Panel</div>
                <div class="text-[11px] text-slate-400">Trial Management</div>
            </div>
        </div>
        <nav class="flex-1 px-3 py-4 text-sm space-y-1">
            <a href="<?= site_url('admin') ?>" class="flex items-center px-3 py-2 rounded-md hover:bg-slate-800 <?= uri_string() === 'admin' ? 'bg-slate-800 text-emerald-300' : '' ?>">Dashboard</a>
            <a href="<?= site_url('admin/trials') ?>" class="flex items-center px-3 py-2 rounded-md hover:bg-slate-800 <?= str_starts_with(uri_string(), 'admin/trials') ? 'bg-slate-800 text-emerald-300' : '' ?>">Trials</a>
            <a href="<?= site_url('admin/players') ?>" class="flex items-center px-3 py-2 rounded-md hover:bg-slate-800 <?= str_starts_with(uri_string(), 'admin/players') ? 'bg-slate-800 text-emerald-300' : '' ?>">Players</a>
            <a href="<?= site_url('admin/attendance') ?>" class="flex items-center px-3 py-2 rounded-md hover:bg-slate-800 <?= str_starts_with(uri_string(), 'admin/attendance') ? 'bg-slate-800 text-emerald-300' : '' ?>">Attendance</a>
        </nav>
        <div class="px-3 py-4 border-t border-slate-800 text-xs flex items-center justify-between">
            <span class="text-slate-400">Logged in as Admin</span>
            <a href="<?= site_url('admin/logout') ?>" class="text-rose-400 hover:text-rose-300">Logout</a>
        </div>
    </aside>

    <div class="flex-1 flex flex-col">
        <header class="bg-white border-b border-slate-200">
            <div class="px-4 py-3 flex items-center justify-between">
                <div class="font-semibold text-sm text-slate-700">
                    <?= esc($title ?? 'Admin') ?>
                </div>
                <a href="<?= site_url('/') ?>" class="text-xs text-slate-500 hover:text-emerald-500">
                    View Site
                </a>
            </div>
        </header>

        <main class="flex-1 p-4 md:p-6">
            <?php if (session()->getFlashdata('message')): ?>
                <div class="max-w-5xl mx-auto mb-4">
                    <div class="rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                        <?= esc(session()->getFlashdata('message')) ?>
                    </div>
                </div>
            <?php endif; ?>
            <?php if (session()->getFlashdata('error')): ?>
                <div class="max-w-5xl mx-auto mb-4">
                    <div class="rounded-md border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
                        <?= esc(session()->getFlashdata('error')) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="max-w-6xl mx-auto">
                <?= $this->renderSection('content') ?>
            </div>
        </main>
    </div>
</div>
</body>
</html>


