<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Slider;
use App\Services\BootstrapTableService;
use App\Services\FileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class SliderController extends Controller {

    private string $uploadFolder;

    public function __construct() {
        $this->uploadFolder = 'sliders';
    }

    public function index() {
        ResponseService::noAnyPermissionThenRedirect(['slider-list', 'slider-create', 'slider-update', 'slider-delete']);
        $slider = Slider::select(['id', 'image', 'sequence'])->orderBy('sequence', 'ASC')->get();
        $items = Item::all();
        return view('slider.index', compact('slider', 'items'));
    }

    public function store(Request $request) {
        ResponseService::noPermissionThenRedirect('slider-create');
        $validator = Validator::make($request->all(), [
            'image.*' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }

        try {
            $lastSequence = Slider::max('sequence');
            $nextSequence = $lastSequence + 1;
            Slider::create([
                'image'            => $request->hasFile('image') ? FileService::compressAndUpload($request->file('image'), $this->uploadFolder) : '',
                'item_id'          => $request->item ?? '',
                'third_party_link' => $request->link ?? '',
                'sequence'         => $nextSequence
            ]);
            ResponseService::successResponse('Slider created successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Slider Controller -> store");
            ResponseService::errorResponse();
        }
    }

    public function destroy($id) {
        ResponseService::noPermissionThenRedirect('slider-delete');
        try {
            $slider = Slider::find($id);
            if ($slider) {
                $url = $slider->image;
                $relativePath = parse_url($url, PHP_URL_PATH);
                if (Storage::disk('public')->exists($relativePath)) {
                    Storage::disk('public')->delete($relativePath);
                }
                $slider->delete();
                ResponseService::successResponse('slider delete successfully');
            }

        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Slider Controller -> destroy");
            ResponseService::errorResponse('something is wrong !!!');
        }
    }

    public function show(Request $request) {
        ResponseService::noPermissionThenRedirect('slider-list');
        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';
        $sql = Slider::with('item')->sort($sort, $order);
        if (!empty($request->search)) {
            $sql = $sql->search($request->search);
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        foreach ($result as $key => $row) {
            $tempRow = $row->toArray();
            $operate = '';
            if (Auth::user()->can('slider-delete')) {
                $operate .= BootstrapTableService::deleteButton(route('slider.destroy', $row->id));
            }
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }
}
