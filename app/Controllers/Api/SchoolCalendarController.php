<?php

namespace App\Controllers\Api;

use App\Models\SchoolCalendarModel;
use CodeIgniter\HTTP\IncomingRequest;

class SchoolCalendarController extends BaseApiController
{
    protected SchoolCalendarModel $schoolCalendarModel;
    protected IncomingRequest $req;

    public function __construct()
    {
        parent::__construct();
        $this->schoolCalendarModel = new SchoolCalendarModel();
        $this->req = service('request');
    }

    public function index()
    {
        if ($resp = $this->checkPermission('school_calendar', 'index', true))
            return $resp;

        try {
            $query = $this->schoolCalendarModel->where('deleted_at', null);

            $filters = ['school_id','branch_id','calendar_type','status'];
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
            ], 'School calendar records fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL_CALENDAR][INDEX] ' . $e->getMessage());
            return $this->respondError('Failed to fetch calendar records' . $e->getMessage(), 500);
        }
    }

    public function show($id = null)
    {
        if ($resp = $this->checkPermission('school_calendar', 'show', true))
            return $resp;

        try {
            $record = $this->schoolCalendarModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Calendar record not found', 404);
            }

            return $this->respondSuccess($record, 'Calendar record fetched successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL_CALENDAR][SHOW] ' . $e->getMessage());
            return $this->respondError('Failed to fetch calendar record' . $e->getMessage(), 500);
        }
    }

    public function create()
    {
        if ($resp = $this->checkPermission('school_calendar', 'create'))
            return $resp;

        try {
            $data = $this->input();

            if (empty($data['school_id']) || empty($data['title'])) {
                return $this->respondError('school_id and title are required', 422);
            }

            if (!$this->schoolCalendarModel->insert($data)) {
                return $this->respondError(
                    $this->schoolCalendarModel->errors() ?: 'Failed to create calendar record',
                    422
                );
            }

            $id = $this->schoolCalendarModel->getInsertID();
            $record = $this->schoolCalendarModel->find($id);

            return $this->respondSuccess($record, 'Calendar record created successfully', 201);

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL_CALENDAR][CREATE] ' . $e->getMessage());
            return $this->respondError('Server error while creating calendar record' . $e->getMessage(), 500);
        }
    }

    public function update($id = null)
    {
        if ($resp = $this->checkPermission('school_calendar', 'update'))
            return $resp;

        try {
            $record = $this->schoolCalendarModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Calendar record not found', 404);
            }

            $data = $this->input();
            $data['updated_at'] = date('Y-m-d H:i:s');

            if (!$this->schoolCalendarModel->update($id, $data)) {
                return $this->respondError(
                    $this->schoolCalendarModel->errors() ?: 'Failed to update calendar record',
                    422
                );
            }

            $updated = $this->schoolCalendarModel->find($id);

            return $this->respondSuccess($updated, 'Calendar record updated successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL_CALENDAR][UPDATE] ' . $e->getMessage());
            return $this->respondError('Server error while updating calendar record' . $e->getMessage(), 500);
        }
    }

    public function delete($id = null)
    {
        if ($resp = $this->checkPermission('school_calendar', 'delete'))
            return $resp;

        try {
            $record = $this->schoolCalendarModel
                ->where('deleted_at', null)
                ->find($id);

            if (!$record) {
                return $this->respondError('Calendar record not found', 404);
            }

            $this->schoolCalendarModel->delete($id);

            return $this->respondSuccess(['id' => $id], 'Calendar record deleted successfully');

        } catch (\Throwable $e) {
            log_message('error', '[SCHOOL_CALENDAR][DELETE] ' . $e->getMessage());
            return $this->respondError('Failed to delete calendar record' . $e->getMessage(), 500);
        }
    }
}
