<?php

namespace Config;

use CodeIgniter\Config\BaseService;
use App\Services\DashboardService;
use App\Services\TrialService;

/**
 * Services Configuration file.
 *
 * Services are simply other classes/libraries that the system uses
 * to do its job. This is used by CodeIgniter to allow the core of the
 * framework to be swapped out easily without affecting the usage within
 * the rest of your application.
 *
 * This file holds any application-specific services, or service overrides
 * that you might need. An example has been included with the general
 * method format you should use for your service methods. For more examples,
 * see the core Services file at system/Config/Services.php.
 */
class Services extends BaseService
{
    public static function dashboardService(bool $getShared = true): DashboardService
    {
        if ($getShared) {
            return static::getSharedInstance('dashboardService');
        }

        return new DashboardService(
            new \App\Models\PlayerModel(),
            new \App\Models\TrialModel(),
            new \App\Models\PaymentModel()
        );
    }

    public static function trialService(bool $getShared = true): TrialService
    {
        if ($getShared) {
            return static::getSharedInstance('trialService');
        }

        return new TrialService(
            new \App\Models\TrialModel()
        );
    }
}
