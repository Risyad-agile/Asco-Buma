<?php 
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DashboardHoldingController;
use App\Http\Controllers\DashboardCompanyController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompaniesController;
use App\Http\Controllers\ConnectorController;
use App\Http\Controllers\ConnectorTargetController;
use App\Http\Controllers\ConnectorSourceController; 
use App\Http\Controllers\ConnectorJobController;
use App\Http\Controllers\AccountDataLoadController;
use App\Http\Controllers\SyncronizeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\AccountStyleController;
use App\Http\Controllers\CustomFactorController;
use App\Http\Controllers\ProcessDataLogController;
use App\Http\Controllers\TasksController;
use App\Http\Controllers\JobController;

use App\Http\Controllers\ProductsController;
use App\Http\Controllers\StoresController;


use App\Http\Controllers\PaymentsController;
use App\Http\Controllers\PromotionsController;
use App\Http\Controllers\ReceivesController;
use App\Http\Controllers\ReturnsController;
use App\Http\Controllers\MutationInsController;
use App\Http\Controllers\MutationOutsController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\OfficialMemosController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\EVoucherController;
use App\Http\Controllers\MemberTypesController;
use App\Http\Controllers\AgilePayController;
use App\Http\Controllers\ExpensesController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReportOfficeController;
use App\Http\Controllers\ReportStoreController;
use App\Http\Controllers\GajaPaymentsController;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () { 
    return redirect('/login');
});
Auth::routes(['verify' => true]);
Route::get('/home', [HomeController::class, 'index'])->name('home');


#--Akses oleh semua user
Route::get('companies/list',[CompaniesController::class,'companyList'])->name('companies.list');
Route::get('/export-excel', [TasksController::class, 'exportExcel']);

Route::post('/sync-from-appclient', [TasksController::class, 'sync']); 

Route::group(['prefix' => 'holding', 'middleware' => ['role:superadmin|holding']], function() {
    Route::get('holding/access/index',[DashboardHoldingController::class,'holdingDashboardIndex'])->name('holding.index'); 
    
});


Route::group(['prefix' => 'asri-core', 'middleware' => ['role:superadmin']], function() {
    
    // COMPANIES 
    Route::get('companies', [CompaniesController::class, 'browse'])->name('companies.browse');
    Route::get('companies/list', [CompaniesController::class, 'list'])->name('companies.list'); // untuk DevExtreme grid AJAX
    
    Route::get('companies/create', [CompaniesController::class, 'create'])->name('companies.create');
    Route::post('companies', [CompaniesController::class, 'store'])->name('companies.store');
    
    Route::get('companies/update/{id}', [CompaniesController::class, 'edit'])->name('companies.edit'); // buka halaman update.blade.php
    Route::post('companies/update/{id}', [CompaniesController::class, 'update'])->name('companies.update');

    // ORGANIZATION 
    Route::get('/organizations', [OrganizationController::class, 'index'])->name('organizations.index');
    Route::get('/organizations/list', [OrganizationController::class, 'list'])->name('organizations.list');

    Route::get('/organizations/create', [OrganizationController::class, 'create'])->name('organizations.create');
    Route::post('/organizations/store', [OrganizationController::class, 'store'])->name('organizations.store');

    Route::get('/organizations/edit/{id}', [OrganizationController::class, 'edit'])->name('organizations.edit');
    Route::post('/organizations/update/{id}', [OrganizationController::class, 'update'])->name('organizations.update');

    Route::post('/organizations/register-envizi', [OrganizationController::class, 'registerEnvizi'])
        ->name('organizations.register.envizi'); 

    Route::get('/companies/combo', [OrganizationController::class, 'combo'])->name('companies.combo');    

    
    
    // connector target 
    Route::get('connector-target/browse', [ConnectorTargetController::class, 'browse'])->name('connector_target.browse'); 
    Route::get('connector-target/create', [ConnectorTargetController::class, 'create'])->name('connector_target.create');
    Route::post('connector-target/store', [ConnectorTargetController::class, 'store'])->name('connector_target.store');
    Route::get('connector-target/edit/{id}', [ConnectorTargetController::class, 'edit'])->name('connector_target.edit');
    Route::post('connector-target/update/{id}', [ConnectorTargetController::class, 'update'])->name('connector_target.update'); 
    Route::post('connector-target/set-status', [ConnectorTargetController::class, 'setStatus'])->name('connector_target.setStatus');
    Route::get('connector-target/config/{id}', [ConnectorTargetController::class, 'getConfig'])->name('connector_target.getConfig'); 

    // connector source
    Route::get('connector-source/browse',[ConnectorSourceController::class,'browse'])->name('connector_source.browse');
    Route::get('connector-source/create', [ConnectorSourceController::class, 'create'])->name('connector_source.create');
    Route::post('connector-source/store', [ConnectorSourceController::class, 'store'])->name('connector_source.store');
    Route::get('connector-source/edit/{id}', [ConnectorSourceController::class, 'edit'])->name('connector_source.edit');
    Route::post('connector-source/update', [ConnectorSourceController::class, 'update'])->name('connector_source.update');
    Route::post('connector-source/setStatus', [ConnectorSourceController::class, 'setStatus'])->name('connector_source.setStatus');
    Route::post('connector-source/test-api', [ConnectorSourceController::class, 'testAPI'])->name('connector_source.testAPI'); 

    // connector jobs browse & change status
    Route::get('connector-job/browse', [ConnectorJobController::class, 'browse'])->name('connector_job.browse');
    Route::post('connector-job/setStatus', [ConnectorJobController::class, 'setStatus'])->name('connector_job.setStatus');
    // connector jobs create & store
    Route::get('connector-job/create', [ConnectorJobController::class, 'create'])->name('connector_job.create');
    Route::post('connector-job/store', [ConnectorJobController::class, 'store'])->name('connector_job.store');
    // connector jobs edit & update
    Route::get('connector-job/edit/{id}', [ConnectorJobController::class, 'edit'])->name('connector_job.edit');
    Route::post('connector-job/update/{id}', [ConnectorJobController::class, 'update'])->name('connector_job.update');
    
    // Fetch & Push API Connection 
    Route::post('connector-job/run-fetch', [ConnectorJobController::class, 'runFetchJob'])->name('connector_job.runFetch');
    Route::post('connector-job/run-push', [ConnectorJobController::class, 'runPushJob'])->name('connector_job.runPush');

});
Route::group(['prefix' => 'asri-core', 'middleware' => ['role:superadmin|manager|supervisor|user|admin']], function() 
{
    // LOCATION 
    Route::get('/location/browse', [LocationController::class, 'browse'])->name('location.browse');
    Route::get('/location/list', [LocationController::class, 'list'])->name('location.list');
    Route::get('/location/load', [LocationController::class, 'create'])->name('location.create');
    Route::post('/location/store', [LocationController::class, 'store'])->name('location.store');

    Route::get('/location/partner', [LocationController::class, 'partnerLocationList'])->name('location.partner');
    Route::get('/location/partner/load', [LocationController::class, 'partnerLocationLoad'])->name('location.partner.load');
    Route::get('/location/combo-org', [LocationController::class, 'comboOrg'])->name('location.combo_org');
});

Route::group(['prefix' => 'asri-core', 'middleware' => ['role:superadmin|agile-manager']], function() {
    Route::get('home/sa/dashboard/index',[HomeController::class,'saDashboardIndex'])->name('dashboard.sa.index');
    Route::get('home/sa/dashboard/merchant',[HomeController::class,'saDashboardMerchantSales'])->name('dashboard.sa.merchant');
    Route::get('home/dashboard/index',[DashboardController::class,'index'])->name('dashboard.manager.index');

    Route::post('connector/list/open',[ConnectorController::class,'listOpen'])->name('connector.list.open');
    Route::post('connector/main',[ConnectorController::class,'mainOpen'])->name('connector.main');
    Route::post('connector/new/protocol',[ConnectorController::class,'newProtocolOpen'])->name('connector.new.protocol');
    Route::get('connector/list/open/{compid}',[ConnectorController::class,'listOpenByCompanyId'])->name('connector.list.open.company');

    Route::post('users/main',[UserController::class,'userMain'])->name('users.main');
    Route::put('users/password/reset/{id}',[UserController::class,'userResetPassword'])->name('users.password.reset');

    Route::get('users/api/token/index',[UserController::class,'userAPITokenIndex'])->name('users.api.token.index');
    Route::post('users/api/token/main',[UserController::class,'userAPITokenMain'])->name('users.api.token.main');
    Route::post('users/api/token/send/email',[UserController::class,'userAPITokenSendEmail'])->name('users.api.token.send.email');

    
    Route::get('users/registration/list',[UserController::class,'userRegistrationList'])->name('users.registration.list');
    Route::post('users/registration/accepted',[UserController::class,'userRegistrationAccepted'])->name('users.registration.accepted');

    Route::get('users/right/index',[UserController::class,'userrightindex'])->name("userright.index");
    Route::get('users/right/role/load',[UserController::class,'roleload'])->name('userright.role.load');
    Route::post('users/right/store',[UserController::class,'userrightstore'])->name('userright.store');
    Route::get('user/pwdreset/index',[UserController::class,'userpwdresetindex'])->name('userpwd.resetindex');

    Route::get('companies/token/index',[CompaniesController::class,'companyTokenIndex'])->name('companies.token.index');
    Route::get('companies/envizi/upload/config/index',[CompaniesController::class,'companyEnviziConfigIndex'])->name('companies.envizi.upload.config.index');
    
    Route::post('organization/list/open',[OrganizationController::class,'listOpen'])->name('organization.list.open');
    Route::post('location/list/open',[LocationController::class,'listOpen'])->name('location.list.open');
    
    Route::post('account/task/list/open',[AccountDataLoadController::class,'listTaskOpen'])->name('account.task.list.open');

    

    Route::get('accountstyles/company/load',[AccountStyleController::class,'companyAccountStyleLoad'])->name('accountstyles.company.load');
    Route::get('accountstyles/company/index',[AccountStyleController::class,'companyAccountStyleIndex'])->name('accountstyles.company.index');
    Route::post('accountstyles/company/list/open',[AccountStyleController::class,'companyAccountStyleListOpen'])->name('accountstyles.company.list.open');
    Route::get('accountstyles/import/file/upload',[AccountStyleController::class,'importFileUpload'])->name('accountstyles.import.file.upload');
    Route::post('accountstyles/import/file/process',[AccountStyleController::class,'importFileProcess'])->name('accountstyles.import.file.process');
    Route::post('accountstyles/import/open/list',[AccountStyleController::class,'importOpenList'])->name('accountstyles.import.open.list');
    Route::post('accountstyles/import/save',[AccountStyleController::class,'importSave'])->name('accountstyles.import.save');
    Route::post('accountstyles/sync',[AccountStyleController::class,'syncAccountStyle'])->name('accountstyles.sync');
    

    Route::post('syncronize/list/open',[SyncronizeController::class,'listOpen'])->name('syncronize.list.open');
    Route::post('syncronize/process/organization',[SyncronizeController::class,'sincronizeOrganization'])->name('syncronize.process.organization');
    Route::post('syncronize/process/location/account',[SyncronizeController::class,'sincronizeLocationAccount'])->name('syncronize.process.location.account');

    Route::post('processdatalog/list/open',[ProcessDataLogController::class,'listOpen'])->name('processdatalog.list.open');
    Route::post('processdatalog/main',[ProcessDataLogController::class,'main'])->name('processdatalog.main');
    Route::get('processdatalog/list/open',[ProcessDataLogController::class,'errorindex'])->name('processdatalog.error.index');
    Route::post('processdatalog/error/list/open',[ProcessDataLogController::class,'errorlistOpen'])->name('processdatalog.error.list.open');

    Route::post('job/list/open',[JobController::class,'listOpen'])->name('job.list.open');
    Route::post('job/main',[JobController::class,'main'])->name('job.main');
    Route::post('job/execute',[JobController::class,'executeJob'])->name('job.execute');
    Route::get('job/list/open/{compid}',[JobController::class,'listOpenByCompanyId'])->name('job.list.open.company');

    Route::post('task/list/open',[TasksController::class,'listOpen'])->name('task.list.open'); 

    Route::resource('users',UserController::class);
    // Route::resource('companies', CompaniesController::class); --> pindah ke role superadmin 
    Route::resource('connector', ConnectorController::class);
    Route::resource('syncronize', SyncronizeController::class);
    // Route::resource('organization', OrganizationController::class); //--> pindah ke role superadmin dulu 
    // Route::resource('location', LocationController::class); ---> pindah ke role superadmin dulu
    Route::resource('accountstyle', AccountStyleController::class);
    Route::resource('processdatalog', ProcessDataLogController::class);
    Route::resource('job', JobController::class);
    Route::resource('task', TasksController::class);
    Route::resource('account', AccountDataLoadController::class);
});



#untuk keperluan akses dari customer/client asri
Route::group(['prefix' => 'partner', 'middleware' => ['role:superadmin|manager|supervisor|user|admin']], function() 
{
    Route::get('company/open',[CompaniesController::class,'partnerCompanyOpen'])->name('partner.company.open');
    Route::put('company/{id}',[CompaniesController::class,'partnerCompanyUpdate'])->name('partner.company.update');

    Route::get('organization/update',[OrganizationController::class,'partnerOrganizationUpdate'])->name('partner.organization.update');
    Route::post('organization/store',[OrganizationController::class,'store'])->name('partner.organization.store');

    Route::get('location/load',[LocationController::class,'partnerLocationLoad'])->name('partner.location.load');
    Route::get('location/list',[LocationController::class,'partnerLocationList'])->name('partner.location.list');
    Route::post('location/store',[LocationController::class,'store'])->name('partner.location.store');

    Route::get('accountstyles/shared/index',[AccountStyleController::class,'sharedAccountStyleIndex'])->name('accountstyles.shared.index');
    Route::get('accountstyles/shared/load',[AccountStyleController::class,'sharedAccountStyleLoad'])->name('accountstyles.shared.load');
    Route::get('accountstyles/company/index',[AccountStyleController::class,'partnerCompanyAccountStyleListOpen'])->name('partner.accountstyles.company.index');
    
    Route::get('connector/list',[ConnectorController::class,'partnerConnectorList'])->name('partner.connector.list');
    Route::post('connector/main',[ConnectorController::class,'partnerConnectorMainOpen'])->name('partner.connector.main');
    Route::post('connector/new/protocol',[ConnectorController::class,'partnerConnectorProtocolOpen'])->name('partner.connector.new.protocol');
    Route::get('connector/list/open/{compid}',[ConnectorController::class,'partnerConnectorListByCompany'])->name('partner.connector.list.open.company');
    Route::put('connector/{id}',[ConnectorController::class,'update'])->name('partner.connector.update');
    Route::post('connector/store',[ConnectorController::class,'store'])->name('partner.connector.store');

    Route::get('job/list',[JobController::class,'partnerJobList'])->name('partner.job.list');
    Route::get('job/list/open/{compid}',[JobController::class,'partnerJobListByCompanyId'])->name('partner.job.list.open.company');
    Route::post('job/main',[JobController::class,'partnerMain'])->name('partner.job.main');
    Route::post('job/store',[JobController::class,'store'])->name('partner.job.store');
    Route::put('job/{id}',[JobController::class,'update'])->name('partner.job.update');
    Route::post('job/execute',[JobController::class,'executeJob'])->name('partner.job.execute');
    
    Route::get('task/index',[TasksController::class,'companyTaskList'])->name('partner.task.list');
    Route::get('task/index',[TasksController::class,'companyTaskList'])->name('job.task.list');

    Route::get('processdatalog/index',[ProcessDataLogController::class,'partnerProcessDataLogIndex'])->name('partner.processdatalog.index');
    Route::post('processdatalog/main',[ProcessDataLogController::class,'partnerProcessDataLogMain'])->name('partner.processdatalog.main');

    Route::get('user/changepassword/index',[UserController::class,'userChangePasswordIdx'])->name('partner.user.changepassword.index');
    Route::put('user/changepassword/{id}',[UserController::class,'userChangePassword'])->name('partner.user.changepassword');

    Route::get('/get-locations/',[TasksController::class,'GetLocations'])->name('partner.getLocations'); 
    Route::get('/get-accountstylesclientE/',[TasksController::class,'GetAccountStylesClientE'])->name('partner.getAccountStylesClientE');  
    Route::get('/get-accountstylesclientS/',[TasksController::class,'GetAccountStylesClientS'])->name('partner.getAccountStylesClientS');  
    Route::get('/get-accountnumber/',[TasksController::class,'GetAccountNumber'])->name('partner.getAccountNumber');

    Route::get('/get-AccountStyleByID', [TasksController::class, 'getAccountStyleByID'])->name('partner.getAccountStyleByID');
    Route::post('/update-task-stateup', [TasksController::class, 'updateTaskStateUp'])->name('update.task.stateUp');  
    Route::post('/update-task-state-approvalE', [TasksController::class, 'updateTaskStateApprovalE'])->name('update.task.stateApprovalE');
    Route::post('/update-task-state-approvalS', [TasksController::class, 'updateTaskStateApprovalS'])->name('update.task.stateApprovalS');
    Route::post('/update-task-statedown', [TasksController::class, 'updateTaskStateDown'])->name('update.task.stateDown');  

    //Custom Factors
    Route::get('customFactor/browse', [CustomFactorController::class, 'customFactorBrowse'])->name('customFactor.browse');  
    Route::post('customfactor/update', [CustomFactorController::class, 'update'])->name('customFactor.update');
    Route::get('custom-factor/export', [CustomFactorController::class, 'exportCustomFactor'])->name('customFactor.export');

});

Route::group(['prefix' => 'partner-user', 'middleware' => ['role:superadmin|user']], function() { 
    #--- Task Create - List
    Route::get('create/taskE',[TasksController::class,'createTaskMainE'])->name('create.task.listE'); // Load Task List
    Route::get('create/taskS',[TasksController::class,'createTaskMainS'])->name('create.task.listS'); // Load Task List 
    #--- Task Create - CRUD
    Route::post('create/task/crudE',[TasksController::class,'createTaskE'])->name('create.task.crudE'); // 
    Route::post('create/task/crudS',[TasksController::class,'createTaskS'])->name('create.task.crudS'); // 
    #--- Task Create - CRUD - New 
    Route::post('create/task/crud/newE', [TasksController::class, 'CreateNewTaskE'])->name('create.task.crud.newE');  
    Route::post('create/task/crud/newUploadE',[TasksController::class,'CreateNewTaskUploadE'])->name('create.task.crud.newUploadE');
    Route::post('create/task/crud/newUploadS',[TasksController::class,'CreateNewTaskUploadS'])->name('create.task.crud.newUploadS');
    Route::post('create/task/crud/newS', [TasksController::class, 'CreateNewTaskS'])->name('create.task.crud.newS');  
    
    #--- Task Create - CRUD - Update (Edit & delete ) 
    Route::get('create/task/crud/readE/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadE'])->name('create.task.crud.readE'); // Load Task Detail Selected
    Route::get('create/task/crud/readS/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadS'])->name('create.task.crud.readS'); // Load Task Detail Selected
    Route::put('create/task/crud/update/{id}',[TasksController::class,'TaskSelectedUpdate'])->name('create.task.crud.update'); // Update Task Detail
    Route::put('create/task/crud/updateCSR/{id}',[TasksController::class,'TaskSelectedUpdateCSR'])->name('create.task.crud.updateCSR'); // Update Task Detail
    Route::post('create/task/crud/insertRow', [TasksController::class, 'TaskSelectedInsertRow'])->name('create.task.crud.insertRow');
    Route::post('create/task/crud/insertRowCSR', [TasksController::class, 'TaskSelectedInsertRowCSR'])->name('create.task.crud.insertRowCSR');
    Route::delete('create/task/crud/updateDel/{id}', [TasksController::class, 'TaskSelectedUpdateDel'] )->name('create.task.crud.updateDel');
    Route::delete('create/task/crud/updateDelCSR/{id}', [TasksController::class, 'TaskSelectedUpdateDelCSR'] )->name('create.task.crud.updateDelCSR');
    
    #--- Task Create - Update Status Ready to Submit into Ready to Check 
    // Route::post('/update-task-state', [TasksController::class, 'updateTaskState'])->name('update.task.state');
    #--- Task UnEvaluation 
    Route::post('task/maker/retrieve/data/open',[TasksController::class,'makerRetrieveDataOpen'])->name('partner.task.maker.retrieve.data.open');
    Route::post('task/maker/retrieve/data/mapping',[TasksController::class,'makerRetrieveDataMaping'])->name('partner.task.maker.retrieve.data.mapping');
    Route::get('task/maker/retrieve/load/{taskid}',[TasksController::class,'makerRetrieveLoad'])->name('partner.task.maker.retrieve.load');
    Route::put('task/maker/retrieve/update/{accstyleid}',[TasksController::class,'makerRetrieveUpdate'])->name('partner.task.maker.retrieve.update');
    Route::get('task/maker/upload/list/task',[TasksController::class,'makerUploadTask'])->name('partner.task.maker.upload.list.task');
    Route::post('task/maker/upload/main',[TasksController::class,'makerUploadMain'])->name('partner.task.maker.upload.main');
    Route::post('task/maker/file/process',[TasksController::class,'makerUploadFileProcess'])->name('partner.task.maker.file.process');
    Route::put('task/maker/created/{id}',[TasksController::class,'makerUploadCreated'])->name('partner.task.maker.created');
    // Route::post('maker/task/main',[TasksController::class,'makerTaskMain'])->name('partner.maker.task.main');   
    // Route::get('maker/task/uploadload',[TasksController::class,'makerTaskUpload'])->name('partner.maker.task.upload');
    //data capture
    // Route::post('maker/task/capture/list',[TasksController::class,'makerDataCaptureIndex'])->name('partner.maker.task.capture.list');
    // Route::get('task/maker/retrieve/list/task',[TasksController::class,'makerRetrieveTask'])->name('partner.task.maker.retrieve.list.task');
    // Route::get('tasks/maker/file/open',[TasksController::class,'makerUploadFileOpen'])->name('tasks.maker.file.open');
    
    
});
Route::group(['prefix' => 'partner-supervisor', 'middleware' => ['role:superadmin|supervisor']], function() {
    #--- Task Verify - List
    Route::get('verify/taskE',[TasksController::class,'verifyTaskMainE'])->name('verify.task.listE'); 
    Route::get('verify/taskS',[TasksController::class,'verifyTaskMainS'])->name('verify.task.listS'); 
    #--- Task Create - CRUD
    Route::post('verify/task/crudE',[TasksController::class,'verifyTaskCRUDE'])->name('verify.task.crudE'); // 
    Route::post('verify/task/crudS',[TasksController::class,'verifyTaskCRUDS'])->name('verify.task.crudS'); // 
    Route::get('verify/task/crud/readE/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadE'])->name('verify.task.crud.readE'); // Load Task Detail Selected
    Route::get('verify/task/crud/readS/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadS'])->name('verify.task.crud.readS'); // Load Task Detail Selected
    Route::put('verify/task/crud/update/{id}',[TasksController::class,'TaskSelectedUpdate'])->name('verify.task.crud.update'); // Update Task Detail
    Route::put('verify/task/crud/updateCSR/{id}',[TasksController::class,'TaskSelectedUpdateCSR'])->name('verify.task.crud.updateCSR'); // Update Task Detail
    Route::post('verify/task/crud/insertRow', [TasksController::class, 'TaskSelectedInsertRow'])->name('verify.task.crud.insertRow');
    Route::post('verify/task/crud/insertRowCSR', [TasksController::class, 'TaskSelectedInsertRowCSR'])->name('verify.task.crud.insertRowCSR');
    Route::delete('verify/task/crud/updateDel/{id}', [TasksController::class, 'TaskSelectedUpdateDel'] )->name('verify.task.crud.updateDel');
    Route::delete('verify/task/crud/updateDelCSR/{id}', [TasksController::class, 'TaskSelectedUpdateDelCSR'] )->name('verify.task.crud.updateDelCSR');
    #--- Task Create - Update Status Ready to Submit into Ready to Check
    // Route::post('task/checker/main',[TasksController::class,'checkerMain'])->name('partner.task.checker.main');
    // Route::put('task/checker/continue/{id}',[TasksController::class,'checkerContinue'])->name('partner.task.checker.continue');
    // Route::post('task/checker/reject',[TasksController::class,'checkerReject'])->name('partner.task.checker.reject');
});

Route::group(['prefix' => 'partner-manager', 'middleware' => ['role:superadmin|manager']], function() {
    Route::get('dashboard/manager/index',[DashboardCompanyController::class,'dashboardIndex'])->name('partner.dashboard.manager.index');
    #--- Task Approve - List
    Route::get('approve/taskE',[TasksController::class,'approveTaskMainE'])->name('approve.task.listE');
    Route::get('approve/taskS',[TasksController::class,'approveTaskMainS'])->name('approve.task.listS');
    #--- Task Create - CRUD 
    Route::post('approve/task/crudE',[TasksController::class,'approveTaskCRUDE'])->name('approve.task.crudE');
    Route::post('approve/task/crudS',[TasksController::class,'approveTaskCRUDS'])->name('approve.task.crudS');
    Route::get('approve/task/crud/readE/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadE'])->name('approval.task.crud.readE'); // Load Task Detail Selected
    Route::get('approve/task/crud/readS/{taskid}/{transno}',[TasksController::class,'TaskSelectedLoadS'])->name('approval.task.crud.readS'); // Load Task Detail Selected
    Route::post('task/approval/main',[TasksController::class,'approvalMain'])->name('partner.task.approval.main');
    Route::put('task/approval/submit/{id}',[TasksController::class,'approvalSubmit'])->name('partner.task.approval.submit');
    Route::post('task/approval/reject',[TasksController::class,'approvalReject'])->name('partner.task.approval.reject');
});


// Route::group(['prefix' => 'security', 'middleware' => ['role:superadmin|admin|agile-manager']], function() {
//     Route::get('stores/access/index',[UserController::class,'storesAccessIndex'])->name('store.access.index');
//     Route::post('stores/access/load',[UserController::class,'storesAccessLoad'])->name('store.access.load');
//     Route::post('stores/access/main',[UserController::class,'storeAccessMain'])->name('store.access.main');
//     Route::put('stores/access/update/{id}',[UserController::class,'storeAccessUpdate'])->name('store.access.update');
// });


// Route::group(['prefix' => 'partner-supervisor', 'middleware' => ['role:superadmin|supervisor']], function() {  
//     Route::get('task/checker/list/task',[TasksController::class,'checkerTask'])->name('partner.task.checker.list.task');
//     Route::post('task/checker/main',[TasksController::class,'checkerMain'])->name('partner.task.checker.main');
//     Route::put('task/checker/continue/{id}',[TasksController::class,'checkerContinue'])->name('partner.task.checker.continue');
//     Route::post('task/checker/reject',[TasksController::class,'checkerReject'])->name('partner.task.checker.reject');
// });

// Route::group(['prefix' => 'partner-manager', 'middleware' => ['role:superadmin|manager']], function() {
//     Route::get('dashboard/manager/index',[DashboardCompanyController::class,'dashboardIndex'])->name('partner.dashboard.manager.index');

//     Route::get('task/approval/list/task',[TasksController::class,'approvalTask'])->name('partner.task.approval.list.task');
//     Route::post('task/approval/main',[TasksController::class,'approvalMain'])->name('partner.task.approval.main');
//     Route::put('task/approval/submit/{id}',[TasksController::class,'approvalSubmit'])->name('partner.task.approval.submit');
//     Route::post('task/approval/reject',[TasksController::class,'approvalReject'])->name('partner.task.approval.reject');
// });
// Route::group(['prefix' => 'asri-core', 'middleware' => ['role:superadmin|agile-manager']], function() {
//     Route::get('integration-log/browse', [IntegrationLogController::class, 'browse'])->name('integration_log.browse');
//     Route::get('integration-log/list', [IntegrationLogController::class, 'list'])->name('integration_log.list');
//     Route::post('integration-log/main', [IntegrationLogController::class, 'main'])->name('integration_log.main');
//     Route::get('integration-log/detail/{id}', [IntegrationLogController::class, 'detail'])->name('integration_log.detail');
// });