<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\DashboardService;

class Dashboard extends BaseController
{
    private DashboardService $dashboardService;

    public function __construct()
    {
        $this->dashboardService = service('dashboardService');
    }

    public function index()
    {
        $stats = $this->dashboardService->getSummaryStats();

        return view('admin/dashboard', array_merge([
            'title' => 'Admin Dashboard',
        ], $stats));
    }
}


