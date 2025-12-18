<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Frontend
$routes->get('/', 'Registration::index');
$routes->get('register', 'Registration::index');
$routes->post('register', 'Registration::store');

$routes->get('status', 'PlayerStatus::index');
$routes->post('status/check', 'PlayerStatus::check');

// Admin (simple password-protected via filter)
$routes->group('admin', ['filter' => 'adminauth'], static function (RouteCollection $routes) {
    $routes->get('/', 'Admin\Dashboard::index');

    // Trials management
    $routes->get('trials', 'Admin\Trials::index');
    $routes->get('trials/create', 'Admin\Trials::create');
    $routes->post('trials/store', 'Admin\Trials::store');
    $routes->get('trials/edit/(:num)', 'Admin\Trials::edit/$1');
    $routes->post('trials/update/(:num)', 'Admin\Trials::update/$1');
    $routes->get('trials/delete/(:num)', 'Admin\Trials::delete/$1');

    // Players management
    $routes->get('players', 'Admin\Players::index');
    $routes->post('players/bulk-delete', 'Admin\Players::bulkDelete');
    $routes->post('players/update-payment/(:num)', 'Admin\Players::updatePayment/$1');
    $routes->get('players/export/csv', 'Admin\Players::exportCsv');
    $routes->get('players/export/excel', 'Admin\Players::exportExcel');
    $routes->get('players/export/pdf', 'Admin\Players::exportPdf');

    // Attendance management
    $routes->get('attendance', 'Admin\Attendance::index');
    $routes->get('attendance/past', 'Admin\Attendance::past');
    $routes->get('attendance/manage/(:num)', 'Admin\Attendance::manage/$1');
    $routes->post('attendance/save/(:num)', 'Admin\Attendance::save/$1');
    $routes->get('attendance/on-spot/(:num)', 'Admin\Attendance::onSpotForm/$1');
    $routes->post('attendance/on-spot/(:num)', 'Admin\Attendance::onSpotStore/$1');
    $routes->get('attendance/export/(:num)', 'Admin\Attendance::export/$1');
    $routes->get('attendance/summary/(:num)', 'Admin\AttendanceSummary::index/$1');
    $routes->get('attendance/summary/(:num)/export/csv', 'Admin\AttendanceSummary::exportCsv/$1');
    $routes->get('attendance/summary/(:num)/export/pdf', 'Admin\AttendanceSummary::exportPdf/$1');

    // Payments reporting
    $routes->get('payments', 'Admin\Payments::index');
    $routes->get('payments/export', 'Admin\Payments::export');
});

// Admin login/logout (no DB users, simple password)
$routes->get('admin/login', 'Admin\Auth::login');
$routes->post('admin/login', 'Admin\Auth::attempt');
$routes->get('admin/logout', 'Admin\Auth::logout');

