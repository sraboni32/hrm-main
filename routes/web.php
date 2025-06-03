<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\SalaryDisbursementController;
//I am coming

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


$installed = Storage::disk('public')->exists('installed');

if ($installed === true) {

    Route::resource('posts', PostController::class);

    Route::get('/test', function () {
        return view('testing');
    })->name('test');

    Route::get('/home', function () {
        return view('home');
    })->name('home');

    Route::get('/about', function () {
        return view('about');
    })->name('about');

    Route::get('/service', function () {
        return view('service');
    })->name('service');

    Route::get('/research', function () {
        return view('research');
    })->name('research');

    Route::get('/feature', function () {
        return view('feature');
    })->name('feature');

    Route::get('/faq', function () {
        return view('faq');
    })->name('faq');


    Route::get('/contact', function () {
        return view('contact');
    })->name('contact');

    Auth::routes(['register' => false]);


    Route::group(['middleware'=>'XSS'],function(){

        Route::get('/', "HomeController@RedirectToLogin");
        Route::get('switch/language/{lang}', 'LocalController@languageSwitch')->name('language.switch');

        //------------------------------- dashboard Admin--------------------------\\

        Route::group(['middleware'=>'auth','Is_Admin'],function(){

            Route::get('dashboard/admin', "DashboardController@dashboard_admin")->name('dashboard');

            //------------------------------------------------------------------\\

            Route::get('/update_database', 'UpdateController@viewStep1');

            Route::get('/update_database/finish', function () {

                return view('update.finishedUpdate');
            });

            Route::post('/update_database/lastStep', [
                'as' => 'update_lastStep', 'uses' => 'UpdateController@lastStep',
            ]);

        });

        Route::group(['middleware'=>'auth'],function(){

            //------------------------------- dashboard --------------------------\\
            //--------------------------------------------------------------------\\

            Route::get('dashboard/employee', "DashboardController@dashboard_employee")->name('dashboard_employee');
            Route::get('dashboard/client', "DashboardController@dashboard_client")->name('dashboard_client');

            //------------------------------- Employee --------------------------\\
            //--------------------------------------------------------------------\\
            Route::resource('employees', 'EmployeesController');
            Route::get("Get_all_employees", "EmployeesController@Get_all_employees");
            Route::get("Get_employees_by_company", "EmployeesController@Get_employees_by_company");
            Route::get("Get_employees_by_department", "EmployeesController@Get_employees_by_department");
            Route::get("Get_office_shift_by_company", "EmployeesController@Get_office_shift_by_company");
            Route::put("update_social_profile/{id}", "EmployeesController@update_social_profile");
            Route::put("update_employee_document/{id}", "EmployeesController@update_employee_document");
            Route::post("employees/delete/by_selection", "EmployeesController@delete_by_selection");


            //------------------------------- Employee Experience ----------------\\
            //--------------------------------------------------------------------\\

            Route::resource('work_experience', 'EmployeeExperienceController');


                //------------------------------- Employee Document ----------------\\
            //--------------------------------------------------------------------\\

            Route::resource('employee_document', 'EmployeeDocumentController');

            //------------------------------- Employee Accounts bank ----------------\\
            //--------------------------------------------------------------------\\

            Route::resource('employee_account', 'EmployeeAccountController');

            //------------------------------- Hr Management --------------------------\\
            //----------------------------------------------------------------------\\

            Route::prefix('core')->group(function () {


                //------------------------------- company --------------------------\\
                //--------------------------------------------------------------------\\
                Route::resource('company', 'CompanyController');
                Route::get("Get_all_Company", "CompanyController@Get_all_Company");
                Route::post("company/delete/by_selection", "CompanyController@delete_by_selection");

                //------------------------------- departments --------------------------\\
                //--------------------------------------------------------------------\\
                Route::resource('departments', 'DepartmentsController');
                Route::get("Get_all_departments", "DepartmentsController@Get_all_Departments");
                Route::get("Get_departments_by_company", "DepartmentsController@Get_departments_by_company")->name('Get_departments_by_company');
                Route::post("departments/delete/by_selection", "DepartmentsController@delete_by_selection");

                //------------------------------- designations --------------------------\\
                //--------------------------------------------------------------------\\
                Route::resource('designations', 'DesignationsController');
                Route::get("get_designations_by_department", "DesignationsController@Get_designations_by_department");
                Route::post("designations/delete/by_selection", "DesignationsController@delete_by_selection");

                //------------------------------- policies --------------------------\\
                //--------------------------------------------------------------------\\
                Route::resource('policies', 'PoliciesController');
                Route::post("policies/delete/by_selection", "PoliciesController@delete_by_selection");


                //------------------------------- announcements ---------------------\\
                //--------------------------------------------------------------------\\
                Route::resource('announcements', 'AnnouncementsController');
                Route::post("announcements/delete/by_selection", "AnnouncementsController@delete_by_selection");

            });


            //------------------------------- Attendances ------------------------\\
            //--------------------------------------------------------------------\\
            Route::resource('attendances', 'AttendancesController');
            Route::get("daily_attendance", "AttendancesController@daily_attendance")->name('daily_attendance');
            Route::post('attendance_by_employee/{id}', 'EmployeeSessionController@attendance_by_employee')->name('attendance_by_employee.post');
            Route::post("attendances/delete/by_selection", "AttendancesController@delete_by_selection");



            //------------------------------- Accounting -----------------------\\
            //----------------------------------------------------------------\\
            Route::prefix('accounting')->group(function () {

                Route::resource('account', 'AccountController');
                Route::resource('deposit', 'DepositController');
                Route::resource('expense', 'ExpenseController');
                Route::resource('expense_category', 'ExpenseCategoryController');
                Route::resource('deposit_category', 'DepositCategoryController');
                Route::resource('payment_methods', 'PaymentMethodController');

                Route::post("account/delete/by_selection", "AccountController@delete_by_selection");
                Route::post("deposit/delete/by_selection", "DepositController@delete_by_selection");
                Route::post("expense/delete/by_selection", "ExpenseController@delete_by_selection");
                Route::post("expense_category/delete/by_selection", "ExpenseCategoryController@delete_by_selection");
                Route::post("deposit_category/delete/by_selection", "DepositCategoryController@delete_by_selection");
                Route::post("payment_methods/delete/by_selection", "PaymentMethodController@delete_by_selection");

                // Expense import
                Route::post('accounting/expense/import', 'ExpenseController@import')->name('expense.import');
                // Expense Category import
                Route::post('accounting/expense_category/import', 'ExpenseCategoryController@import')->name('expense_category.import');
                // Account import
                Route::post('accounting/account/import', 'AccountController@import')->name('account.import');
                // Deposit import
                Route::post('accounting/deposit/import', 'DepositController@import')->name('deposit.import');
                // Deposit Category import
                Route::post('accounting/deposit_category/import', 'DepositCategoryController@import')->name('deposit_category.import');
                // Payment Methods import
                Route::post('accounting/payment_methods/import', 'PaymentMethodController@import')->name('payment_methods.import');

            });


            //------------------------------- Project -----------------------\\
            //----------------------------------------------------------------\\

            // Custom project routes (must be before resource routes to avoid conflicts)
            Route::post('projects/import', [App\Http\Controllers\ProjectController::class, 'import'])->name('projects.import');
            Route::get('projects/download-template', [App\Http\Controllers\ProjectController::class, 'downloadTemplate'])->name('projects.download_template');
            Route::post("projects/delete/by_selection", "ProjectController@delete_by_selection");

            Route::resource('projects', 'ProjectController');

            Route::post("project_discussions", "ProjectController@Create_project_discussions");
            Route::delete("project_discussions/{id}", "ProjectController@destroy_project_discussion");

            Route::post("project_issues", "ProjectController@Create_project_issues");
            Route::put("project_issues/{id}", "ProjectController@Update_project_issues");
            Route::delete("project_issues/{id}", "ProjectController@destroy_project_issues");

            Route::post("project_documents", "ProjectController@Create_project_documents");
            Route::delete("project_documents/{id}", "ProjectController@destroy_project_documents");

            Route::post("project_links", "ProjectController@Create_project_links");
            Route::delete("project_links/{id}", "ProjectController@destroy_project_links");

            //------------------------------- Task -----------------------\\
            //----------------------------------------------------------------\\

            // Custom task routes (must be before resource routes to avoid conflicts)
            Route::post('tasks/import', [App\Http\Controllers\TaskController::class, 'import'])->name('tasks.import');
            Route::get('tasks/download-template', [App\Http\Controllers\TaskController::class, 'downloadTemplate'])->name('tasks.download_template');
            Route::post("tasks/delete/by_selection", "TaskController@delete_by_selection");
            Route::get("tasks_kanban", "TaskController@tasks_kanban")->name('tasks_kanban');
            Route::post("task_change_status", "TaskController@task_change_status")->name('task_change_status');

            Route::resource('tasks', 'TaskController');
            Route::put("update_task_status/{id}", "TaskController@update_task_status");

            Route::post("task_discussions", "TaskController@Create_task_discussions");
            Route::delete("task_discussions/{id}", "TaskController@destroy_task_discussion");

            Route::post("task_documents", "TaskController@Create_task_documents");
            Route::delete("task_documents/{id}", "TaskController@destroy_task_documents");

            Route::post("task_links", "TaskController@Create_task_links");
            Route::delete("task_links/{id}", "TaskController@destroy_task_links");

            //------------------------------- AI Task Generation -----------------------\\
            //----------------------------------------------------------------\\

            Route::get('projects/{id}/ai-tasks', 'ProjectController@showAiTaskGenerator')->name('projects.ai_tasks');
            Route::post('projects/{id}/ai-tasks/generate-and-create', 'ProjectController@generateAndCreateAiTasks')->name('projects.generate_and_create_ai_tasks');
            Route::post('projects/{id}/ai-tasks/generate', 'ProjectController@generateAiTasks')->name('projects.generate_ai_tasks');
            Route::post('projects/{id}/ai-tasks/save-bulk', 'ProjectController@saveBulkAiTasks')->name('projects.save_bulk_ai_tasks');

            //------------------------------- AI Chat -----------------------\\
            //----------------------------------------------------------------\\

            Route::prefix('ai-chat')->middleware('ai_chat_access')->group(function () {
                Route::get('/', 'AiChatController@index')->name('ai_chat.index');
                Route::post('/send', 'AiChatController@sendMessage')->name('ai_chat.send');
                Route::get('/conversation/{id}', 'AiChatController@getConversation')->name('ai_chat.conversation');
                Route::get('/conversations', 'AiChatController@getConversations')->name('ai_chat.conversations');
                Route::post('/conversation/{id}/end', 'AiChatController@endConversation')->name('ai_chat.end');
                Route::post('/new', 'AiChatController@newConversation')->name('ai_chat.new');
            });

            //------------------------------- Enhanced AI Chat with Suggestions -----------------------\\
            //----------------------------------------------------------------\\

            Route::prefix('enhanced-ai-chat')->middleware('ai_chat_access')->group(function () {
                Route::post('/chat', 'EnhancedAiChatController@chat')->name('enhanced_ai_chat.chat');
                Route::get('/suggestions/initial', 'EnhancedAiChatController@getInitialSuggestions')->name('enhanced_ai_chat.initial_suggestions');
                Route::get('/suggestions/search', 'EnhancedAiChatController@searchSuggestions')->name('enhanced_ai_chat.search_suggestions');
                Route::get('/suggestions/category/{category}', 'EnhancedAiChatController@getSuggestionsByCategory')->name('enhanced_ai_chat.category_suggestions');
            });

            //------------------------------- Request leave  -----------------------\\
            //----------------------------------------------------------------\\

            Route::resource('leave', 'LeaveController');
            Route::resource('leave_type', 'LeaveTypeController');
            Route::post("leave/delete/by_selection", "LeaveController@delete_by_selection");
            Route::post("leave_type/delete/by_selection", "LeaveTypeController@delete_by_selection");



            //------------------------------- training ----------------------\\
            //----------------------------------------------------------------\\
            Route::resource('trainings', 'TrainingController');
            Route::post("trainings/delete/by_selection", "TrainingController@delete_by_selection");

            Route::resource('trainers', 'TrainersController');
            Route::post("trainers/delete/by_selection", "TrainersController@delete_by_selection");

            Route::resource('training_skills', 'TrainingSkillsController');
            Route::post("training_skills/delete/by_selection", "TrainingSkillsController@delete_by_selection");


            //------------------------------- Apps Management ----------------\\
            //--------------------------------------------------------------------\\

            Route::prefix('hr')->group(function () {


                //------------------------------- office_shift ------------------\\
                //----------------------------------------------------------------\\

                Route::resource('office_shift', 'OfficeShiftController');
                Route::post("office_shift/delete/by_selection", "OfficeShiftController@delete_by_selection");

                //------------------------------- event ---------------------------\\
                //----------------------------------------------------------------\\

                Route::resource('event', 'EventController');
                Route::post("event/delete/by_selection", "EventController@delete_by_selection");

                //------------------------------- holiday ----------------------\\
                //----------------------------------------------------------------\\

                Route::resource('holiday', 'HolidayController');
                Route::post("holiday/delete/by_selection", "HolidayController@delete_by_selection");

                //------------------------------- award ----------------------\\
                //----------------------------------------------------------------\\

                Route::resource('award', 'AwardController');
                Route::post("award/delete/by_selection", "AwardController@delete_by_selection");

                Route::resource('award_type', 'AwardTypeController');
                Route::post("award_type/delete/by_selection", "AwardTypeController@delete_by_selection");


                //------------------------------- complaint ----------------------\\
                //----------------------------------------------------------------\\

                Route::resource('complaint', 'ComplaintController');
                Route::post("complaint/delete/by_selection", "ComplaintController@delete_by_selection");

                //------------------------------- travel ----------------------\\
                //----------------------------------------------------------------\\

                Route::resource('travel', 'TravelController');
                Route::post("travel/delete/by_selection", "TravelController@delete_by_selection");

                Route::resource('arrangement_type', 'ArrangementTypeController');
                Route::post("arrangement_type/delete/by_selection", "ArrangementTypeController@delete_by_selection");

            });


                //------------------------------- Clients ----------------------\\
                //----------------------------------------------------------------\\

                Route::resource('clients', 'ClientController');
                Route::post("clients/delete/by_selection", "ClientController@delete_by_selection");

                //------------------------------- Sessions Client ----------------------\\
                //----------------------------------------------------------------\\

                Route::get("client_projects", "ClientController@client_projects_index");
                Route::get("client_projects/create", "ClientController@client_projects_create");
                Route::post("client_projects", "ClientController@client_projects_store");

                Route::get("client_tasks", "ClientController@client_tasks_index");
                Route::get("client_tasks/create", "ClientController@client_tasks_create");
                Route::post("client_tasks", "ClientController@client_tasks_store");

                Route::put('client_profile/{id}', 'ProfileController@Update_client_profile');
                Route::get('client_profile', 'ProfileController@get_client_profile')->name('client_profile');

                //------------------------------- Sessions Employee ----------------------\\
                //----------------------------------------------------------------\\

                Route::put('employee_profile/{id}', 'EmployeeSessionController@Update_employee_profile');
                Route::get('employee_profile', 'EmployeeSessionController@get_employee_profile')->name('employee_profile');

                Route::get('employee/overview', 'EmployeeSessionController@employee_details')->name('employee_details');
                Route::put('session_employee/basic/info/{id}', 'EmployeeSessionController@update_basic_info');
                Route::put('session_employee/social/{id}', 'EmployeeSessionController@update_social_profile');
                Route::put("session_employee/document/{id}", "EmployeeSessionController@update_employee_document");

                Route::post("session_employee/storeExperiance", "EmployeeSessionController@storeExperiance");
                Route::put("session_employee/updateExperiance/{id}", "EmployeeSessionController@updateExperiance");
                Route::delete("session_employee/destroyExperiance/{id}", "EmployeeSessionController@destroyExperiance");


                Route::put('session_employee/update_task_status/{id}', 'EmployeeSessionController@update_task_status');

                Route::post("session_employee/storeAccount", "EmployeeSessionController@storeAccount");
                Route::put("session_employee/updateAccount/{id}", "EmployeeSessionController@updateAccount");
                Route::delete("session_employee/destroyAccount/{id}", "EmployeeSessionController@destroyAccount");

                Route::get("session_employee/Get_leave_types", "EmployeeSessionController@Get_leave_types");
                Route::post("session_employee/requestleave", "EmployeeSessionController@Request_leave");


                //------------------------------- users --------------------------\\
                //----------------------------------------------------------------\\
                Route::resource('users', 'UserController');
                Route::post('assignRole', 'UserController@assignRole');

                Route::get('getAllPermissions', 'UserController@getAllPermissions');



            //------------------------------- Settings --------------------------\\
            //----------------------------------------------------------------\\

            Route::prefix('settings')->group(function () {
                Route::resource('system_settings', 'SettingController');
                Route::resource('update_settings', 'UpdateController');
                Route::resource('email_settings', 'EmailSettingController');
                Route::resource('permissions', 'PermissionsController');
                Route::resource('currency', 'CurrencyController');
                Route::resource('backup', 'BackupController');

                Route::post("currency/delete/by_selection", "CurrencyController@delete_by_selection");

            });

                //------------------------------- Module Settings ------------------------\\

                Route::resource('module_settings', 'ModuleSettingController');
                Route::post('update_status_module', 'ModuleSettingController@update_status_module')->name('update_status_module');
                Route::post('upload_module', 'ModuleSettingController@upload_module')->name('upload_module');
                Route::get('update_database_module/{module_name}', 'ModuleSettingController@update_database_module')->name('update_database_module');

                Route::get('GenerateBackup', 'BackupController@GenerateBackup');


            //------------------------------- Reports --------------------------\\
            //----------------------------------------------------------------\\

            Route::prefix('report')->group(function () {
                Route::get('attendance', 'ReportController@attendance_report_index')->name('attendance_report_index');
                Route::get('employee', 'ReportController@employee_report_index')->name('employee_report_index');
                Route::get('project', 'ReportController@project_report_index')->name('project_report_index');
                Route::get('task', 'ReportController@task_report_index')->name('task_report_index');
                Route::get('expense', 'ReportController@expense_report_index')->name('expense_report_index');
                Route::get('deposit', 'ReportController@deposit_report_index')->name('deposit_report_index');

                Route::POST('fetchDepartment', 'ReportController@fetchDepartment')->name('fetchDepartment');
                Route::POST('fetchDesignation', 'ReportController@fetchDesignation')->name('fetchDesignation');

                // KPI Summary Report
                Route::get('kpi-summary-report', [App\Http\Controllers\ReportController::class, 'kpi_summary_report_index'])->name('kpi_summary_report');
                // Monthly Salary Disbursement Report
                Route::get('monthly-salary-disbursement-report', [App\Http\Controllers\ReportController::class, 'monthly_salary_disbursement_report'])->name('report.monthly_salary_disbursement_report');
                Route::get('export/salary-disbursement-report', [App\Http\Controllers\ReportController::class, 'export_salary_disbursement_report'])->name('export.salary_disbursement_report');
                Route::get('leave-absence-report', [App\Http\Controllers\ReportController::class, 'leave_absence_report'])->name('report.leave_absence_report');

                // Export Routes
                Route::get('export/employee-report', [App\Http\Controllers\ReportController::class, 'export_employee_report'])->name('export.employee_report');
                Route::get('export/attendance-report', [App\Http\Controllers\ReportController::class, 'export_attendance_report'])->name('export.attendance_report');
                Route::get('export/kpi-summary-report', [App\Http\Controllers\ReportController::class, 'export_kpi_summary_report'])->name('export.kpi_summary_report');
                Route::get('export/leave-absence-report', [App\Http\Controllers\ReportController::class, 'export_leave_absence_report'])->name('export.leave_absence_report');

                // Test PDF Export System
                Route::get('test-pdf-exports', [App\Http\Controllers\ReportController::class, 'test_pdf_exports'])->name('test.pdf_exports');
            });

            //------------------------------- Profile --------------------------\\
            //----------------------------------------------------------------\\
            Route::put('updateProfile/{id}', 'ProfileController@updateProfile');
            Route::resource('profile', 'ProfileController');

            //------------------------------- clear_cache --------------------------\\

            Route::get("clear_cache", "SettingController@Clear_Cache");

        });


    });

    // HRM Bonus/Allowance Management
    Route::prefix('hrm')->middleware(['auth'])->group(function () {
        Route::get('bonus-allowance', [App\Http\Controllers\BonusAllowanceController::class, 'index'])->name('hrm.bonus_allowance.index');
        Route::get('bonus-allowance/create', [App\Http\Controllers\BonusAllowanceController::class, 'create'])->name('hrm.bonus_allowance.create');
        Route::post('bonus-allowance', [App\Http\Controllers\BonusAllowanceController::class, 'store'])->name('hrm.bonus_allowance.store');
        Route::post('bonus-allowance/bulk', [App\Http\Controllers\BonusAllowanceController::class, 'bulkStore'])->name('hrm.bonus_allowance.bulk_store');
    });

    // Notifications
    Route::middleware(['auth'])->group(function () {
        Route::get('notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/fetch', [App\Http\Controllers\NotificationController::class, 'fetch'])->name('notifications.fetch');
        Route::post('notifications/{id}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    });

    // Public Job Vacancies
    Route::get('/jobs', [App\Http\Controllers\JobVacancyController::class, 'index'])->name('job_vacancies.index');

    // Admin Job Vacancies
    Route::middleware(['auth'])->group(function () {
        Route::prefix('admin')->group(function () {
            Route::get('/job-vacancies', [App\Http\Controllers\JobVacancyController::class, 'admin_index'])->name('job_vacancies.admin.index');
            Route::get('/job-vacancies/export', [App\Http\Controllers\JobVacancyController::class, 'export'])->name('job_vacancies.admin.export');
            Route::post('/job-vacancies/import', [App\Http\Controllers\JobVacancyController::class, 'import'])->name('job_vacancies.admin.import');
            Route::post('/job-vacancies/delete/by_selection', [App\Http\Controllers\JobVacancyController::class, 'delete_by_selection'])->name('job_vacancies.admin.delete_by_selection');
            Route::get('/job-vacancies/create', [App\Http\Controllers\JobVacancyController::class, 'create'])->name('job_vacancies.admin.create');
            Route::post('/job-vacancies', [App\Http\Controllers\JobVacancyController::class, 'store'])->name('job_vacancies.admin.store');
            Route::get('/job-vacancies/{id}/edit', [App\Http\Controllers\JobVacancyController::class, 'edit'])->name('job_vacancies.admin.edit');
            Route::put('/job-vacancies/{id}', [App\Http\Controllers\JobVacancyController::class, 'update'])->name('job_vacancies.admin.update');
            Route::delete('/job-vacancies/{id}', [App\Http\Controllers\JobVacancyController::class, 'destroy'])->name('job_vacancies.admin.destroy');
            Route::post('/job-vacancies/{id}/delete', [App\Http\Controllers\JobVacancyController::class, 'destroy'])->name('job_vacancies.admin.destroy.post');
        });
    });



    // Salary Disbursement Routes
    Route::middleware(['auth'])->group(function () {
        Route::post('/salary-disbursement/send-for-review', [SalaryDisbursementController::class, 'sendForReview'])
            ->name('salary-disbursement.send-for-review')
            ->middleware('permission:salary_disbursement_report');

        Route::post('/salary-disbursement/{disbursement}/review', [SalaryDisbursementController::class, 'submitReview'])
            ->name('salary-disbursement.submit-review');

        Route::post('/salary-disbursement/{disbursement}/approve', [SalaryDisbursementController::class, 'approve'])
            ->name('salary-disbursement.approve');

        Route::post('/salary-disbursement/{disbursement}/mark-as-paid', [SalaryDisbursementController::class, 'markAsPaid'])
            ->name('salary-disbursement.mark-as-paid');

        Route::post('/salary-disbursement/{disbursement}/address-feedback', [SalaryDisbursementController::class, 'addressFeedback'])
            ->name('salary-disbursement.address-feedback');

        Route::post('/salary-disbursement/{disbursement}/resubmit-for-review', [SalaryDisbursementController::class, 'resubmitForReview'])
            ->name('salary-disbursement.resubmit-for-review');

        Route::post('/salary-disbursement/update-inline', [SalaryDisbursementController::class, 'updateInline'])
            ->name('salary-disbursement.update-inline');

        Route::get('/my-salary-disbursements', [SalaryDisbursementController::class, 'employeeIndex'])->name('salary_disbursement.employee_index');
        Route::put('/salary-disbursement/{id}', [App\Http\Controllers\SalaryDisbursementController::class, 'update'])
            ->name('salary-disbursement.update')
            ->middleware('permission:salary_disbursement_report');

        Route::match(['put', 'post'], '/salary-disbursement/{id}', [App\Http\Controllers\SalaryDisbursementController::class, 'update'])
            ->name('salary-disbursement.update')
            ->middleware('permission:salary_disbursement_report');
    });

} else {

        Route::get('/{vue?}',
        function () {
                return redirect('/setup');
        })->where('vue', '^(?!setup).*$');


    Route::get('/setup', [
        'uses' => 'SetupController@viewCheck',
    ])->name('setup');

    Route::get('/setup/step-1', [
        'uses' => 'SetupController@viewStep1',
    ]);

    Route::post('/setup/step-2', [
        'as' => 'setupStep1', 'uses' => 'SetupController@setupStep1',
    ]);

    Route::post('/setup/testDB', [
        'as' => 'testDB', 'uses' => 'TestDbController@testDB',
    ]);

    Route::get('/setup/step-2', [
        'uses' => 'SetupController@viewStep2',
    ]);

    Route::get('/setup/step-3', [
        'uses' => 'SetupController@viewStep3',
    ]);

    Route::get('/setup/finish', function () {

        return view('setup.finishedSetup');
    });

    Route::get('/setup/getNewAppKey', [
        'as' => 'getNewAppKey', 'uses' => 'SetupController@getNewAppKey',
    ]);

    Route::post('/setup/step-3', [
        'as' => 'setupStep2', 'uses' => 'SetupController@setupStep2',
    ]);

    Route::post('/setup/step-4', [
        'as' => 'setupStep3', 'uses' => 'SetupController@setupStep3',
    ]);

    Route::post('/setup/step-5', [
        'as' => 'setupStep4', 'uses' => 'SetupController@setupStep4',
    ]);

    Route::post('/setup/lastStep', [
        'as' => 'lastStep', 'uses' => 'SetupController@lastStep',
    ]);

    Route::get('setup/lastStep', function () {
        return redirect('/setup', 301);
    });

}



