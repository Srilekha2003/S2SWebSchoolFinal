<?php

namespace App\Controllers\Api;

use App\Models\AttendanceModel;

class AttendanceController extends BaseApiController
{
    protected AttendanceModel $attendanceModel;

    public function __construct()
    {
        parent::__construct();
        $this->attendanceModel = new AttendanceModel();
    }

    // -------------------------------------------------------
    // 📋 GET: List all Attendance records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('attendance', 'index', true))
            return $resp;

        try {
            $query = $this->attendanceModel->where('deleted_at', null);

            // Optional filters
            $filters = [
                'school_id',
                'branch_id',
                'class_id',
                'date',
                'student_id',
                'status'
            ];

            foreach ($filters as $f) {
                $val = $this->input($f);
                if ($val !== null && $val !== '') {
                    $query->where($f, $val);
                }
            }

            $records = $query->orderBy('id', 'DESC')->findAll();

            return $this->respondSuccess([
                'count' => count($records),
                'data'  => $records
            ], 'Attendance records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ATTENDANCE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch attendance records', 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Attendance record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('attendance', 'show', true))
            return $resp;

        try {
            $record = $this->attendanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Attendance record not found', 404);
            }

            return $this->respondSuccess($record, 'Attendance record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ATTENDANCE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch attendance record', 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Attendance record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('attendance', 'create'))
            return $resp;

        try {
            $data = $this->input();

            // Required fields check
            if (empty($data['date']) || empty($data['student_id'])) {
                return $this->respondError('date and student_id are required', 422);
            }

            if (!$this->attendanceModel->insert($data)) {
                return $this->respondError(
                    $this->attendanceModel->errors() ?: 'Failed to create attendance record',
                    422
                );
            }

            $id = $this->attendanceModel->getInsertID();
            $record = $this->attendanceModel->find($id);

            return $this->respondSuccess($record, 'Attendance record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[ATTENDANCE][CREATE] ' . $e->getMessage());
           return $this->respondError(
    'Server error while creating attendance record: ' . $e->getMessage(),
    500
);

        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Attendance record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('attendance', 'update'))
            return $resp;

        try {
            $record = $this->attendanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Attendance record not found', 404);
            }

            $data = $this->input();

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->attendanceModel->update($id, $data)) {
                return $this->respondError(
                    $this->attendanceModel->errors() ?: 'Failed to update attendance record',
                    422
                );
            }

            $updated = $this->attendanceModel->find($id);

            return $this->respondSuccess($updated, 'Attendance record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ATTENDANCE][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating attendance record', 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete Attendance (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('attendance', 'delete'))
            return $resp;

        try {
            $record = $this->attendanceModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Attendance record not found', 404);
            }

            $this->attendanceModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Attendance record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[ATTENDANCE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete attendance record', 500);
        }
    }
}
