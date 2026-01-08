<?php

namespace App\Controllers\Api;

use App\Models\FeeManagementModel;
use CodeIgniter\HTTP\IncomingRequest;

class FeeManagementController extends BaseApiController
{
    protected FeeManagementModel $feeManagementModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->feeManagementModel = new FeeManagementModel();
        $this->req = service('request');
    }

    // -------------------------------------------------------
    // 📋 GET: List all Fee records (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('fee_management', 'index', true))
            return $resp;

        try {
            $query = $this->feeManagementModel->where('deleted_at', null);

            // Optional filters
            $filters = [
                'school_id',
                'branch_id',
                'academic_year',
                'class_id',
                'student_id',
                'payment_status'
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
            ], 'Fee records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[FEE_MANAGEMENT][INDEX] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch fee records ' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Fee record
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('fee_management', 'show', true))
            return $resp;

        try {
            $record = $this->feeManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Fee record not found', 404);
            }

            return $this->respondSuccess(
                $record,
                'Fee record fetched successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[FEE_MANAGEMENT][SHOW] ' . $e->getMessage());
            return $this->respondError(
                'Failed to fetch fee record ' . $e->getMessage(),
                500
            );
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Fee record (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('fee_management', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['academic_year']) || empty($data['fee_type']) || empty($data['amount_due'])) {
                return $this->respondError(
                    'academic_year, fee_type and amount_due are required',
                    422
                );
            }

            if (!$this->feeManagementModel->insert($data)) {
                return $this->respondError(
                    $this->feeManagementModel->errors()
                        ?: 'Failed to create fee record',
                    422
                );
            }

            $id = $this->feeManagementModel->getInsertID();
            $record = $this->feeManagementModel->find($id);

            return $this->respondSuccess(
                $record,  'Fee record created successfully',201
            );

        } catch (\Throwable $e) {
            log_message('error', '[FEE_MANAGEMENT][CREATE] ' . $e->getMessage());
            return $this->respondError(
                'Server error while creating fee record ' . $e->getMessage(),500
            );
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Fee record (Restricted)
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('fee_management', 'update'))
            return $resp;

        try {
            $record = $this->feeManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Fee record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->feeManagementModel->update($id, $data)) {
                return $this->respondError(
                    $this->feeManagementModel->errors()   ?: 'Failed to update fee record',  422
                );
            }

            $updated = $this->feeManagementModel->find($id);

            return $this->respondSuccess(
                $updated, 'Fee record updated successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[FEE_MANAGEMENT][UPDATE] ' . $e->getMessage());
            return $this->respondError( 'Server error while updating fee record ' . $e->getMessage(), 500
            );
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete (Restricted)
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('fee_management', 'delete'))
            return $resp;

        try {
            $record = $this->feeManagementModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Fee record not found', 404);
            }

            // Use CI4 soft delete
            $this->feeManagementModel->delete($id);

            return $this->respondSuccess(  ['id' => $id], 'Fee record deleted successfully'
            );

        } catch (\Throwable $e) {
            log_message('error', '[FEE_MANAGEMENT][DELETE] ' . $e->getMessage());
            return $this->respondError(  'Failed to delete fee record ' . $e->getMessage(), 500
            );
        }
    }
}
