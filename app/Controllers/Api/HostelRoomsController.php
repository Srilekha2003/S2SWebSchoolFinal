<?php

namespace App\Controllers\Api;

use App\Models\HostelRoomsModel;
use CodeIgniter\HTTP\IncomingRequest;

class HostelRoomsController extends BaseApiController
{
    protected HostelRoomsModel $hostelRoomsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->hostelRoomsModel = new HostelRoomsModel();
        $this->req = service('request');   // ✔ Always works
    }

    // -------------------------------------------------------
    // 📋 GET: List all Hostel Rooms (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('hostel_rooms', 'index', true))
            return $resp;

        try {
            $query = $this->hostelRoomsModel->where('deleted_at', null);

            // Optional filters (SAME STRUCTURE)
            $filters = ['school_id', 'branch_id', 'availability', 'room_type'];
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
            ], 'Hostel rooms fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ROOMS][INDEX] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch hostel rooms' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Hostel Room
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('hostel_rooms', 'show', true))
            return $resp;

        try {
            $record = $this->hostelRoomsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel room not found', 404);
            }

            return $this->respondSuccess(
                $record,
                'Hostel room fetched successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ROOMS][SHOW] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch hostel room' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Hostel Room (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('hostel_rooms', 'create'))
            return $resp;

        try {
            $data = $this->input();

            // SAME REQUIRED CHECK STYLE
            if (empty($data['school_id']) || empty($data['room_number'])) {
                return $this->respondError(
                    'school_id and room_number are required',
                    422
                );
            }

            if (!$this->hostelRoomsModel->insert($data)) {
                return $this->respondError(
                    $this->hostelRoomsModel->errors()
                        ?: 'Failed to create hostel room',
                    422
                );
            }

            $id = $this->hostelRoomsModel->getInsertID();
            $record = $this->hostelRoomsModel->find($id);

            return $this->respondSuccess(
                $record,
                'Hostel room created successfully',
                201
            );

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ROOMS][CREATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while creating hostel room' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Hostel Room (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('hostel_rooms', 'update'))
            return $resp;

        try {
            $record = $this->hostelRoomsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel room not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->hostelRoomsModel->update($id, $data)) {
                return $this->respondError(
                    $this->hostelRoomsModel->errors()
                        ?: 'Failed to update hostel room',
                    422
                );
            }

            $updated = $this->hostelRoomsModel->find($id);

            return $this->respondSuccess(
                $updated,
                'Hostel room updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ROOMS][UPDATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while updating hostel room' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('hostel_rooms', 'delete'))
            return $resp;

        try {
            $record = $this->hostelRoomsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Hostel room not found', 404);
            }

            // SAME SOFT DELETE LOGIC
            $this->hostelRoomsModel->delete($id);

            return $this->respondSuccess(
                ['id' => $id],
                'Hostel room deleted successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[HOSTEL_ROOMS][DELETE] ' . $e->getMessage());
            return $this->respondError(
                'Failed to delete hostel room' . $e->getMessage(),
                500
            );
        }
    }
}
