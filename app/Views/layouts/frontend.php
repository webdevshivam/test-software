<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'Trial Management') ?></title>
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
<header class="bg-slate-900 text-white">
    <div class="max-w-5xl mx-auto flex items-center justify-between px-4 py-3">
        <div class="flex items-center space-x-2">
            <div class="h-9 w-9 rounded-full bg-emerald-500 flex items-center justify-center text-white font-semibold">
                TM
            </div>
            <div>
                <div class="font-semibold tracking-tight">Trial Management</div>
                <div class="text-xs text-slate-300">Cricket Player Registration</div>
            </div>
        </div>
        <nav class="hidden sm:flex items-center space-x-4 text-sm">
            <a href="<?= site_url('register') ?>" class="hover:text-emerald-400">Register</a>
            <a href="<?= site_url('status') ?>" class="hover:text-emerald-400">Check Status</a>
        </nav>
    </div>
</header>

<main class="py-10">
    <div class="max-w-5xl mx-auto px-4">
        <?= $this->renderSection('content') ?>
    </div>
</main>

<footer class="border-t border-slate-200 mt-10">
    <div class="max-w-5xl mx-auto px-4 py-4 text-xs text-slate-500 flex items-center justify-between">
        <span>© <?= date('Y') ?> Trial Management</span>
        <span>Built with CodeIgniter 4 & Tailwind CSS</span>
    </div>
</footer>
</body>
</html>


