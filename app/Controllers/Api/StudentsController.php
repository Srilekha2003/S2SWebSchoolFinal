<?php

namespace App\Controllers\Api;

use App\Models\StudentsModel;
use CodeIgniter\HTTP\IncomingRequest;

class StudentsController extends BaseApiController
{
    protected StudentsModel $studentsModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->studentsModel = new StudentsModel();
        $this->req = service('request');  // ✔ Always works for getFile()
    }

    // -------------------------------------------------------
    // 📋 GET: List all Students (Public Allowed)
    // -------------------------------------------------------
    public function index()
    {
        if ($resp = $this->checkPermission('students', 'index', true))
            return $resp;

        try {
            $query = $this->studentsModel->where('deleted_at', null);

            // Optional Filters
            $filters = ['school_id','branch_id','class_id','status','rejoined'];
            foreach ($filters as $f) {
                $val = $this->input($f);
                if (!empty($val)) $query->where($f, $val);
            }

            $records = $query->orderBy('id', 'DESC')->findAll();

            return $this->respondSuccess([
                'count' => count($records),
                'data'  => $records
            ], 'Students fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENTS][INDEX] '.$e->getMessage());
            return $this->respondError('Failed to fetch students '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // 📋 GET: Single Student
    // -------------------------------------------------------
    public function show($id = null)
    {
        if ($resp = $this->checkPermission('students','show', true))
            return $resp;

        try {
            $record = $this->studentsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Student not found', 404);

            return $this->respondSuccess($record, 'Student fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENTS][SHOW] '.$e->getMessage());
            return $this->respondError('Failed to fetch student '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ➕ POST: Create Student (Restricted)
    // -------------------------------------------------------
    public function create()
    {
        if ($resp = $this->checkPermission('students','create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['first_name'])) {
                return $this->respondError('First name is required', 422);
            }

            // ✔ File Upload (Profile Photo)
            $file = $this->req->getFile('profile_photo');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();

                $path = uploadFile($file, 'students', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid file or upload failed', 422);

                $data['profile_photo'] = $path;

            }

            if (!$this->studentsModel->insert($data)) {
                return $this->respondError(
                    $this->studentsModel->errors() ?: 'Failed to create student',
                    422
                );
            }

            $id = $this->studentsModel->getInsertID();
            $record = $this->studentsModel->find($id);

            return $this->respondSuccess($record, 'Student created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[STUDENTS][CREATE] '.$e->getMessage());
            return $this->respondError('Server error while creating student '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ✏️ PUT: Update Student
    // -------------------------------------------------------
    public function update($id = null)
    {
        if ($resp = $this->checkPermission('students','update'))
            return $resp;

        try {
            $record = $this->studentsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record){
                return $this->respondError('Student not found', 404);
            }

            $data = $this->input();

            // ✔ File Upload Only if Provided
            $file = $this->req->getFile('profile_photo');
            if ($file && $file->isValid()) {

                $mime = $file->getMimeType();

                $path = uploadFile($file, 'students', [
                    'jpg','jpeg','png','webp'
                ]);

                if (!$path)
                    return $this->respondError('Invalid file or upload failed', 422);

                // delete old file
                if (!empty($record['profile_photo'])) {
                    $publicPath = realpath(FCPATH);
                    $old = $publicPath.$record['profile_photo'];
                    if (is_file($old)) @unlink($old);
                }

                $data['profile_photo'] = $path;
            }

            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->studentsModel->update($id, $data)) {
                return $this->respondError(
                    $this->studentsModel->errors() ?: 'Failed to update student',
                    422
                );
            }

            $updated = $this->studentsModel->find($id);

            return $this->respondSuccess($updated, 'Student updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENTS][UPDATE] '.$e->getMessage());
            return $this->respondError('Server error while updating student '.$e->getMessage(), 500);
        }
    }

    // -------------------------------------------------------
    // ❌ DELETE: Soft Delete
    // -------------------------------------------------------
    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('students','delete'))
            return $resp;

        try {
            $record = $this->studentsModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record)
                return $this->respondError('Student not found', 404);

            // delete old file
            if (!empty($record['profile_photo'])) {
                $publicPath = realpath(FCPATH);
                $old = $publicPath.$record['profile_photo'];
                if (is_file($old)) @unlink($old);
            }

            $this->studentsModel->delete($id);

            return $this->respondSuccess(['id'=>$id], 'Student deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[STUDENTS][DELETE] '.$e->getMessage());
            return $this->respondError('Failed to delete student '.$e->getMessage(), 500);
        }
    }
}

