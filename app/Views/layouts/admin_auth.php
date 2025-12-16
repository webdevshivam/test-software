<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Admin Login') ?></title>
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
<body class="min-h-screen bg-slate-900 text-slate-50 flex items-center justify-center px-4">
<div class="w-full max-w-md">
    <div class="bg-slate-900/60 border border-slate-700 rounded-2xl shadow-xl p-6">
        <div class="flex items-center space-x-3 mb-6">
            <div class="h-10 w-10 rounded-xl bg-emerald-500 flex items-center justify-center text-white font-semibold">
                AD
            </div>
            <div>
                <div class="text-sm font-semibold tracking-tight">Admin Panel</div>
                <div class="text-[11px] text-slate-400">Trial Management System</div>
            </div>
        </div>

        <?php if (! empty($message)): ?>
            <div class="mb-4 rounded-md border border-rose-500/40 bg-rose-500/10 px-3 py-2 text-xs text-rose-100">
                <?= esc($message) ?>
            </div>
        <?php endif; ?>

        <form action="<?= site_url('admin/login') ?>" method="post" class="space-y-4">
            <?= csrf_field() ?>
            <div>
                <label class="block text-xs font-medium text-slate-200 mb-1">
                    Admin Password
                </label>
                <input type="password" name="password" required
                       class="w-full rounded-md border border-slate-600 bg-slate-800/60 px-3 py-2 text-sm text-slate-50 placeholder:text-slate-500 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                <p class="mt-1 text-[11px] text-slate-400">Default: <span class="font-mono">admin123</span> (change in <code>app/Config/Admin.php</code>).</p>
            </div>
            <button type="submit"
                    class="w-full inline-flex justify-center items-center rounded-md bg-emerald-500 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-slate-900">
                Sign in
            </button>
        </form>
    </div>
    <p class="mt-4 text-[11px] text-center text-slate-400">
        Built with CodeIgniter 4 & Tailwind CSS
    </p>
</div>
</body>
</html>


