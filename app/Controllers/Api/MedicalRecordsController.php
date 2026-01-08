<?php

namespace App\Controllers\Api;

use App\Models\MedicalRecordsModel;
use CodeIgniter\HTTP\IncomingRequest;

class MedicalRecordsController extends BaseApiController
{
    protected MedicalRecordsModel $medicalRecordsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->medicalRecordsModel = new MedicalRecordsModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Medical Records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('medical_records', 'index', true))
            return $resp;

        try {
            $query = $this->medicalRecordsModel->where('deleted_at', null);

            // Optional filters
            $filters = ['school_id', 'branch_id', 'student_id', 'severity'];
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
            ], 'Medical records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[MEDICAL][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch medical records ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Medical Record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('medical_records', 'show', true))
            return $resp;

        try {
            $record = $this->medicalRecordsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Medical record not found', 404);
            }

            return $this->respondSuccess($record, 'Medical record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[MEDICAL][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch medical record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Medical Record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('medical_records', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['student_id']) || empty($data['medical_date']) || empty($data['medical_issues'])) {
                return $this->respondError('student_id, medical_date and medical_issues are required', 422);
            }

            if (!$this->medicalRecordsModel->insert($data)) {
                return $this->respondError(
                    $this->medicalRecordsModel->errors() ?: 'Failed to create medical record',
                    422
                );
            }

            $id = $this->medicalRecordsModel->getInsertID();
            $record = $this->medicalRecordsModel->find($id);

            return $this->respondSuccess($record, 'Medical record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[MEDICAL][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating medical record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Medical Record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('medical_records', 'update'))
            return $resp;

        try {
            $record = $this->medicalRecordsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Medical record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->medicalRecordsModel->update($id, $data)) {
                return $this->respondError(
                    $this->medicalRecordsModel->errors() ?: 'Failed to update medical record',
                    422
                );
            }

            $updated = $this->medicalRecordsModel->find($id);

            return $this->respondSuccess($updated, 'Medical record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[MEDICAL][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating medical record ' . $e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('medical_records', 'delete'))
            return $resp;

        try {
            $record = $this->medicalRecordsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Medical record not found', 404);
            }

            $this->medicalRecordsModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Medical record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[MEDICAL][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete medical record ' . $e->getMessage(), 500);
        }
    }
}
