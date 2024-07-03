<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ItemCustomFieldValue;
use App\Models\Notifications;
use App\Models\User;
use App\Services\BootstrapTableService;
use App\Services\NotificationService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ItemController extends Controller {

    public function index() {
        ResponseService::noAnyPermissionThenRedirect(['item-list', 'item-update']);
        return view('items.index');
    }

    public function show(Request $request) {
        ResponseService::noPermissionThenSendJson('item-list');
        $offset = $request->input('offset', 0);
        $limit = $request->input('limit', 10);
        $sort = $request->input('sort', 'sequence');
        $order = $request->input('order', 'ASC');

        $sql = Item::with(['custom_fields', 'category:id,name', 'user:id,name', 'gallery_images'])->withTrashed();
        if (!empty($request->search)) {
            $sql = $sql->search($request->search);
        }

        $total = $sql->count();
        $sql = $sql->sort($sort, $order)->skip($offset)->take($limit);
        $result = $sql->get();

        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();

        $itemCustomFieldValues = ItemCustomFieldValue::whereIn('item_id', $result->pluck('id'))->get();
        foreach ($result as $key => $row) {
            /* Merged ItemCustomFieldValue's data to main data */
            $itemCustomFieldValue = $itemCustomFieldValues->filter(function ($data) use ($row) {
                return $data->item_id == $row->id;
            });

            $row->custom_fields = collect($row->custom_fields)->map(function ($customField) use ($itemCustomFieldValue) {
                $customField['value'] = $itemCustomFieldValue->first(function ($data) use ($customField) {
                    return $data->custom_field_id == $customField->id;
                });

                if ($customField->type == "fileinput" && !empty($customField['value']->value)) {
                    $customField['value']->value = url(Storage::url($customField['value']->value));
                }
                return $customField;
            });
            $tempRow = $row->toArray();
            $operate = '';
            if (count($row->custom_fields) > 0 && Auth::user()->can('item-update')) {
                $operate .= BootstrapTableService::button('fa fa-eye', '#', ['editdata', 'btn-light-danger  '], ['title' => __("View"), "data-bs-target" => "#editModal", "data-bs-toggle" => "modal",]);
            }

            if (Auth::user()->can('custom-field-delete')) {
                $operate .= BootstrapTableService::editButton(route('item.approval', $row->id), true, '#editStatusModal', 'edit-status', $row->id);
            }
            $tempRow['active_status'] = empty($row->deleted_at);//IF deleted_at is empty then status is true else false
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }
        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

//    public function updateItemStatus(Request $request) {
//        $validator = Validator::make($request->all(), [
//            'id'     => 'required|integer',
//            'status' => 'required',
//        ]);
//        if ($validator->fails()) {
//            ResponseService::validationError($validator->errors()->first());
//        }
//        try {
//            Item::where('id', $request->id)->update(['active' => $request->status]);
//            $item = Item::with('user')->find($request->id);
//
//            // TODO : this send notification code can be extracted to a function
//            if (!empty($item->user) && $item->user->status == 1) {
//                $fcmID = User::where('id', $item->user->id)->select(['id', 'fcm_id'])->get()->pluck('fcm_id');
//                $msg = "";
//                if (count($fcmID) !== 0) {
//                    $msg = $request->status ? 'Activate now by Administrator ' : 'Deactive now by Administrator ';
//                    $registrationIDs = $fcmID;
//                    $fcmMsg = array(
//                        'title'        => 'About ' . $item->name,
//                        'message'      => $msg,
//                        'type'         => 'Item_Status',
//                        'body'         => $msg,
//                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
//                        'sound'        => 'default',
//                        'id'           => $request->id,
//                    );
//                    send_push_notification($registrationIDs, $fcmMsg);
//                }
//
//                Notifications::create([
//                    'title'       => 'Item Updated',
//                    'message'     => 'Your Item Post ' . $msg,
//                    'image'       => '',
//                    'type'        => '1',
//                    'send_type'   => '0',
//                    'app_user_id' => $item->user->id,
//                    'item_id'     => $item->id
//                ]);
//            }
//            ResponseService::successResponse('successfully updated');
//        } catch (Throwable) {
//            ResponseService::errorResponse('something went wrong ');
//        }
//    }

    public function updateItemApproval(Request $request, $id) {
        try {
            ResponseService::noPermissionThenSendJson('item-update');
            $item = Item::with('user')->withTrashed()->findOrFail($id)->update(['status' => $request->status]);
            if (!empty($item->user) && $item->user->status == 1) {
                $fcm_ids = array();
                //TODO : this whole send notification function can be extracted to one service class function
                $user_token = User::where('id', $item->user->id)->select(['fcm_id'])->get()->pluck('fcm_id')->toArray();
                $fcm_ids[] = $user_token;
                $msg = "";
                if (!empty($fcm_ids)) {
                    $msg = ucfirst($request->status) . ' by Administrator ';
                    $registrationIDs = $fcm_ids[0];
                    NotificationService::sendFcmNotification($registrationIDs, 'About ' . $item->name, $msg, ['id' => $request->id,]);
                }

                Notifications::create([
                    'title'       => 'Item Updated',
                    'message'     => 'Your Item Post ' . $msg,
                    'image'       => '',
                    'type'        => '1',
                    'send_type'   => '0',
                    'app_user_id' => $item->user->id,
                    'item_id'     => $item->id
                ]);
            }
            ResponseService::successResponse('Item Status Updated Successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, 'ItemController ->updateItemApproval');
            ResponseService::errorResponse('Something Went Wrong');
        }
    }
}
