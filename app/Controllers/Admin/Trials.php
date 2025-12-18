<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\TrialService;

class Trials extends BaseController
{
    private TrialService $trialService;

    public function __construct()
    {
        $this->trialService = service('trialService');
    }

    public function index()
    {
        $trials = $this->trialService->getAll();

        return view('admin/trials/index', [
            'title'  => 'Manage Trials',
            'trials' => $trials,
        ]);
    }

    public function create()
    {
        return view('admin/trials/form', [
            'title'      => 'Create Trial',
            'validation' => \Config\Services::validation(),
            'trial'      => null,
        ]);
    }

    public function store()
    {
        $rules = [
            'name'   => 'required|min_length[3]|max_length[150]',
            'city'   => 'required|max_length[100]',
            'state'  => 'required|max_length[100]',
            'venue'  => 'required|max_length[255]',
            'trial_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors below.');
        }

        $data  = [
            'name'           => $this->request->getPost('name'),
            'city'           => $this->request->getPost('city'),
            'state'          => $this->request->getPost('state'),
            'venue'          => $this->request->getPost('venue'),
            'trial_date'     => $this->request->getPost('trial_date'),
            'reporting_time' => $this->request->getPost('reporting_time'),
            'status'         => $this->request->getPost('status'),
        ];

        $this->trialService->create($data);

        return redirect()->to('/admin/trials')->with('message', 'Trial created successfully.');
    }

    public function edit(int $id)
    {
        $trial = $this->trialService->findActive($id);

        if ($trial === null) {
            return redirect()->to('/admin/trials')->with('message', 'Trial not found.');
        }

        return view('admin/trials/form', [
            'title'      => 'Edit Trial',
            'validation' => \Config\Services::validation(),
            'trial'      => $trial,
        ]);
    }

    public function update(int $id)
    {
        $rules = [
            'name'   => 'required|min_length[3]|max_length[150]',
            'city'   => 'required|max_length[100]',
            'state'  => 'required|max_length[100]',
            'venue'  => 'required|max_length[255]',
            'trial_date' => 'required|valid_date[Y-m-d]',
            'status' => 'required|in_list[active,inactive]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Please correct the errors below.');
        }

        $data  = [
            'name'           => $this->request->getPost('name'),
            'city'           => $this->request->getPost('city'),
            'state'          => $this->request->getPost('state'),
            'venue'          => $this->request->getPost('venue'),
            'trial_date'     => $this->request->getPost('trial_date'),
            'reporting_time' => $this->request->getPost('reporting_time'),
            'status'         => $this->request->getPost('status'),
        ];

        $this->trialService->update($id, $data);

        return redirect()->to('/admin/trials')->with('message', 'Trial updated successfully.');
    }

    public function delete(int $id)
    {
        $this->trialService->delete($id);

        return redirect()->to('/admin/trials')->with('message', 'Trial deleted.');
    }
}


