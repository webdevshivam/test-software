<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TrialModel;

class Trials extends BaseController
{
    public function index()
    {
        $model  = new TrialModel();
        $trials = $model->where('deleted_at', null)->orderBy('trial_date', 'DESC')->findAll();

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

        $model = new TrialModel();
        $data  = [
            'name'           => $this->request->getPost('name'),
            'city'           => $this->request->getPost('city'),
            'state'          => $this->request->getPost('state'),
            'venue'          => $this->request->getPost('venue'),
            'trial_date'     => $this->request->getPost('trial_date'),
            'reporting_time' => $this->request->getPost('reporting_time'),
            'status'         => $this->request->getPost('status'),
        ];

        $model->insert($data);

        return redirect()->to('/admin/trials')->with('message', 'Trial created successfully.');
    }

    public function edit(int $id)
    {
        $model = new TrialModel();
        $trial = $model->find($id);

        if (! $trial || $trial['deleted_at'] !== null) {
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

        $model = new TrialModel();
        $data  = [
            'name'           => $this->request->getPost('name'),
            'city'           => $this->request->getPost('city'),
            'state'          => $this->request->getPost('state'),
            'venue'          => $this->request->getPost('venue'),
            'trial_date'     => $this->request->getPost('trial_date'),
            'reporting_time' => $this->request->getPost('reporting_time'),
            'status'         => $this->request->getPost('status'),
        ];

        $model->update($id, $data);

        return redirect()->to('/admin/trials')->with('message', 'Trial updated successfully.');
    }

    public function delete(int $id)
    {
        $model = new TrialModel();
        $model->delete($id);

        return redirect()->to('/admin/trials')->with('message', 'Trial deleted.');
    }
}


