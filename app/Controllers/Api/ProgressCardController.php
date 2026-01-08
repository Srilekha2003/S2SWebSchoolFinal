<?php

namespace App\Controllers\Api;

use App\Models\ProgressCardModel;
use CodeIgniter\HTTP\IncomingRequest;

class ProgressCardController extends BaseApiController
{
    protected ProgressCardModel $progressCardModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->progressCardModel = new ProgressCardModel();
        $this->req = service('request'); // ✔ Same as StudentsController
    }

    // -------------------------------------------------------
    // 📋 GET: List all Progress Cards (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('progress_cards', 'index', true))
            return $resp;

        try {
            $query = $this->progressCardModel->where('deleted_at', null);

            // Optional Filters
            $filters = [
                'school_id',
                'branch_id',
                'student_id',
                'exam_id',
                'class_id',
                'result_status'
            ];

            foreach ($filters as $f) {
                $val = $this->input($f);
                if (!empty($val)) $query->where($f, $val);
            }

            $records = $query->orderBy('id', 'DESC')->findAll();

            return $this->respondSuccess([
                'count' => count($records),
                'data'  => $records
            ], 'Progress cards fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[PROGRESS_CARD][INDEX] '.$e->getMessage());
            return $this->respondError('Failed to fetch progress cards '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Progress Card
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('progress_cards','show', true))
            return $resp;

        try {
            $record = $this->progressCardModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Progress card not found', 404);

            return $this->respondSuccess($record, 'Progress card fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[PROGRESS_CARD][SHOW] '.$e->getMessage());
            return $this->respondError('Failed to fetch progress card '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Progress Card
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('progress_cards','create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['student_id']) || empty($data['exam_id'])) {
                return $this->respondError('student_id and exam_id are required', 422);
            }

            // ✔ Teacher Signature Upload
            $teacherFile = $this->req->getFile('teacher_signature');
            if ($teacherFile && $teacherFile->isValid()) {

                $path = uploadFile($teacherFile, 'progress_cards/teachers', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid teacher signature file', 422);

                $data['teacher_signature'] = $path;
            }

            // ✔ Principal Signature Upload
            $principalFile = $this->req->getFile('principal_signature');
            if ($principalFile && $principalFile->isValid()) {

                $path = uploadFile($principalFile, 'progress_cards/principals', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid principal signature file', 422);

                $data['principal_signature'] = $path;
            }

            if (!$this->progressCardModel->insert($data)) {
                return $this->respondError(
                    $this->progressCardModel->errors() ?: 'Failed to create progress card',
                    422
                );
            }

            $id = $this->progressCardModel->getInsertID();
            $record = $this->progressCardModel->find($id);

            return $this->respondSuccess($record, 'Progress card created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[PROGRESS_CARD][CREATE] '.$e->getMessage());
            return $this->respondError('Server error while creating progress card '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Progress Card
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('progress_cards','update'))
            return $resp;

        try {
            $record = $this->progressCardModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Progress card not found', 404);

            $data = $this->input();

            // ✔ Teacher Signature Upload
            $teacherFile = $this->req->getFile('teacher_signature');
            if ($teacherFile && $teacherFile->isValid()) {

                $path = uploadFile($teacherFile, 'progress_cards/teachers', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid teacher signature file', 422);

                if (!empty($record['teacher_signature'])) {
                    $old = realpath(FCPATH).$record['teacher_signature'];
                    if (is_file($old)) @unlink($old);
                }

                $data['teacher_signature'] = $path;
            }

            // ✔ Principal Signature Upload
            $principalFile = $this->req->getFile('principal_signature');
            if ($principalFile && $principalFile->isValid()) {

                $path = uploadFile($principalFile, 'progress_cards/principals', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid principal signature file', 422);

                if (!empty($record['principal_signature'])) {
                    $old = realpath(FCPATH).$record['principal_signature'];
                    if (is_file($old)) @unlink($old);
                }

                $data['principal_signature'] = $path;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->progressCardModel->update($id, $data)) {
                return $this->respondError(
                    $this->progressCardModel->errors() ?: 'Failed to update progress card',
                    422
                );
            }

            $updated = $this->progressCardModel->find($id);

            return $this->respondSuccess($updated, 'Progress card updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[PROGRESS_CARD][UPDATE] '.$e->getMessage());
            return $this->respondError('Server error while updating progress card '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('progress_cards','delete'))
            return $resp;

        try {
            $record = $this->progressCardModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Progress card not found', 404);

            // delete old files
            foreach (['teacher_signature','principal_signature'] as $field) {
                if (!empty($record[$field])) {
                    $old = realpath(FCPATH).$record[$field];
                    if (is_file($old)) @unlink($old);
                }
            }

            $this->progressCardModel->delete($id);

            return $this->respondSuccess(['id'=>$id], 'Progress card deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[PROGRESS_CARD][DELETE] '.$e->getMessage());
            return $this->respondError('Failed to delete progress card '.$e->getMessage(), 500);
        }
    }
}
