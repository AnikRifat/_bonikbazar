<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\CustomersController;
use App\Http\Controllers\CustomFieldController;
use App\Http\Controllers\FeatureSectionController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallerController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PackageController;
use App\Http\Controllers\ReportReasonController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SliderController;
use App\Http\Controllers\StaffController;
use App\Http\Controllers\SystemUpdateController;
use App\Http\Controllers\WebhookController;
use App\Models\Setting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Auth::routes();
Route::get('/', static function () {
    if (Auth::user()) {
        return redirect('/home');
    }
    return view('auth.login');
});

Route::get('page/privacy-policy', static function () {
    $privacy_policy = Setting::where('name', 'privacy_policy')->firstOrFail();
    echo htmlspecialchars_decode($privacy_policy->value);
})->name('public.privacy-policy');

Route::get('page/terms-conditions', static function () {
    $terms_conditions = Setting::where('name', 'terms_conditions')->firstOrFail();
    echo htmlspecialchars_decode($terms_conditions->value);
})->name('public.terms-conditions');


Route::group(['prefix' => 'webhook'], static function () {
    Route::post('/stripe', [WebhookController::class, 'stripe']);
});

/* Non-Authenticated Common Functions */
Route::group(['prefix' => 'common'], static function () {
    Route::get('/js/lang.js', [Controller::class, 'readLanguageFile'])->name('common.language.read');
});
Route::group(['prefix' => 'install'], static function () {
    Route::get('purchase-code', [InstallerController::class, 'purchaseCodeIndex'])->name('install.purchase-code.index');
    Route::post('purchase-code', [InstallerController::class, 'checkPurchaseCode'])->name('install.purchase-code.post');
});

Route::group(['middleware' => ['auth', 'language']], static function () {
    /*** Authenticated Common Functions ***/
    Route::group(['prefix' => 'common'], static function () {
        Route::put('/change-row-order', [Controller::class, 'changeRowOrder'])->name('common.row-order.change');
        Route::put('/change-status', [Controller::class, 'changeStatus'])->name('common.status.change');
    });


    /*** Home Module : START ***/
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    Route::get('change-password', [HomeController::class, 'changePasswordIndex'])->name('change-password.index');
    Route::post('change-password', [HomeController::class, 'changePasswordUpdate'])->name('change-password.update');

    Route::get('change-profile', [HomeController::class, 'changeProfileIndex'])->name('change-profile.index');
    Route::post('change-profile', [HomeController::class, 'changeProfileUpdate'])->name('change-profile.update');
    /*** Home Module : END ***/

    /*** Category Module : START ***/
    Route::resource('category', CategoryController::class);
    Route::group(['prefix' => 'category'], static function () {
        Route::get('/{id}/subcategories', [CategoryController::class, 'getSubCategories'])->name('category.subcategories');
        Route::get('/{id}/custom-fields', [CategoryController::class, 'customFields'])->name('category.custom-fields');
        Route::get('/{id}/custom-fields/show', [CategoryController::class, 'getCategoryCustomFields'])->name('category.custom-fields.show');
        Route::delete('/{id}/custom-fields/{customFieldID}/delete', [CategoryController::class, 'destroyCategoryCustomField'])->name('category.custom-fields.destroy');
    });
    /*** Category Module : END ***/

    /*** Custom Field Module : START ***/
    Route::group(['prefix' => 'custom-fields'], static function () {
        Route::post('/{id}/value/add', [CustomFieldController::class, 'addCustomFieldValue'])->name('custom-fields.value.add');
        Route::get('/{id}/value/show', [CustomFieldController::class, 'getCustomFieldValues'])->name('custom-fields.value.show');
        Route::put('/{id}/value/edit', [CustomFieldController::class, 'updateCustomFieldValue'])->name('custom-fields.value.update');
        Route::delete('/{id}/value/{value}/delete', [CustomFieldController::class, 'deleteCustomFieldValue'])->name('custom-fields.value.delete');
    });
    Route::resource('custom-fields', CustomFieldController::class);
    /*** Custom Field Module : END ***/

    /*** Item Module : START ***/
    Route::group(['prefix' => 'item'], static function () {
        Route::put('/{id}/approval', [ItemController::class, 'updateItemApproval'])->name('item.approval');
    });
    Route::resource('item', ItemController::class);
    /*** Item Module : END ***/

    /*** Setting Module : START ***/
    Route::group(['prefix' => 'settings'], static function () {
        Route::get('/', [SettingController::class, 'index'])->name('settings.index');
        Route::post('/store', [SettingController::class, 'store'])->name('settings.store');

        Route::get('system', [SettingController::class, 'page'])->name('settings.system');
        Route::get('about-us', [SettingController::class, 'page'])->name('settings.about-us.index');
        Route::get('privacy-policy', [SettingController::class, 'page'])->name('settings.privacy-policy.index');
        Route::get('terms-conditions', [SettingController::class, 'page'])->name('settings.terms-conditions.index');

        Route::get('firebase', [SettingController::class, 'page'])->name('settings.firebase.index');
        Route::post('firebase/update', [SettingController::class, 'updateFirebaseSettings'])->name('settings.firebase.update');

        Route::get('payment-gateway', [SettingController::class, 'paymentSettingsIndex'])->name('settings.payment-gateway.index');
        Route::post('payment-gateway', [SettingController::class, 'paymentSettingsStore'])->name('settings.payment-gateway.store');
        Route::get('language', [SettingController::class, 'page'])->name('settings.language.index');
    });

    Route::group(['prefix' => 'system-update'], static function () {
        Route::get('/', [SystemUpdateController::class, 'index'])->name('system-update.index');
        Route::post('/', [SystemUpdateController::class, 'update'])->name('system-update.update');
    });
    /*** Setting Module : END ***/

    /*** Language Module : START ***/
    Route::group(['prefix' => 'language'], static function () {
        Route::get('set-language/{lang}', [LanguageController::class, 'setLanguage'])->name('language.set-current');
        Route::get('download/panel', [LanguageController::class, 'downloadPanelFile'])->name('language.download.panel.json');
        Route::get('download/app', [LanguageController::class, 'downloadAppFile'])->name('language.download.app.json');
    });
    Route::resource('language', LanguageController::class);
    /*** Language Module : END ***/

    /*** User Module : START ***/
    Route::group(['prefix' => 'staff'], static function () {
        Route::put('/{id}/change-password', [StaffController::class, 'changePassword'])->name('staff.change-password');
    });
    Route::resource('staff', StaffController::class);

    Route::get('updateFCMID', [StaffController::class, 'updateFCMID']);
    /*** User Module : END ***/

    /*** Customer Module : START ***/
    Route::resource('customer', CustomersController::class);
    /*** Customer Module : END ***/


    /*** Slider Module : START ***/
    Route::resource('slider', SliderController::class);
    /*** Slider Module : END ***/

    /*** Package Module : STARTS ***/
    Route::group(['prefix' => 'package'], static function () {
        Route::get('/advertisement', [PackageController::class, 'advertisementIndex'])->name('package.advertisement.index');
        Route::get('/advertisement/show', [PackageController::class, 'advertisementShow'])->name('package.advertisement.show');
        Route::post('/advertisement/store', [PackageController::class, 'advertisementStore'])->name('package.advertisement.store');
        Route::put('/advertisement/{id}/update', [PackageController::class, 'advertisementUpdate'])->name('package.advertisement.update');
        Route::get('/users/', [PackageController::class, 'userPackagesIndex'])->name('package.users.index');
        Route::get('/users/show', [PackageController::class, 'userPackagesShow'])->name('package.users.show');
        Route::get('/payment-transactions/', [PackageController::class, 'paymentTransactionIndex'])->name('package.payment-transactions.index');
        Route::get('/payment-transactions/show', [PackageController::class, 'paymentTransactionShow'])->name('package.payment-transactions.show');
    });
    Route::resource('package', PackageController::class);
    /*** Package Module : ENDS ***/


    /*** Report Reason Module : START ***/
    Route::group(['prefix' => 'report-reasons'], static function () {
        Route::get('/user-report', [ReportReasonController::class, 'usersReports'])->name('report-reasons.user-reports.index');
        Route::get('/user-report/show', [ReportReasonController::class, 'userReportsShow'])->name('report-reasons.user-reports.show');
    });
    Route::resource('report-reasons', ReportReasonController::class);
    /*** Report Reason Module : END ***/


    /*** Notification Module : START ***/
    Route::group(['prefix' => 'notification'], static function () {
        Route::delete('/batch-delete', [NotificationController::class, 'batchDelete'])->name('notification.batch.delete');
    });
    Route::resource('notification', NotificationController::class);
    /*** Notification Module : END ***/


    /*** Feature Section Module : START ***/
    Route::resource('feature-section', FeatureSectionController::class);
    /*** Feature Section Module : END ***/

    Route::get("/roles-list", [RoleController::class, 'list'])->name('roles.list');
    Route::resource('roles', RoleController::class);
});
Route::get('/migrate', static function () {
    Artisan::call('migrate');
    return redirect()->back();
});

//Route::get('/seeder', static function () {
//    Artisan::call('db:seed');
//    return redirect()->back();
//});

Route::get('clear', static function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
    return redirect()->back();
});

Route::get('storage-link', static function () {
    Artisan::call('storage:link');
});

