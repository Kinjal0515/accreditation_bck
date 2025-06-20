<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ContactUsController;
use App\Http\Controllers\DashboardController;

use App\Http\Controllers\EmailTemplateController;

use App\Http\Controllers\MailController;

use App\Http\Controllers\PopUpController;

use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SmsController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WhatsappConfigurationsController;
use App\Http\Controllers\ZoneController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;


Route::get('/opration', function () {
    Artisan::call('storage:link');
    Artisan::call('cache:clear');
    Artisan::call('optimize:clear');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//,'device.info'
Route::middleware(['restrict.ip'])->group(function () {
    // auth routes
    Route::post('verify-user', [AuthController::class, 'verifyUser']);
    Route::post('login', [AuthController::class, 'verifyUserRequest']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('verify-Password', [AuthController::class, 'verifyPassword']);


    Route::get('/getAllData', [DashboardController::class, 'getAllData']);


    Route::post('create-user', [UserController::class, 'create']);
    Route::post('/send-email/{id}', [EmailTemplateController::class, 'send']);

    Route::get('wc-mdl-list', [PopUpController::class, 'index']);

    Route::middleware(['auth:api'])->group(function () {
        Route::get('verify-user-settion', [AuthController::class, 'verifyUserSession']);
        //Dashboard routes
        Route::get('/bookingCount/{id}', [DashboardController::class, 'BookingCounts']);
        Route::get('/calculateSale/{id}', [DashboardController::class, 'calculateSale']);
        Route::get('/getDashboardSummary/{type}', [DashboardController::class, 'getDashboardSummary']);
        Route::get('/payment-log', [DashboardController::class, 'getPaymentLog']);
        Route::delete('/flush-payment-log', [DashboardController::class, 'PaymentLogDelet']);


        Route::post('/create-role', [RolePermissionController::class, 'createRole']);
        Route::get('/role-list', [RolePermissionController::class, 'getRoles']);
        // ;

        Route::get('/role-edit/{id}', [RolePermissionController::class, 'EditRole']);
        Route::post('/role-update', [RolePermissionController::class, 'UpdateRole']);
        // permisson
        Route::post('/create-permission', [RolePermissionController::class, 'createPermission']);
        Route::get('/permission-list', [RolePermissionController::class, 'getPermissions']);
        // });
        Route::get('/permission-edit/{id}', [RolePermissionController::class, 'EditPermission']);
        Route::post('/permission-update', [RolePermissionController::class, 'UpdatePermission']);

        // role permission
        Route::get('/role-permission/{id}', [RolePermissionController::class, 'getRolePermissions']);
        Route::post('/role-permission/{id}', [RolePermissionController::class, 'giveRolePermissions']);
        // role permission
        Route::get('/user-permission/{id}', [RolePermissionController::class, 'getUserPermissions']);
        Route::post('/user-permission/{id}', [RolePermissionController::class, 'giveUserPermissions']);


        //user route
        Route::get('users', [UserController::class, 'index']);
        Route::post('user-approval/{id}', [UserController::class, 'approvalUrl']);
        Route::get('fatch-company/{org_id}', [UserController::class, 'fatchCompany']);
        Route::get('users/list', [UserController::class, 'indexlist']);
        Route::get('users-by-role/{role}', [UserController::class, 'getUsersByRole']);
        Route::delete('user-delete/{id}', [UserController::class, 'destroy']);

        Route::post('chek-email', [UserController::class, 'checkEmail']);
        Route::post('chek-number-email', [UserController::class, 'checkMobile']);

        Route::get('low-credit-users/{id}', [UserController::class, 'lowBalanceUser']);
        Route::get('edit-user/{id}', [UserController::class, 'edit']);
        Route::get('chek-user/{id}', [UserController::class, 'CheckValidUser']);
        Route::post('chek-password', [UserController::class, 'checkPassword']);
        Route::post('update-security', [UserController::class, 'UpdateUserSecurity']);
        Route::post('update-user/{id}', [UserController::class, 'update']);
        Route::post('update-user-alert/{id}', [UserController::class, 'updateAlerts']);
        Route::get('scanner-token-length/{id}', [UserController::class, 'getQrLength']);

        Route::post('create-bulk-user', [UserController::class, 'createBulkUsers']);

        //event-ticket
        Route::get('event-ticket/{event_id}', [UserController::class, 'eventTicket']);

        // passwrord change after login
        Route::post('update-password/{id}', [AuthController::class, 'changePassword']);


        Route::get('/logs', [UserController::class, 'logs']);
        Route::delete('/clear-logs', [UserController::class, 'destroyLogs']);

        //mail templates
        Route::get('/email-templates/{id}', [EmailTemplateController::class, 'index']);
        Route::post('/store-templates', [EmailTemplateController::class, 'store']);
        Route::post('/update-templates', [EmailTemplateController::class, 'update']);



        // alerts route
        Route::get('send-mail', [MailController::class, 'send']);
        Route::get('email-config', [MailController::class, 'index']);
        Route::post('email-config', [MailController::class, 'store']);

        //SMS
        Route::get('/sms-api/{id}', [SmsController::class, 'index']);
        Route::post('/store-api', [SmsController::class, 'DefaultApi']);
        Route::post('/store-custom-api/{id}', [SmsController::class, 'CustomApi']);
        Route::post('/sms-template/{id}', [SmsController::class, 'store']);
        Route::post('/sms-template-update/{id}', [SmsController::class, 'update']);
        Route::post('/send-sms', [SmsController::class, 'sendSms']);

        //CategoryController
        Route::get('/category-list/{user_id}', [CategoryController::class, 'index']);
        Route::get('/category', [CategoryController::class, 'listData']);
        Route::post('/category-store', [CategoryController::class, 'store']);
        Route::post('/category-update/{id}', [CategoryController::class, 'update']);
        Route::get('/category-show/{id}', [CategoryController::class, 'show']);
        Route::delete('/category-destroy/{id}', [CategoryController::class, 'destroy']);

        //ZoneController
        Route::get('/zone-list/{user_id}', [ZoneController::class, 'index']);
        Route::get('/zone', [ZoneController::class, 'listData']);
        Route::post('/zone-store', [ZoneController::class, 'store']);
        Route::post('/zone-update/{id}', [ZoneController::class, 'update']);
        Route::get('/zone-show/{id}', [ZoneController::class, 'show']);
        Route::delete('/zone-destroy/{id}', [ZoneController::class, 'destroy']);
    });


    //ContactUs
    Route::get('contac-list', [ContactUsController::class, 'index']);
    Route::post('contac-store', [ContactUsController::class, 'store']);
    Route::post('contac-update/{id}', [ContactUsController::class, 'update']);
    Route::get('contac-show/{id}', [ContactUsController::class, 'show']);
    Route::delete('contac-destroy/{id}', [ContactUsController::class, 'destroy']);

    // whatsapp configuration dynamic
    Route::get('whatsapp-config-show/{id}', [WhatsappConfigurationsController::class, 'show']);
    Route::post('whatsapp-config-store/{id}', [WhatsappConfigurationsController::class, 'store']);
    Route::delete('whatsapp-config-destroy/{id}', [WhatsappConfigurationsController::class, 'destroy']);
    Route::get('whatsapp-api-show', [WhatsappConfigurationsController::class, 'listData']);
    Route::get('whatsapp-api-show/{id}', [WhatsappConfigurationsController::class, 'list']);
    Route::post('whatsapp-api-store', [WhatsappConfigurationsController::class, 'storeApi']);
    Route::post('whatsapp-api-update/{id}', [WhatsappConfigurationsController::class, 'updateApi']);
    Route::delete('whatsapp-api-destroy/{id}', [WhatsappConfigurationsController::class, 'deleteApi']);
    Route::get('whatsapp-api/{id}/{title}', [WhatsappConfigurationsController::class, 'whatsappData']);
    Route::get('whatsapp-apiData/{title}', [WhatsappConfigurationsController::class, 'whatsappTitleData']);
});


Route::get('/run-command', function () {
    Artisan::call('optimize:clear');
    // Artisan::call('storage:link');
    $output = Artisan::output();

    return $output;
});
