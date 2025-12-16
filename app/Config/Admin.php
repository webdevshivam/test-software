<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Admin extends BaseConfig
{
    /**
     * Simple admin password for protecting /admin area.
     * Change this in production.
     */
    public string $password = 'admin123';
}


