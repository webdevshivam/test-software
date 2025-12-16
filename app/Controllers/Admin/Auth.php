<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Admin as AdminConfig;

class Auth extends BaseController
{
    public function login()
    {
        $session = session();

        if ($session->get('is_admin_logged_in')) {
            return redirect()->to('/admin');
        }

        return view('layouts/admin_auth', [
            'title'   => 'Admin Login',
            'errors'  => session('errors'),
            'message' => session('message'),
        ]);
    }

    public function attempt()
    {
        $session = session();
        $config  = config(AdminConfig::class);

        $password = (string) ($this->request->getPost('password') ?? '');

        if ($password === $config->password) {
            $session->set('is_admin_logged_in', true);

            return redirect()->to('/admin');
        }

        return redirect()->back()->with('message', 'Invalid admin password.');
    }

    public function logout()
    {
        $session = session();
        $session->remove('is_admin_logged_in');

        return redirect()->to('/admin/login')->with('message', 'Logged out successfully.');
    }
}


