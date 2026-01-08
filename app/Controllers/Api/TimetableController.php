<?php

namespace App\Controllers\Api;

use App\Models\TimetableModel;
use CodeIgniter\HTTP\IncomingRequest;

class TimetableController extends BaseApiController
{
    protected TimetableModel $timetableModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->timetableModel = new TimetableModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Timetable records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('timetable', 'index', true))
            return $resp;

        try {
            $query = $this->timetableModel->where('deleted_at', null);

            // Optional filters
            $filters = [
                'school_id',
                'branch_id',
                'class_id',
                'day_of_week',
                'status'
            ];

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
            ], 'Timetable records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TIMETABLE][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch timetable records' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Timetable record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('timetable', 'show', true))
            return $resp;

        try {
            $record = $this->timetableModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Timetable record not found', 404);
            }

            return $this->respondSuccess($record, 'Timetable record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TIMETABLE][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch timetable record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Timetable record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('timetable', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (
                empty($data['day_of_week']) ||
                empty($data['period_number']) ||
                empty($data['subject'])
            ) {
                return $this->respondError(
                    'day_of_week, period_number and subject are required',
                    422
                );
            }

            if (!$this->timetableModel->insert($data)) {
                return $this->respondError(
                    $this->timetableModel->errors() ?: 'Failed to create timetable record',
                    422
                );
            }

            $id = $this->timetableModel->getInsertID();
            $record = $this->timetableModel->find($id);

            return $this->respondSuccess($record, 'Timetable record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[TIMETABLE][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating timetable record' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Timetable record
    // -------------------------------------------------------
   public function update($id = null)
    {
        if ($resp = $this->checkPermission('timetable', 'update'))
            return $resp;

        try {
            $record = $this->timetableModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Timetable not found', 404);
            }

            // ✔ Get input data
            $data = $this->input();

            /**
             * ✔ Optional File Upload Block
             * (kept only for consistency with Students update)
             */
            $file = $this->req->getFile('attachment');
            if ($file && $file->isValid()) {

                $path = uploadFile($file, 'timetable', [
                    'jpg','jpeg','png','webp','pdf'
                ]);

                if (!$path)
                    return $this->respondError('Invalid file or upload failed', 422);

                // delete old file if exists
                if (!empty($record['attachment'])) {
                    $publicPath = realpath(FCPATH);
                    $old = $publicPath . $record['attachment'];
                    if (is_file($old)) @unlink($old);
                }

                $data['attachment'] = $path;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->timetableModel->update($id, $data)) {
                return $this->respondError(
                    $this->timetableModel->errors() ?: 'Failed to update timetable',
                    422
                );
            }

            $updated = $this->timetableModel->find($id);

            return $this->respondSuccess($updated, 'Timetable updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TIMETABLE][UPDATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while updating timetable ' . $e->getMessage(),
                500
            );
            }
    }


    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('timetable', 'delete'))
            return $resp;

        try {
            $record = $this->timetableModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Timetable record not found', 404);
            }

            $this->timetableModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Timetable record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[TIMETABLE][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete timetable record' . $e->getMessage(), 500);
        }
    }
}
