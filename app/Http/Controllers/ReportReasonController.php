<?php

namespace App\Http\Controllers;

use App\Models\ReportReason;
use App\Models\UserReports;
use App\Services\BootstrapTableService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Throwable;

class ReportReasonController extends Controller {

    public function index() {
        ResponseService::noAnyPermissionThenRedirect(['report-reason-list', 'report-reason-create', 'report-reason-update', 'report-reason-delete']);
        return view('reports.index');
    }

    public function store(Request $request) {
        ResponseService::noPermissionThenSendJson('report-reason-create');
        $validator = Validator::make($request->all(), [
            'reason' => 'required'
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            ReportReason::create($request->all());
            ResponseService::successResponse('Reason Successfully Added');

        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "ReportReason Controller -> store");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }

    public function show(Request $request) {
        ResponseService::noPermissionThenSendJson('report-reason-list');
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';
        $sql = ReportReason::orderBy($sort, $order);
        if (!empty($request->search)) {
            $sql = $sql->search($request->search);
        }

        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        $no = 1;
        foreach ($result as $key => $row) {
            $tempRow = $row->toArray();
            $tempRow['no'] = $no++;
            $operate = '';
            if (Auth::user()->can('report-reason-update')) {
                $operate .= BootstrapTableService::editButton(route('report-reasons.update', $row->id), true, null, null, $row->id);
            }

            if (Auth::user()->can('report-reason-delete')) {
                $operate .= BootstrapTableService::deleteButton(route('report-reasons.destroy', $row->id));
            }
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }


    public function update(Request $request, $id) {
        try {
            ResponseService::noPermissionThenSendJson('report-reason-update');
            ReportReason::findOrFail($id)->update($request->all());
            ResponseService::successResponse('Reason Successfully Updated');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "ReportReason Controller -> store");
            ResponseService::errorResponse('Something Went Wrong');
        }

    }

    public function destroy($id) {
        try {
            ResponseService::noPermissionThenSendJson('report-reason-delete');
            ReportReason::findOrFail($id)->delete();
            ResponseService::successResponse('Reason Deleted Successfully');
        } catch (Throwable) {
            ResponseService::errorResponse('Something Went Wrong');
        }
    }

    public function usersReports() {
        ResponseService::noPermissionThenRedirect('user-reports-list');
        return view('reports.user_reports');
    }

    public function userReportsShow(Request $request) {
        ResponseService::noPermissionThenRedirect('user-reports-list');
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';
        $sql = UserReports::with('user:id,name', 'report_reason:id,reason', 'item:id,name')->sort($sort, $order);

        if (!empty($request->search)) {
            $sql = $sql->search($request->search);
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $res = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();

        foreach ($res as $row) {
            $rows[] = $row->toArray();
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
