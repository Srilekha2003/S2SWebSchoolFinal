<?php

namespace App\Controllers\Api;

use App\Models\HostelAllocationsModel;
use CodeIgniter\HTTP\IncomingRequest;

class HostelAllocationsController extends BaseApiController
{
    protected HostelAllocationsModel $hostelAllocationsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->hostelAllocationsModel = new HostelAllocationsModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Hostel Allocations (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('hostel_allocations', 'index', true))
            return $resp;

        try {
            $query = $this->hostelAllocationsModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'room_id', 'student_id', 'allocation_status'];
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
            ], 'Hostel allocations fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ALLOCATIONS][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch hostel allocations ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Hostel Allocation
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('hostel_allocations', 'show', true))
            return $resp;

        try {
            $record = $this->hostelAllocationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel allocation not found', 404);
            }

            return $this->respondSuccess($record, 'Hostel allocation fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ALLOCATIONS][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch hostel allocation ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Hostel Allocation (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('hostel_allocations', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['room_id']) || empty($data['student_id'])) {
                return $this->respondError('school_id, room_id and student_id are required', 422);
            }

            if (!$this->hostelAllocationsModel->insert($data)) {
                return $this->respondError(
                    $this->hostelAllocationsModel->errors() ?: 'Failed to create hostel allocation',
                    422
                );
            }

            $id = $this->hostelAllocationsModel->getInsertID();
            $record = $this->hostelAllocationsModel->find($id);

            return $this->respondSuccess($record, 'Hostel allocation created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ALLOCATIONS][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating hostel allocation ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Hostel Allocation (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('hostel_allocations', 'update'))
            return $resp;

        try {
            $record = $this->hostelAllocationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel allocation not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->hostelAllocationsModel->update($id, $data)) {
                return $this->respondError(
                    $this->hostelAllocationsModel->errors() ?: 'Failed to update hostel allocation',
                    422
                );
            }

            $updated = $this->hostelAllocationsModel->find($id);

            return $this->respondSuccess($updated, 'Hostel allocation updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ALLOCATIONS][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating hostel allocation ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('hostel_allocations', 'delete'))
            return $resp;

        try {
            $record = $this->hostelAllocationsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel allocation not found', 404);
            }

            $this->hostelAllocationsModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Hostel allocation deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ALLOCATIONS][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete hostel allocation ' . $e->getMessage(), 500);
        }
    }
}
