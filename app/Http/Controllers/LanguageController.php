<?php

namespace App\Http\Controllers;

use App\Models\Language;
use App\Services\BootstrapTableService;
use App\Services\FileService;
use App\Services\ResponseService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Throwable;

class LanguageController extends Controller {

    private string $uploadFolder;

    public function __construct() {
        $this->uploadFolder = "language";
    }

    public function index() {
        $language = Language::where('code', 'en')->first();
        Session::save();
        Session::put('language', $language);
        return view('settings.language');
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'code'       => 'required',
            'rtl'        => 'nullable',
            'app_file'   => 'required|mimetypes:application/json',
            'panel_file' => 'required|mimetypes:application/json',
            'image'      => 'required|mimes:jpeg,png,jpg,svg|max:2048',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            $language = new Language();
            $language->name = $request->name;
            $language->code = $request->code;
            $language->rtl = $request->rtl == "on";

            if ($request->hasFile('app_file')) {
                $language->app_file = FileService::uploadLanguageFile($request->file('app_file'), $request->code . "_app");
            }

            if ($request->hasFile('panel_file')) {
                $language->panel_file = FileService::uploadLanguageFile($request->file('panel_file'), $request->code);
            }

            if ($request->hasFile('image')) {
                $language->image = FileService::upload($request->file('image'), $this->uploadFolder);
            }
            $language->save();
            ResponseService::successResponse('Language Successfully Added');
        } catch (Throwable $th) {
            ResponseService::logErrorRedirect($th, "Language Controller -> Store");
            ResponseService::errorResponse(route('language.index'), 'Something Went Wrong');
        }
    }

    public function show(Request $request) {

        $offset = $request->offset ?? 0;
        $limit = $request->limit ?? 10;
        $sort = $request->sort ?? 'id';
        $order = $request->order ?? 'DESC';

        $sql = Language::orderBy($sort, $order);

        if (!empty($_GET['search'])) {
            $search = $_GET['search'];
            $sql->where('id', 'LIKE', "%$search%")->orwhere('code', 'LIKE', "%$search%")->orwhere('name', 'LIKE', "%$search%");
        }
        $total = $sql->count();
        $sql->skip($offset)->take($limit);
        $result = $sql->get();
        $bulkData = array();
        $bulkData['total'] = $total;
        $rows = array();
        foreach ($result as $key => $row) {
            $tempRow = $row->toArray();
            $tempRow['rtl'] = ($row->rtl == 1) ? "Yes" : "No";
            $operate = '';
            if ($row->code != "en") {
                $operate .= BootstrapTableService::editButton(route('language.update', $row->id), true);
                $operate .= BootstrapTableService::deleteButton(route('language.destroy', $row->id));
            }
            $tempRow['operate'] = $operate;
            $rows[] = $tempRow;
        }

        $bulkData['rows'] = $rows;
        return response()->json($bulkData);
    }

    public function update(Request $request, $id) {
        $validator = Validator::make($request->all(), [
            'name'       => 'required',
            'code'       => 'required',
            'rtl'        => 'required|boolean',
            'app_file'   => 'nullable|mimes:json',
            'panel_file' => 'nullable|mimes:json',
            'image'      => 'nullable|mimes:jpeg,png,jpg,svg|max:2048',
        ]);
        if ($validator->fails()) {
            ResponseService::validationError($validator->errors()->first());
        }
        try {
            $language = Language::findOrFail($id);
            $data = [
                ...$request->all()
            ];
            if ($request->hasFile('app_file')) {
                $data['app_file'] = FileService::uploadLanguageFile($request->file('app_file'), $language->code . "_app");
            }
            if ($request->hasFile('panel_file')) {
                $data['panel_file'] = FileService::uploadLanguageFile($request->file('panel_file'), $language->code);
            }

            if ($request->hasFile('image')) {
                $data['image'] = FileService::replace($request->file('image'), $this->uploadFolder, $language->getRawOriginal('image'));
            }
            $language->update($data);
            ResponseService::successResponse('Language Updated successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorResponse($th, "Language Controller --> update");
            ResponseService::errorResponse(route('language.index'), 'Something Went Wrong');
        }
    }

    public function destroy($id) {
        try {
//            if (!has_permissions('delete', 'property')) {
//                return redirect()->back()->with('error', PERMISSION_ERROR_MSG);
//            }
            $language = Language::findOrFail($id);
            $language->delete();

            FileService::deleteLanguageFile($language->app_file . "_app");
            FileService::deleteLanguageFile($language->panel_file);
            FileService::delete($language->getRawOriginal('image'));

            ResponseService::successResponse('Language Deleted successfully');
        } catch (Throwable $th) {
            ResponseService::logErrorRedirect($th, "Language Controller --> Destroy");
            ResponseService::errorResponse('Something Went Wrong');
        }
    }

    public function setLanguage($languageCode) {
        $language = Language::where('code', $languageCode)->first();
        if (!empty($language)) {
            Session::put('locale', $language->code);
            Session::put('language', (object)$language->toArray());
            Session::save();
            app()->setLocale($language->code);
        }
        return redirect()->back();
    }

    public function downloadPanelFile() {
        return Response::download(base_path("resources/lang/en.json"), 'panel.json', [
            'Content-Type' => 'application/json',
        ]);
    }

    public function downloadAppFile() {
        return Response::download(base_path("resources/lang/en_app.json"), 'app.json', [
            'Content-Type' => 'application/json',
        ]);
    }
}
