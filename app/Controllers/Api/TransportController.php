<?php

namespace App\Controllers\Api;

use App\Models\TransportModel;
use CodeIgniter\HTTP\IncomingRequest;

class TransportController extends BaseApiController
{
    protected TransportModel $transportModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->transportModel = new TransportModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Transport records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('transport', 'index', true))
            return $resp;

        try {
            $query = $this->transportModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'student_id', 'transport_status'];
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
            ], 'Transport records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TRANSPORT][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch transport records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Transport record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('transport', 'show', true))
            return $resp;

        try {
            $record = $this->transportModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Transport record not found', 404);
            }

            return $this->respondSuccess($record, 'Transport record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TRANSPORT][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch transport record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Transport record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('transport', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['student_id']) || empty($data['route_number']) || empty($data['bus_number'])) {
                return $this->respondError('student_id, route_number and bus_number are required', 422);
            }

            if (!$this->transportModel->insert($data)) {
                return $this->respondError(
                    $this->transportModel->errors() ?: 'Failed to create transport record',
                    422
                );
            }

            $id = $this->transportModel->getInsertID();
            $record = $this->transportModel->find($id);

            return $this->respondSuccess($record, 'Transport record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[TRANSPORT][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating transport record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Transport record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('transport', 'update'))
            return $resp;

        try {
            $record = $this->transportModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Transport record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->transportModel->update($id, $data)) {
                return $this->respondError(
                    $this->transportModel->errors() ?: 'Failed to update transport record',
                    422
                );
            }

            $updated = $this->transportModel->find($id);

            return $this->respondSuccess($updated, 'Transport record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TRANSPORT][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating transport record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('transport', 'delete'))
            return $resp;

        try {
            $record = $this->transportModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Transport record not found', 404);
            }

            $this->transportModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Transport record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TRANSPORT][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete transport record' . $e->getMessage(), 500);
        }
    }
}
