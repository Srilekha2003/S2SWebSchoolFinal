<?php

namespace App\Controllers\Api;

use App\Models\BranchesModel;
use CodeIgniter\HTTP\IncomingRequest;

class BranchesController extends BaseApiController
{
    protected BranchesModel $branchesModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->branchesModel = new BranchesModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Branches (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('branches', 'index', true))
            return $resp;

        try {
            $query = $this->branchesModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'status'];
            foreach ($filters as $f) {
                $val = $this->input($f);
                if (!empty($val)) {
                    $query->where($f, $val);
                }
            }

            $records = $query->orderBy('id', 'DESC')->findAll();

            return $this->respondSuccess([
                'count' => count($records),
                'data'  => $records
            ], 'Branch records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[BRANCHES][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch branch records ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Branch
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('branches', 'show', true))
            return $resp;

        try {
            $record = $this->branchesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Branch not found', 404);

            return $this->respondSuccess($record, 'Branch record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[BRANCHES][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch branch record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Branch
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('branches', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['branch_name'])) {
                return $this->respondError('school_id and branch_name are required', 422);
            }

            if (!$this->branchesModel->insert($data)) {
                return $this->respondError(
                    $this->branchesModel->errors() ?: 'Failed to create branch',
                    422
                );
            }

            $id = $this->branchesModel->getInsertID();
            $record = $this->branchesModel->find($id);

            return $this->respondSuccess($record, 'Branch created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[BRANCHES][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating branch' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Branch
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('branches', 'update'))
            return $resp;

        try {
            $record = $this->branchesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Branch not found', 404);

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->branchesModel->update($id, $data)) {
                return $this->respondError(
                    $this->branchesModel->errors() ?: 'Failed to update branch',
                    422
                );
            }

            $updated = $this->branchesModel->find($id);

            return $this->respondSuccess($updated, 'Branch updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[BRANCHES][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating branch' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Branch
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('branches', 'delete'))
            return $resp;

        try {
            $record = $this->branchesModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Branch not found', 404);

            // Soft delete
            $this->branchesModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Branch deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[BRANCHES][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete branch' . $e->getMessage(), 500);
        }
    }
}
