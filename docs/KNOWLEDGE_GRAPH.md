# HTMS Knowledge Graph Index
> Tạo tự động bởi vibecode/enricher.py — 2026-06-08 09:03
> Đây là bộ nhớ nén toàn hệ thống. AI agents đọc file này thay vì scan codebase.

## Tóm Tắt Nhanh

- **Tổng files:** 593
- **Số layers:** 22
- **Số domains:** 17

---

## Index Theo Domain (Business Logic)

### 🛡️ PERMISSIONS (42 files)

- `app/Console/Commands/SyncFeatures.php` [PHP-Other] — PHP-Other: Sync Features
- `app/Console/Commands/SyncPermissions.php` [PHP-Other] — PHP-Other: Sync Permissions
- `app/Http/Controllers/Admin/SystemFeatureController.php` [AdminController] — Controller phân quyền MAC hệ thống
- `app/Http/Controllers/RoleController.php` [Controller] — Controller: Role Controller
- `app/Http/Middleware/CheckFeatureAccess.php` [Middleware] — Middleware kiểm tra quyền truy cập portal
- `app/Http/Middleware/CheckPortalAccess.php` [Middleware] — Middleware kiểm tra quyền truy cập portal
- `app/Http/Middleware/PortalAccessMiddleware.php` [Middleware] — Middleware kiểm tra quyền truy cập portal
- `app/Models/DepartmentRole.php` [Model] — Model phân quyền MAC V2
- `app/Models/Feature.php` [Model] — Model phân quyền MAC V2
- `app/Models/FeatureDepartment.php` [Model] — Model phân quyền MAC V2
- `app/Models/OrgRole.php` [Model] — Model phân quyền MAC V2
- `config/permission.php` [Config] — Config: permission
- `database/migrations/2026_02_26_050106_create_permission_tables.php` [Migration] — Migration DB: 2026 02 26 050106 create permission tables
- `database/migrations/2026_02_26_050141_create_org_roles_table.php` [Migration] — Migration DB: 2026 02 26 050141 create org roles table
- `database/migrations/2026_02_27_124818_add_feature_keys_to_departments_table.php` [Migration] — Migration DB: 2026 02 27 124818 add feature keys to departments table
- `database/migrations/2026_03_01_043137_add_speaker_permissions.php` [Migration] — Migration DB: 2026 03 01 043137 add speaker permissions
- `database/migrations/2026_03_01_050031_add_department_portal_permissions.php` [Migration] — Migration DB: 2026 03 01 050031 add department portal permissions
- `database/migrations/2026_03_01_105023_add_portal_permissions.php` [Migration] — Migration DB: 2026 03 01 105023 add portal permissions
- `database/migrations/2026_03_06_102207_add_available_features_to_departments_table.php` [Migration] — Migration DB: 2026 03 06 102207 add available features to departments table
- `database/migrations/2026_03_06_171215_create_features_table.php` [Migration] — Migration DB: 2026 03 06 171215 create features table
- `database/migrations/2026_03_07_031354_create_feature_department_table.php` [Migration] — Migration DB: 2026 03 07 031354 create feature department table
- `database/migrations/2026_03_07_031530_add_is_active_to_feature_department_table.php` [Migration] — Migration DB: 2026 03 07 031530 add is active to feature department table
- `database/migrations/2026_03_07_063945_make_block_type_nullable_in_feature_department_table.php` [Migration] — Migration DB: 2026 03 07 063945 make block type nullable in feature department table
- `database/migrations/2026_03_10_031010_create_department_roles_table.php` [Migration] — Migration DB: 2026 03 10 031010 create department roles table
- `database/migrations/2026_03_10_041456_add_section_fields_to_department_roles_table.php` [Migration] — Migration DB: 2026 03 10 041456 add section fields to department roles table
- `database/migrations/2026_03_16_030556_add_data_scope_to_features.php` [Migration] — Migration DB: 2026 03 16 030556 add data scope to features
- `database/seeders/FeatureSeeder.php` [Seeder] — Seeder dữ liệu: Feature Seeder
- `database/seeders/PermissionSampleDataSeeder.php` [Seeder] — Seeder dữ liệu: Permission Sample Data Seeder
- `database/seeders/PermissionSeeder.php` [Seeder] — Seeder dữ liệu: Permission Seeder
- `database/seeders/RestoreFeaturesSeeder.php` [Seeder] — Seeder dữ liệu: Restore Features Seeder
- *(... và 12 files khác)*

### 🔐 AUTH (28 files)

- `app/Http/Controllers/Auth/AuthController.php` [AuthController] — AuthController: Auth Controller
- `app/Http/Controllers/Auth/NewPasswordController.php` [AuthController] — AuthController: New Password Controller
- `app/Http/Controllers/Auth/PasswordResetLinkController.php` [AuthController] — AuthController: Password Reset Link Controller
- `app/Models/EduSession.php` [Model] — Model người dùng & xác thực
- `app/Models/EduSessionRecord.php` [Model] — Model người dùng & xác thực
- `app/Models/FinanceSessionMetric.php` [Model] — Model người dùng & xác thực
- `config/auth.php` [Config] — Config: auth
- `config/session.php` [Config] — Config: session
- `database/migrations/2026_03_02_051027_create_finance_session_metrics_table.php` [Migration] — Migration DB: 2026 03 02 051027 create finance session metrics table
- `database/migrations/2026_03_02_080227_add_tithe_count_to_finance_session_metrics_table.php` [Migration] — Migration DB: 2026 03 02 080227 add tithe count to finance session metrics table
- `database/migrations/2026_03_04_084500_add_attendance_mode_to_edu_sessions.php` [Migration] — Migration DB: 2026 03 04 084500 add attendance mode to edu sessions
- `database/migrations/2026_03_04_100000_add_lesson_series_to_edu_sessions.php` [Migration] — Migration DB: 2026 03 04 100000 add lesson series to edu sessions
- `database/migrations/2026_03_04_114000_add_teacher_to_edu_sessions.php` [Migration] — Migration DB: 2026 03 04 114000 add teacher to edu sessions
- `database/migrations/2026_03_05_040000_add_bible_quiz_fields_to_edu_sessions.php` [Migration] — Migration DB: 2026 03 05 040000 add bible quiz fields to edu sessions
- `public/build/assets/Auth-DMlBpIDd.js` [JavaScript] — JavaScript: Auth DMl Bp IDd
- `public/build/assets/AuthenticatedLayout-D0KeaYO_.js` [JavaScript] — JavaScript: Authenticated Layout D0Kea YO 
- `public/build/assets/ForgotPassword-BNO6Tb_A.js` [JavaScript] — JavaScript: Forgot Password BNO6Tb A
- `public/build/assets/Login-DcAVPomB.js` [JavaScript] — JavaScript: Login Dc AVPom B
- `public/build/assets/ResetPassword-CCapvyxD.js` [JavaScript] — JavaScript: Reset Password CCapvyx D
- `public/build/assets/Session-0m3FEyb2.js` [JavaScript] — JavaScript: Session 0m3FEyb2
- `public/build/assets/SessionList-BFhE43Sa.js` [JavaScript] — JavaScript: Session List BFh E43Sa
- `resources/js/Layouts/AuthenticatedLayout.vue` [VueLayout] — Layout Vue: Authenticated Layout
- `resources/js/Pages/Auth/ForgotPassword.vue` [VuePage] — Trang Vue: Forgot Password
- `resources/js/Pages/Auth/Login.vue` [VuePage] — Trang Vue: Login
- `resources/js/Pages/Auth/ResetPassword.vue` [VuePage] — Trang Vue: Reset Password
- `resources/js/Pages/Docs/Auth.vue` [VuePage] — Trang Vue: Auth
- `resources/js/Pages/Portal/Education/Session.vue` [VuePage-Portal] — Trang Vue (Portal): Session
- `resources/js/Pages/Portal/Education/SessionList.vue` [VuePage-Portal] — Trang Vue (Portal): Session List

### 👥 MEMBERS (59 files)

- `app/Exports/PortalMemberExport.php` [PHP-Other] — PHP-Other: Portal Member Export
- `app/Http/Controllers/Admin/UserPermissionController.php` [AdminController] — Controller quản lý người dùng (Admin)
- `app/Http/Controllers/MemberController.php` [Controller] — Controller: Member Controller
- `app/Http/Controllers/MemberPortalController.php` [Controller] — Controller: Member Portal Controller
- `app/Http/Controllers/Portal/PortalMemberController.php` [PortalController] — Controller quản lý thành viên portal
- `app/Http/Controllers/User/DonationController.php` [Controller] — Controller: Donation Controller
- `app/Http/Controllers/UserController.php` [Controller] — Controller: User Controller
- `app/Imports/PortalMemberImport.php` [PHP-Other] — PHP-Other: Portal Member Import
- `app/Models/EduClassMember.php` [Model] — Model dữ liệu thành viên
- `app/Models/Member.php` [Model] — Model dữ liệu thành viên
- `app/Models/MemberContribution.php` [Model] — Model dữ liệu thành viên
- `app/Models/MemberSensitive.php` [Model] — Model dữ liệu thành viên
- `app/Models/OrgMembership.php` [Model] — Model dữ liệu thành viên
- `app/Models/User.php` [Model] — Model dữ liệu thành viên
- `app/Models/UserDepartmentFeature.php` [Model] — Model dữ liệu thành viên
- `app/Notifications/MemberApprovalResultNotification.php` [PHP-Other] — PHP-Other: Member Approval Result Notification
- `app/Notifications/PendingMemberNotification.php` [PHP-Other] — PHP-Other: Pending Member Notification
- `app/Observers/OrgMembershipObserver.php` [PHP-Other] — PHP-Other: Org Membership Observer
- `app/Policies/PortalMemberPolicy.php` [Policy] — Policy phân quyền: Portal Member Policy
- `database/factories/MemberFactory.php` [PHP-Other] — PHP-Other: Member Factory
- `database/factories/UserFactory.php` [PHP-Other] — PHP-Other: User Factory
- `database/migrations/0001_01_01_000000_create_users_table.php` [Migration] — Migration DB: 0001 01 01 000000 create users table
- `database/migrations/2026_02_26_050138_create_members_table.php` [Migration] — Migration DB: 2026 02 26 050138 create members table
- `database/migrations/2026_02_26_050139_create_member_sensitives_table.php` [Migration] — Migration DB: 2026 02 26 050139 create member sensitives table
- `database/migrations/2026_02_26_050144_create_org_memberships_table.php` [Migration] — Migration DB: 2026 02 26 050144 create org memberships table
- `database/migrations/2026_02_27_040333_expand_members_and_sensitives_tables.php` [Migration] — Migration DB: 2026 02 27 040333 expand members and sensitives tables
- `database/migrations/2026_02_27_040349_create_member_modules_tables.php` [Migration] — Migration DB: 2026 02 27 040349 create member modules tables
- `database/migrations/2026_03_01_125619_add_portal_member_permissions.php` [Migration] — Migration DB: 2026 03 01 125619 add portal member permissions
- `database/migrations/2026_03_06_092438_add_permissions_to_org_memberships_table.php` [Migration] — Migration DB: 2026 03 06 092438 add permissions to org memberships table
- `database/migrations/2026_03_06_171215_create_user_department_features_table.php` [Migration] — Migration DB: 2026 03 06 171215 create user department features table
- *(... và 29 files khác)*

### 📋 ATTENDANCE (49 files)

- `app/Console/Commands/BackfillAttendanceCount.php` [PHP-Other] — PHP-Other: Backfill Attendance Count
- `app/Exports/AttendanceTemplateExport.php` [PHP-Other] — PHP-Other: Attendance Template Export
- `app/Exports/MeetingExport.php` [PHP-Other] — PHP-Other: Meeting Export
- `app/Exports/MeetingsExport.php` [PHP-Other] — PHP-Other: Meetings Export
- `app/Http/Controllers/MeetingController.php` [Controller] — Controller: Meeting Controller
- `app/Http/Controllers/Portal/AttendanceController.php` [PortalController] — Controller điểm danh sinh hoạt
- `app/Imports/AttendanceImport.php` [PHP-Other] — PHP-Other: Attendance Import
- `app/Imports/MeetingsImport.php` [PHP-Other] — PHP-Other: Meetings Import
- `app/Models/DeaconAttendanceRecord.php` [Model] — Model dữ liệu điểm danh
- `app/Models/DepartmentMeeting.php` [Model] — Model dữ liệu điểm danh
- `app/Models/Meeting.php` [Model] — Model dữ liệu điểm danh
- `app/Models/MeetingAttendance.php` [Model] — Model dữ liệu điểm danh
- `app/Models/MeetingAttendanceSummary.php` [Model] — Model dữ liệu điểm danh
- `app/Models/MeetingFinance.php` [Model] — Model dữ liệu điểm danh
- `app/Models/MeetingPersonnel.php` [Model] — Model dữ liệu điểm danh
- `app/Models/MeetingReport.php` [Model] — Model dữ liệu điểm danh
- `app/Policies/AttendancePolicy.php` [Policy] — Policy phân quyền: Attendance Policy
- `app/Services/MeetingService.php` [Service] — Service xử lý logic điểm danh
- `database/migrations/2026_02_28_151703_create_meetings_table.php` [Migration] — Migration DB: 2026 02 28 151703 create meetings table
- `database/migrations/2026_02_28_151704_create_meeting_personnels_table.php` [Migration] — Migration DB: 2026 02 28 151704 create meeting personnels table
- `database/migrations/2026_02_28_151830_create_meeting_reports_table.php` [Migration] — Migration DB: 2026 02 28 151830 create meeting reports table
- `database/migrations/2026_02_28_152033_create_meeting_finances_table.php` [Migration] — Migration DB: 2026 02 28 152033 create meeting finances table
- `database/migrations/2026_02_28_234655_add_speaker_id_to_meetings_table.php` [Migration] — Migration DB: 2026 02 28 234655 add speaker id to meetings table
- `database/migrations/2026_03_01_111309_create_meeting_attendance_summaries_table.php` [Migration] — Migration DB: 2026 03 01 111309 create meeting attendance summaries table
- `database/migrations/2026_03_01_111310_create_meeting_attendances_table.php` [Migration] — Migration DB: 2026 03 01 111310 create meeting attendances table
- `database/migrations/2026_03_01_111443_add_attendance_permissions.php` [Migration] — Migration DB: 2026 03 01 111443 add attendance permissions
- `database/migrations/2026_03_01_123002_add_department_attendance_fields_to_meeting_attendances_table.php` [Migration] — Migration DB: 2026 03 01 123002 add department attendance fields to meeting attendances table
- `database/migrations/2026_03_05_155150_add_attendance_marked_to_meetings_table.php` [Migration] — Migration DB: 2026 03 05 155150 add attendance marked to meetings table
- `database/migrations/2026_03_05_200001_create_deacon_attendance_records_table.php` [Migration] — Migration DB: 2026 03 05 200001 create deacon attendance records table
- `database/migrations/2026_03_09_014336_add_quiz_passage_to_meetings_table.php` [Migration] — Migration DB: 2026 03 09 014336 add quiz passage to meetings table
- *(... và 19 files khác)*

### 📅 ASSIGNMENTS (25 files)

- `app/Http/Controllers/DutyRosterController.php` [Controller] — Controller: Duty Roster Controller
- `app/Models/DutyAssignment.php` [Model] — Model: Duty Assignment
- `app/Models/FeatureAssignment.php` [Model] — Model: Feature Assignment
- `app/Models/RosterTemplate.php` [Model] — Model: Roster Template
- `app/Models/RosterTemplateRole.php` [Model] — Model: Roster Template Role
- `app/Notifications/DutyAssignedNotification.php` [PHP-Other] — PHP-Other: Duty Assigned Notification
- `app/Services/FeatureAssignmentService.php` [Service] — Service: Feature Assignment Service
- `database/migrations/2026_03_10_031013_create_duty_assignments_table.php` [Migration] — Migration DB: 2026 03 10 031013 create duty assignments table
- `database/migrations/2026_03_10_033021_create_roster_templates_table.php` [Migration] — Migration DB: 2026 03 10 033021 create roster templates table
- `database/migrations/2026_03_10_033022_create_roster_template_roles_table.php` [Migration] — Migration DB: 2026 03 10 033022 create roster template roles table
- `database/migrations/2026_03_10_041501_add_slot_to_duty_assignments_table.php` [Migration] — Migration DB: 2026 03 10 041501 add slot to duty assignments table
- `database/migrations/2026_03_12_113516_add_type_to_roster_templates_table.php` [Migration] — Migration DB: 2026 03 12 113516 add type to roster templates table
- `database/migrations/2026_03_15_083604_add_status_and_reason_to_duty_assignments_table.php` [Migration] — Migration DB: 2026 03 15 083604 add status and reason to duty assignments table
- `database/migrations/2026_03_16_025646_add_scope_to_feature_assignment_tables.php` [Migration] — Migration DB: 2026 03 16 025646 add scope to feature assignment tables
- `database/seeders/DutyRosterSeeder.php` [Seeder] — Seeder dữ liệu: Duty Roster Seeder
- `public/build/assets/Assignments-Bee3vHkc.js` [JavaScript] — JavaScript: Assignments Bee3v Hkc
- `public/build/assets/DutyRoster-Dmoqs8IV.js` [JavaScript] — JavaScript: Duty Roster Dmoqs8IV
- `resources/js/Layouts/DutyRosterLayout.vue` [VueLayout] — Layout Vue: Duty Roster Layout
- `resources/js/Pages/Docs/Departments/Assignments.vue` [VuePage] — Trang Vue: Assignments
- `resources/js/Pages/Docs/DutyRoster.vue` [VuePage] — Trang Vue: Duty Roster
- `resources/js/Pages/DutyRoster/HolisticView.vue` [VuePage] — Trang Vue: Holistic View
- `resources/js/Pages/DutyRoster/Show.vue` [VuePage] — Trang Vue: Show
- `resources/js/Pages/DutyRoster/Templates/Create.vue` [VuePage] — Trang Vue: Create
- `resources/js/Pages/DutyRoster/Templates/Index.vue` [VuePage] — Trang Vue: Index
- `resources/js/Pages/DutyRoster/Templates/Show.vue` [VuePage] — Trang Vue: Show

### 💰 FINANCE (42 files)

- `app/Http/Controllers/Admin/DonationController.php` [AdminController] — Controller quản lý tài chính (Admin)
- `app/Http/Controllers/Portal/DeptFinanceController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Controllers/Portal/FinanceFundController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Controllers/Portal/FinanceFundTransferController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Controllers/Portal/FinancePortalController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Controllers/Portal/FinanceReportController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Controllers/Portal/FinanceTransactionController.php` [PortalController] — Controller tài chính phòng ban
- `app/Http/Middleware/EnsureFinanceContext.php` [Middleware] — Middleware: Ensure Finance Context
- `app/Models/DepartmentFund.php` [Model] — Model dữ liệu tài chính
- `app/Models/DepartmentTransaction.php` [Model] — Model dữ liệu tài chính
- `app/Models/Donation.php` [Model] — Model dữ liệu tài chính
- `app/Models/EduClassFund.php` [Model] — Model dữ liệu tài chính
- `app/Models/EduClassTransaction.php` [Model] — Model dữ liệu tài chính
- `app/Models/FinanceFund.php` [Model] — Model dữ liệu tài chính
- `app/Models/FinanceTransaction.php` [Model] — Model dữ liệu tài chính
- `app/Models/Fund.php` [Model] — Model dữ liệu tài chính
- `app/Models/FundTransfer.php` [Model] — Model dữ liệu tài chính
- `app/Notifications/FinanceTransferNotification.php` [PHP-Other] — PHP-Other: Finance Transfer Notification
- `app/Policies/DepartmentFinancePolicy.php` [Policy] — Policy phân quyền: Department Finance Policy
- `app/Policies/FinanceFundPolicy.php` [Policy] — Policy phân quyền: Finance Fund Policy
- `app/Policies/FinancePolicy.php` [Policy] — Policy phân quyền: Finance Policy
- `app/Policies/FinanceTransactionPolicy.php` [Policy] — Policy phân quyền: Finance Transaction Policy
- `database/migrations/2026_03_02_051027_create_finance_funds_table.php` [Migration] — Migration DB: 2026 03 02 051027 create finance funds table
- `database/migrations/2026_03_02_051028_add_finance_permissions.php` [Migration] — Migration DB: 2026 03 02 051028 add finance permissions
- `database/migrations/2026_03_02_051028_create_finance_transactions_table.php` [Migration] — Migration DB: 2026 03 02 051028 create finance transactions table
- `database/migrations/2026_03_02_200001_finance_overhaul_add_tables.php` [Migration] — Migration DB: 2026 03 02 200001 finance overhaul add tables
- `database/migrations/2026_03_02_210001_create_department_finance_tables.php` [Migration] — Migration DB: 2026 03 02 210001 create department finance tables
- `database/migrations/2026_03_10_161132_create_funds_table.php` [Migration] — Migration DB: 2026 03 10 161132 create funds table
- `database/migrations/2026_03_10_161133_create_donations_table.php` [Migration] — Migration DB: 2026 03 10 161133 create donations table
- `database/seeders/ThanhTrangDeptFinanceSeeder.php` [Seeder] — Seeder dữ liệu: Thanh Trang Dept Finance Seeder
- *(... và 12 files khác)*

### ✝️ MINISTRY (16 files)

- `app/Console/Commands/DeleteEducationFeatures.php` [PHP-Other] — PHP-Other: Delete Education Features
- `app/Http/Controllers/MinistryPortalController.php` [Controller] — Controller: Ministry Portal Controller
- `app/Http/Controllers/Portal/EducationController.php` [PortalController] — Controller Portal: Education Controller
- `app/Http/Middleware/EnsureMinistryContext.php` [Middleware] — Middleware: Ensure Ministry Context
- `app/Models/EduClass.php` [Model] — Model: Edu Class
- `app/Policies/EduClassPolicy.php` [Policy] — Policy phân quyền: Edu Class Policy
- `database/migrations/2026_03_03_222600_add_class_type_to_edu_classes.php` [Migration] — Migration DB: 2026 03 03 222600 add class type to edu classes
- `public/build/assets/Education-C6FxebPf.js` [JavaScript] — JavaScript: Education C6Fxeb Pf
- `resources/js/Pages/Docs/Education.vue` [VuePage] — Trang Vue: Education
- `resources/js/Pages/Ministry/Dashboard.vue` [VuePage-Ministry] — Trang Vue (Mục Vụ): Dashboard
- `resources/js/Pages/Ministry/Visitation/Index.vue` [VuePage-Ministry] — Trang Vue (Mục Vụ): Index
- `resources/js/Pages/Portal/Education/Dashboard.vue` [VuePage-Portal] — Trang Vue (Portal): Dashboard
- `resources/js/Pages/Portal/Education/Index.vue` [VuePage-Portal] — Trang Vue (Portal): Index
- `resources/js/Pages/Portal/Education/Report.vue` [VuePage-Portal] — Trang Vue (Portal): Report
- `routes/ministry.php` [Routes] — Định nghĩa Routes: ministry
- `tests/Feature/Ministry/VisitationTest.php` [PHP-Other] — PHP-Other: Visitation Test

### 📖 CHRONICLES (18 files)

- `app/Http/Controllers/Admin/ActivityLogController.php` [AdminController] — Controller Admin: Activity Log Controller
- `app/Http/Controllers/Admin/ChronicleController.php` [AdminController] — Controller Admin: Chronicle Controller
- `app/Http/Controllers/Portal/ChronicleController.php` [PortalController] — Controller sổ tay hội thánh
- `app/Models/CareLog.php` [Model] — Model: Care Log
- `app/Models/ChronicleEntry.php` [Model] — Model: Chronicle Entry
- `config/activitylog.php` [Config] — Config: activitylog
- `config/logging.php` [Config] — Config: logging
- `database/migrations/2026_02_26_050108_create_activity_log_table.php` [Migration] — Migration DB: 2026 02 26 050108 create activity log table
- `database/migrations/2026_02_26_050109_add_event_column_to_activity_log_table.php` [Migration] — Migration DB: 2026 02 26 050109 add event column to activity log table
- `database/migrations/2026_02_26_050110_add_batch_uuid_column_to_activity_log_table.php` [Migration] — Migration DB: 2026 02 26 050110 add batch uuid column to activity log table
- `database/migrations/2026_03_16_103921_create_chronicle_entries_table.php` [Migration] — Migration DB: 2026 03 16 103921 create chronicle entries table
- `database/migrations/2026_03_16_140208_add_department_id_to_chronicles.php` [Migration] — Migration DB: 2026 03 16 140208 add department id to chronicles
- `public/build/assets/ActivityLogItem-D8UmSp-M.js` [JavaScript] — JavaScript: Activity Log Item D8Um Sp M
- `public/build/assets/ActivityLogs-BqMbUhsW.js` [JavaScript] — JavaScript: Activity Logs Bq Mb Uhs W
- `resources/js/Components/ActivityLogItem.vue` [VueComponent] — Component Vue: Activity Log Item
- `resources/js/Pages/Admin/ActivityLogs/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Admin/Chronicles/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Portal/ActivityLogs.vue` [VuePage-Portal] — Trang Vue (Portal): Activity Logs

### 🤝 CARE (23 files)

- `app/Http/Controllers/Admin/VisitorController.php` [AdminController] — Controller quản lý chăm sóc mục vụ (Admin)
- `app/Http/Controllers/CareController.php` [Controller] — Controller: Care Controller
- `app/Http/Controllers/Portal/ActivitiesVisitationController.php` [PortalController] — Controller chăm sóc mục vụ
- `app/Http/Controllers/Portal/VisitationController.php` [PortalController] — Controller chăm sóc mục vụ
- `app/Models/CareRequest.php` [Model] — Model: Care Request
- `app/Models/Visitation.php` [Model] — Model: Visitation
- `app/Models/VisitationReason.php` [Model] — Model: Visitation Reason
- `app/Models/Visitor.php` [Model] — Model: Visitor
- `app/Models/VisitorFollowup.php` [Model] — Model: Visitor Followup
- `app/Policies/VisitationPolicy.php` [Policy] — Policy phân quyền: Visitation Policy
- `database/migrations/2026_03_01_151255_create_visitations_table.php` [Migration] — Migration DB: 2026 03 01 151255 create visitations table
- `database/migrations/2026_03_02_151931_add_advanced_visitation_columns_to_tables.php` [Migration] — Migration DB: 2026 03 02 151931 add advanced visitation columns to tables
- `database/migrations/2026_03_06_032611_add_status_priority_to_visitations_table.php` [Migration] — Migration DB: 2026 03 06 032611 add status priority to visitations table
- `database/migrations/2026_03_10_155015_create_care_requests_table.php` [Migration] — Migration DB: 2026 03 10 155015 create care requests table
- `database/migrations/2026_03_10_160559_create_visitors_table.php` [Migration] — Migration DB: 2026 03 10 160559 create visitors table
- `database/migrations/2026_03_10_160600_create_visitor_followups_table.php` [Migration] — Migration DB: 2026 03 10 160600 create visitor followups table
- `database/migrations/2026_03_12_060331_create_visitation_reasons_table.php` [Migration] — Migration DB: 2026 03 12 060331 create visitation reasons table
- `database/migrations/2026_03_12_060332_change_reason_column_in_visitations.php` [Migration] — Migration DB: 2026 03 12 060332 change reason column in visitations
- `database/migrations/2026_03_17_072408_add_department_id_to_care_requests_table.php` [Migration] — Migration DB: 2026 03 17 072408 add department id to care requests table
- `resources/js/Pages/Admin/Visitors/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Care/Index.vue` [VuePage] — Trang Vue: Index
- `resources/js/Pages/Docs/Departments/Visitation.vue` [VuePage] — Trang Vue: Visitation
- `resources/js/Pages/Portal/Visitation/Index.vue` [VuePage-Portal] — Trang Vue (Portal): Index

### 📄 DOCUMENTS (104 files)

- `app/Http/Controllers/Admin/AssetController.php` [AdminController] — Controller quản lý tài liệu (Admin)
- `app/Http/Controllers/DocumentController.php` [Controller] — Controller: Document Controller
- `app/Models/Asset.php` [Model] — Model: Asset
- `app/Models/AssetLoan.php` [Model] — Model: Asset Loan
- `app/Models/Document.php` [Model] — Model: Document
- `config/filesystems.php` [Config] — Config: filesystems
- `database/migrations/2026_03_10_152735_create_documents_table.php` [Migration] — Migration DB: 2026 03 10 152735 create documents table
- `database/migrations/2026_03_10_155906_create_assets_table.php` [Migration] — Migration DB: 2026 03 10 155906 create assets table
- `public/build/assets/AdminPortalLayout-zrp31gtw.js` [JavaScript] — JavaScript: Admin Portal Layout zrp31gtw
- `public/build/assets/AppCard-CPbP9tOB.js` [JavaScript] — JavaScript: App Card CPb P9t OB
- `public/build/assets/BatchEntry-lFCC_pYd.js` [JavaScript] — JavaScript: Batch Entry l FCC p Yd
- `public/build/assets/Create-C5d-GhEf.js` [JavaScript] — JavaScript: Create C5d Gh Ef
- `public/build/assets/Dashboard-CAUPQg60.js` [JavaScript] — JavaScript: Dashboard CAUPQg60
- `public/build/assets/Dashboard-CPRcEjfb.js` [JavaScript] — JavaScript: Dashboard CPRc Ejfb
- `public/build/assets/Dashboard-CUQLBTey.js` [JavaScript] — JavaScript: Dashboard CUQLBTey
- `public/build/assets/Dashboard-gfTVYx3o.js` [JavaScript] — JavaScript: Dashboard gf TVYx3o
- `public/build/assets/Dashboard-mUOntCD0.js` [JavaScript] — JavaScript: Dashboard m UOnt CD0
- `public/build/assets/DataToolbar-D0oadrXa.js` [JavaScript] — JavaScript: Data Toolbar D0oadr Xa
- `public/build/assets/DepartmentForm-BITbpBHw.js` [JavaScript] — JavaScript: Department Form BITbp BHw
- `public/build/assets/DocsLayout-DK5iTiRh.js` [JavaScript] — JavaScript: Docs Layout DK5i Ti Rh
- `public/build/assets/Dropdown-D5uUIwiD.js` [JavaScript] — JavaScript: Dropdown D5u UIwi D
- `public/build/assets/FaithJourneyTimeline-DqvwzFOp.js` [JavaScript] — JavaScript: Faith Journey Timeline Dqvwz FOp
- `public/build/assets/Features-B28VT9dK.js` [JavaScript] — JavaScript: Features B28VT9d K
- `public/build/assets/FeaturesTab-Ct-30h7G.js` [JavaScript] — JavaScript: Features Tab Ct 30h7G
- `public/build/assets/FormModal-DkowqYLk.js` [JavaScript] — JavaScript: Form Modal Dkowq YLk
- `public/build/assets/HolisticView-CR3Fjuro.js` [JavaScript] — JavaScript: Holistic View CR3Fjuro
- `public/build/assets/Index-0k7guybI.js` [JavaScript] — JavaScript: Index 0k7guyb I
- `public/build/assets/Index-40cv_aQv.js` [JavaScript] — JavaScript: Index 40cv a Qv
- `public/build/assets/Index-B5GR8VyL.js` [JavaScript] — JavaScript: Index B5GR8Vy L
- `public/build/assets/Index-B9RrXo_O.js` [JavaScript] — JavaScript: Index B9Rr Xo O
- *(... và 74 files khác)*

### 📊 REPORTS (19 files)

- `app/Http/Controllers/Portal/DeptReportController.php` [PortalController] — Controller Portal: Dept Report Controller
- `app/Http/Middleware/EnsurePortalContext.php` [Middleware] — Middleware: Ensure Portal Context
- `app/Models/DeaconMonthlyReport.php` [Model] — Model: Deacon Monthly Report
- `app/Models/DeaconReportIncident.php` [Model] — Model: Deacon Report Incident
- `app/Models/DepartmentReport.php` [Model] — Model: Department Report
- `app/Models/EduReport.php` [Model] — Model: Edu Report
- `app/Notifications/ReportApprovedNotification.php` [PHP-Other] — PHP-Other: Report Approved Notification
- `app/Notifications/ReportSubmittedNotification.php` [PHP-Other] — PHP-Other: Report Submitted Notification
- `app/Policies/DepartmentReportPolicy.php` [Policy] — Policy phân quyền: Department Report Policy
- `database/migrations/2026_03_03_000001_create_department_reports_table.php` [Migration] — Migration DB: 2026 03 03 000001 create department reports table
- `database/migrations/2026_03_04_110000_create_edu_reports_table.php` [Migration] — Migration DB: 2026 03 04 110000 create edu reports table
- `database/migrations/2026_03_05_200002_create_deacon_monthly_reports_table.php` [Migration] — Migration DB: 2026 03 05 200002 create deacon monthly reports table
- `database/migrations/2026_03_05_200003_create_deacon_report_incidents_table.php` [Migration] — Migration DB: 2026 03 05 200003 create deacon report incidents table
- `database/migrations/2026_03_05_210000_add_report_fields_to_deacon_monthly_reports.php` [Migration] — Migration DB: 2026 03 05 210000 add report fields to deacon monthly reports
- `database/migrations/2026_03_06_063048_add_unlock_requested_to_deacon_reports.php` [Migration] — Migration DB: 2026 03 06 063048 add unlock requested to deacon reports
- `resources/js/Pages/Deacon/Report.vue` [VuePage] — Trang Vue: Report
- `resources/js/Pages/Docs/Departments/Reports.vue` [VuePage] — Trang Vue: Reports
- `resources/js/Pages/Portal/Reports/Index.vue` [VuePage-Portal] — Trang Vue (Portal): Index
- `resources/views/pdf/dept-report.blade.php` [PHP-Other] — PHP-Other: dept report.blade

### 📢 BROADCAST (9 files)

- `app/Http/Controllers/Admin/BroadcastController.php` [AdminController] — Controller Admin: Broadcast Controller
- `app/Http/Controllers/NotificationController.php` [Controller] — Controller: Notification Controller
- `app/Jobs/SendBroadcastEmail.php` [Job] — Background Job: Send Broadcast Email
- `app/Models/EmailBroadcast.php` [Model] — Model: Email Broadcast
- `database/migrations/2026_03_10_151416_create_notifications_table.php` [Migration] — Migration DB: 2026 03 10 151416 create notifications table
- `database/migrations/2026_03_10_162141_create_email_broadcasts_table.php` [Migration] — Migration DB: 2026 03 10 162141 create email broadcasts table
- `resources/js/Components/NotificationDropdown.vue` [VueComponent] — Component Vue: Notification Dropdown
- `resources/js/Pages/Admin/Broadcasts/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Notifications/Index.vue` [VuePage] — Trang Vue: Index

### 🏢 ORGANIZATION (17 files)

- `app/Http/Controllers/DepartmentController.php` [Controller] — Controller: Department Controller
- `app/Http/Controllers/DepartmentPortalController.php` [Controller] — Controller: Department Portal Controller
- `app/Models/Department.php` [Model] — Model: Department
- `app/Models/DepartmentSupervisor.php` [Model] — Model: Department Supervisor
- `app/Models/Team.php` [Model] — Model: Team
- `app/Policies/DepartmentPortalPolicy.php` [Policy] — Policy phân quyền: Department Portal Policy
- `app/Traits/Traits/HasDepartmentScope.php` [PHP-Other] — PHP-Other: Has Department Scope
- `database/migrations/2026_02_26_050140_create_departments_table.php` [Migration] — Migration DB: 2026 02 26 050140 create departments table
- `database/migrations/2026_02_26_050142_create_teams_table.php` [Migration] — Migration DB: 2026 02 26 050142 create teams table
- `database/migrations/2026_02_26_050143_create_department_supervisors_table.php` [Migration] — Migration DB: 2026 02 26 050143 create department supervisors table
- `database/migrations/2026_02_27_032559_add_parent_id_to_departments_table.php` [Migration] — Migration DB: 2026 02 27 032559 add parent id to departments table
- `database/seeders/OrgStructureSeeder.php` [Seeder] — Seeder dữ liệu: Org Structure Seeder
- `resources/js/Pages/Departments/Index.vue` [VuePage] — Trang Vue: Index
- `resources/js/Pages/Departments/Partials/DepartmentForm.vue` [VuePage] — Trang Vue: Department Form
- `resources/js/Pages/Departments/Show.vue` [VuePage] — Trang Vue: Show
- `resources/js/Pages/Departments/Tabs/TeamsTab.vue` [VuePage] — Trang Vue: Teams Tab
- `resources/js/Pages/Docs/Departments/Intro.vue` [VuePage] — Trang Vue: Intro

### 📝 FORMS (6 files)

- `app/Http/Controllers/Admin/FormTemplateController.php` [AdminController] — Controller Admin: Form Template Controller
- `app/Models/FormTemplate.php` [Model] — Model: Form Template
- `database/migrations/2026_03_17_071204_create_form_templates_table.php` [Migration] — Migration DB: 2026 03 17 071204 create form templates table
- `database/migrations/2026_03_29_161346_add_performance_indexes_to_core_tables.php` [Migration] — Migration DB: 2026 03 29 161346 add performance indexes to core tables
- `resources/js/Pages/Admin/Forms/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Speakers/Partials/SpeakerForm.vue` [VuePage] — Trang Vue: Speaker Form

### 🏠 PORTAL (13 files)

- `app/Console/Commands/SeedPortalTestData.php` [PHP-Other] — PHP-Other: Seed Portal Test Data
- `app/Http/Controllers/DashboardController.php` [Controller] — Controller: Dashboard Controller
- `app/Http/Controllers/Portal/DeaconPortalController.php` [PortalController] — Controller Portal: Deacon Portal Controller
- `app/Services/PortalService.php` [Service] — Service: Portal Service
- `resources/js/Layouts/AdminPortalLayout.vue` [VueLayout] — Layout Vue: Admin Portal Layout
- `resources/js/Layouts/DocsLayout.vue` [VueLayout] — Layout Vue: Docs Layout
- `resources/js/Layouts/MobileLayout.vue` [VueLayout] — Layout Vue: Mobile Layout
- `resources/js/Layouts/PortalLayout.vue` [VueLayout] — Layout Vue: Portal Layout
- `resources/js/Pages/Dashboard.vue` [VuePage] — Trang Vue: Dashboard
- `resources/js/Pages/Docs/Portals.vue` [VuePage] — Trang Vue: Portals
- `resources/js/Pages/Docs/Portals/Intro.vue` [VuePage] — Trang Vue: Intro
- `resources/js/Pages/Portal/Dashboard.vue` [VuePage-Portal] — Trang Vue (Portal): Dashboard
- `routes/portal.php` [Routes] — Định nghĩa Routes: portal

### ⚙️ ADMIN (14 files)

- `app/Http/Controllers/Admin/AnnouncementController.php` [AdminController] — Controller Admin: Announcement Controller
- `app/Http/Middleware/EnsureSuperAdmin.php` [Middleware] — Middleware: Ensure Super Admin
- `config/app.php` [Config] — Config: app
- `config/cache.php` [Config] — Config: cache
- `config/database.php` [Config] — Config: database
- `config/mail.php` [Config] — Config: mail
- `config/queue.php` [Config] — Config: queue
- `config/services.php` [Config] — Config: services
- `database/seeders/SystemSkeletonSeeder.php` [Seeder] — Seeder dữ liệu: System Skeleton Seeder
- `postcss.config.js` [JavaScript] — JavaScript: postcss.config
- `resources/js/Pages/Admin/Announcements/Index.vue` [VuePage-Admin] — Trang Vue (Admin): Index
- `resources/js/Pages/Docs/SysAdmin.vue` [VuePage] — Trang Vue: Sys Admin
- `tailwind.config.js` [JavaScript] — JavaScript: tailwind.config
- `vite.config.js` [JavaScript] — JavaScript: vite.config

### 📁 GENERAL (109 files)

- `app/Console/Commands/BackfillMemoryVerseCount.php` [PHP-Other] — PHP-Other: Backfill Memory Verse Count
- `app/Console/Commands/BackupDatabase.php` [PHP-Other] — PHP-Other: Backup Database
- `app/Console/Commands/IndexCodebase.php` [PHP-Other] — PHP-Other: Index Codebase
- `app/Console/Commands/SeedDeaconData.php` [PHP-Other] — PHP-Other: Seed Deacon Data
- `app/Http/Controllers/CalendarController.php` [Controller] — Controller: Calendar Controller
- `app/Http/Controllers/Controller.php` [Controller] — Controller: Controller
- `app/Http/Controllers/DocsController.php` [Controller] — Controller: Docs Controller
- `app/Http/Controllers/FaithJourneyController.php` [Controller] — Controller: Faith Journey Controller
- `app/Http/Controllers/HouseholdController.php` [Controller] — Controller: Household Controller
- `app/Http/Controllers/SearchController.php` [Controller] — Controller: Search Controller
- `app/Http/Controllers/SpeakerController.php` [Controller] — Controller: Speaker Controller
- `app/Http/Controllers/WelcomeController.php` [Controller] — Controller: Welcome Controller
- `app/Http/Middleware/EnsureDeaconContext.php` [Middleware] — Middleware: Ensure Deacon Context
- `app/Http/Middleware/HandleInertiaRequests.php` [Middleware] — Middleware: Handle Inertia Requests
- `app/Mail/NewsletterEmail.php` [PHP-Other] — PHP-Other: Newsletter Email
- `app/Models/Announcement.php` [Model] — Model: Announcement
- `app/Models/ApprovalRequest.php` [Model] — Model: Approval Request
- `app/Models/Church.php` [Model] — Model: Church
- `app/Models/Course.php` [Model] — Model: Course
- `app/Models/Event.php` [Model] — Model: Event
- `app/Models/FaithJourney.php` [Model] — Model: Faith Journey
- `app/Models/Household.php` [Model] — Model: Household
- `app/Models/Module.php` [Model] — Model: Module
- `app/Models/Relationship.php` [Model] — Model: Relationship
- `app/Models/Speaker.php` [Model] — Model: Speaker
- `app/Models/Talent.php` [Model] — Model: Talent
- `app/Providers/AppServiceProvider.php` [PHP-Other] — PHP-Other: App Service Provider
- `app/Services/ScopeResolver.php` [Service] — Service: Scope Resolver
- `app/Traits/HasDataScope.php` [PHP-Other] — PHP-Other: Has Data Scope
- `bootstrap/app.php` [PHP-Other] — PHP-Other: app
- *(... và 79 files khác)*

---

## Index Theo Layer (Architecture)

### Middleware (9 files)

- `app/Http/Middleware/CheckFeatureAccess.php`
- `app/Http/Middleware/CheckPortalAccess.php`
- `app/Http/Middleware/EnsureDeaconContext.php`
- `app/Http/Middleware/EnsureFinanceContext.php`
- `app/Http/Middleware/EnsureMinistryContext.php`
- `app/Http/Middleware/EnsurePortalContext.php`
- `app/Http/Middleware/EnsureSuperAdmin.php`
- `app/Http/Middleware/HandleInertiaRequests.php`
- `app/Http/Middleware/PortalAccessMiddleware.php`

### Routes (4 files)

- `routes/console.php`
- `routes/ministry.php`
- `routes/portal.php`
- `routes/web.php`

### AdminController (10 files)

- `app/Http/Controllers/Admin/ActivityLogController.php`
- `app/Http/Controllers/Admin/AnnouncementController.php`
- `app/Http/Controllers/Admin/AssetController.php`
- `app/Http/Controllers/Admin/BroadcastController.php`
- `app/Http/Controllers/Admin/ChronicleController.php`
- `app/Http/Controllers/Admin/DonationController.php`
- `app/Http/Controllers/Admin/FormTemplateController.php`
- `app/Http/Controllers/Admin/SystemFeatureController.php`
- `app/Http/Controllers/Admin/UserPermissionController.php`
- `app/Http/Controllers/Admin/VisitorController.php`

### PortalController (14 files)

- `app/Http/Controllers/Portal/ActivitiesVisitationController.php`
- `app/Http/Controllers/Portal/AttendanceController.php`
- `app/Http/Controllers/Portal/ChronicleController.php`
- `app/Http/Controllers/Portal/DeaconPortalController.php`
- `app/Http/Controllers/Portal/DeptFinanceController.php`
- `app/Http/Controllers/Portal/DeptReportController.php`
- `app/Http/Controllers/Portal/EducationController.php`
- `app/Http/Controllers/Portal/FinanceFundController.php`
- `app/Http/Controllers/Portal/FinanceFundTransferController.php`
- `app/Http/Controllers/Portal/FinancePortalController.php`
- `app/Http/Controllers/Portal/FinanceReportController.php`
- `app/Http/Controllers/Portal/FinanceTransactionController.php`
- `app/Http/Controllers/Portal/PortalMemberController.php`
- `app/Http/Controllers/Portal/VisitationController.php`

### AuthController (3 files)

- `app/Http/Controllers/Auth/AuthController.php`
- `app/Http/Controllers/Auth/NewPasswordController.php`
- `app/Http/Controllers/Auth/PasswordResetLinkController.php`

### Controller (22 files)

- `app/Http/Controllers/CalendarController.php`
- `app/Http/Controllers/CareController.php`
- `app/Http/Controllers/Controller.php`
- `app/Http/Controllers/DashboardController.php`
- `app/Http/Controllers/DepartmentController.php`
- `app/Http/Controllers/DepartmentPortalController.php`
- `app/Http/Controllers/DocsController.php`
- `app/Http/Controllers/DocumentController.php`
- `app/Http/Controllers/DutyRosterController.php`
- `app/Http/Controllers/FaithJourneyController.php`
- `app/Http/Controllers/HouseholdController.php`
- `app/Http/Controllers/MeetingController.php`
- `app/Http/Controllers/MemberController.php`
- `app/Http/Controllers/MemberPortalController.php`
- `app/Http/Controllers/MinistryPortalController.php`
- `app/Http/Controllers/NotificationController.php`
- `app/Http/Controllers/RoleController.php`
- `app/Http/Controllers/SearchController.php`
- `app/Http/Controllers/SpeakerController.php`
- `app/Http/Controllers/User/DonationController.php`
- *(... và 2 files khác)*

### Service (4 files)

- `app/Services/FeatureAssignmentService.php`
- `app/Services/MeetingService.php`
- `app/Services/PortalService.php`
- `app/Services/ScopeResolver.php`

### Model (66 files)

- `app/Models/Announcement.php`
- `app/Models/ApprovalRequest.php`
- `app/Models/Asset.php`
- `app/Models/AssetLoan.php`
- `app/Models/CareLog.php`
- `app/Models/CareRequest.php`
- `app/Models/ChronicleEntry.php`
- `app/Models/Church.php`
- `app/Models/Course.php`
- `app/Models/DeaconAttendanceRecord.php`
- `app/Models/DeaconMonthlyReport.php`
- `app/Models/DeaconReportIncident.php`
- `app/Models/Department.php`
- `app/Models/DepartmentFund.php`
- `app/Models/DepartmentMeeting.php`
- `app/Models/DepartmentReport.php`
- `app/Models/DepartmentRole.php`
- `app/Models/DepartmentSupervisor.php`
- `app/Models/DepartmentTransaction.php`
- `app/Models/Document.php`
- *(... và 46 files khác)*

### Policy (10 files)

- `app/Policies/AttendancePolicy.php`
- `app/Policies/DepartmentFinancePolicy.php`
- `app/Policies/DepartmentPortalPolicy.php`
- `app/Policies/DepartmentReportPolicy.php`
- `app/Policies/EduClassPolicy.php`
- `app/Policies/FinanceFundPolicy.php`
- `app/Policies/FinancePolicy.php`
- `app/Policies/FinanceTransactionPolicy.php`
- `app/Policies/PortalMemberPolicy.php`
- `app/Policies/VisitationPolicy.php`

### VuePage-Admin (11 files)

- `resources/js/Pages/Admin/ActivityLogs/Index.vue`
- `resources/js/Pages/Admin/Announcements/Index.vue`
- `resources/js/Pages/Admin/Assets/Index.vue`
- `resources/js/Pages/Admin/Broadcasts/Index.vue`
- `resources/js/Pages/Admin/Chronicles/Index.vue`
- `resources/js/Pages/Admin/Finance/Donations/BatchEntry.vue`
- `resources/js/Pages/Admin/Finance/Donations/Index.vue`
- `resources/js/Pages/Admin/Forms/Index.vue`
- `resources/js/Pages/Admin/SystemFeaturesTab.vue`
- `resources/js/Pages/Admin/UserPermissions.vue`
- `resources/js/Pages/Admin/Visitors/Index.vue`

### VuePage-Portal (13 files)

- `resources/js/Pages/Portal/ActivityLogs.vue`
- `resources/js/Pages/Portal/Attendance/Index.vue`
- `resources/js/Pages/Portal/Attendance/Show.vue`
- `resources/js/Pages/Portal/Dashboard.vue`
- `resources/js/Pages/Portal/Education/Dashboard.vue`
- `resources/js/Pages/Portal/Education/Index.vue`
- `resources/js/Pages/Portal/Education/Report.vue`
- `resources/js/Pages/Portal/Education/Session.vue`
- `resources/js/Pages/Portal/Education/SessionList.vue`
- `resources/js/Pages/Portal/Finance/Index.vue`
- `resources/js/Pages/Portal/Members/Index.vue`
- `resources/js/Pages/Portal/Reports/Index.vue`
- `resources/js/Pages/Portal/Visitation/Index.vue`

### VuePage-Ministry (2 files)

- `resources/js/Pages/Ministry/Dashboard.vue`
- `resources/js/Pages/Ministry/Visitation/Index.vue`

### VuePage (64 files)

- `resources/js/Pages/Auth/ForgotPassword.vue`
- `resources/js/Pages/Auth/Login.vue`
- `resources/js/Pages/Auth/ResetPassword.vue`
- `resources/js/Pages/Calendar/Index.vue`
- `resources/js/Pages/Care/Index.vue`
- `resources/js/Pages/Dashboard.vue`
- `resources/js/Pages/Deacon/Attendance.vue`
- `resources/js/Pages/Deacon/AttendanceShow.vue`
- `resources/js/Pages/Deacon/Index.vue`
- `resources/js/Pages/Deacon/Report.vue`
- `resources/js/Pages/Departments/Index.vue`
- `resources/js/Pages/Departments/Partials/DepartmentForm.vue`
- `resources/js/Pages/Departments/Show.vue`
- `resources/js/Pages/Departments/Tabs/FeaturesTab.vue`
- `resources/js/Pages/Departments/Tabs/MembersTab.vue`
- `resources/js/Pages/Departments/Tabs/TeamsTab.vue`
- `resources/js/Pages/Docs/Admin/Features.vue`
- `resources/js/Pages/Docs/Admin/Permissions.vue`
- `resources/js/Pages/Docs/Admin/Users.vue`
- `resources/js/Pages/Docs/Auth.vue`
- *(... và 44 files khác)*

### VueLayout (6 files)

- `resources/js/Layouts/AdminPortalLayout.vue`
- `resources/js/Layouts/AuthenticatedLayout.vue`
- `resources/js/Layouts/DocsLayout.vue`
- `resources/js/Layouts/DutyRosterLayout.vue`
- `resources/js/Layouts/MobileLayout.vue`
- `resources/js/Layouts/PortalLayout.vue`

### VueComponent (21 files)

- `resources/js/Components/ActivityLogItem.vue`
- `resources/js/Components/AppCard.vue`
- `resources/js/Components/CommandPalette.vue`
- `resources/js/Components/DangerButton.vue`
- `resources/js/Components/DataToolbar.vue`
- `resources/js/Components/DeleteConfirmModal.vue`
- `resources/js/Components/Dropdown.vue`
- `resources/js/Components/DropdownLink.vue`
- `resources/js/Components/InputError.vue`
- `resources/js/Components/InputLabel.vue`
- `resources/js/Components/Member/FaithJourneyTimeline.vue`
- `resources/js/Components/Member/FamilyTreeCard.vue`
- `resources/js/Components/Modal.vue`
- `resources/js/Components/NotificationDropdown.vue`
- `resources/js/Components/Pagination.vue`
- `resources/js/Components/PrimaryButton.vue`
- `resources/js/Components/SearchableSelect.vue`
- `resources/js/Components/SecondaryButton.vue`
- `resources/js/Components/SlideOver.vue`
- `resources/js/Components/StatusBadge.vue`
- *(... và 1 files khác)*

### Migration (106 files)

- `database/migrations/0001_01_01_000000_create_users_table.php`
- `database/migrations/0001_01_01_000001_create_cache_table.php`
- `database/migrations/0001_01_01_000002_create_jobs_table.php`
- `database/migrations/2026_02_26_050106_create_permission_tables.php`
- `database/migrations/2026_02_26_050108_create_activity_log_table.php`
- `database/migrations/2026_02_26_050109_add_event_column_to_activity_log_table.php`
- `database/migrations/2026_02_26_050110_add_batch_uuid_column_to_activity_log_table.php`
- `database/migrations/2026_02_26_050137_create_churches_table.php`
- `database/migrations/2026_02_26_050138_create_members_table.php`
- `database/migrations/2026_02_26_050139_create_member_sensitives_table.php`
- `database/migrations/2026_02_26_050140_create_departments_table.php`
- `database/migrations/2026_02_26_050141_create_org_roles_table.php`
- `database/migrations/2026_02_26_050142_create_teams_table.php`
- `database/migrations/2026_02_26_050143_create_department_supervisors_table.php`
- `database/migrations/2026_02_26_050144_create_org_memberships_table.php`
- `database/migrations/2026_02_26_050145_create_approval_requests_table.php`
- `database/migrations/2026_02_26_050146_create_modules_table.php`
- `database/migrations/2026_02_27_032559_add_parent_id_to_departments_table.php`
- `database/migrations/2026_02_27_040333_expand_members_and_sensitives_tables.php`
- `database/migrations/2026_02_27_040349_create_member_modules_tables.php`
- *(... và 86 files khác)*

### Seeder (22 files)

- `database/seeders/AnalyticsDataSeeder.php`
- `database/seeders/BanThanhTrangSeeder.php`
- `database/seeders/DatabaseSeeder.php`
- `database/seeders/DemoDataSeeder.php`
- `database/seeders/DepartmentUserSeeder.php`
- `database/seeders/DeptActivitiesSeeder.php`
- `database/seeders/DutyRosterSeeder.php`
- `database/seeders/FeatureSeeder.php`
- `database/seeders/FoundationSeeder.php`
- `database/seeders/FullDemoSeeder.php`
- `database/seeders/InitialSeeder.php`
- `database/seeders/MemberDataSeeder.php`
- `database/seeders/MemberSeeder.php`
- `database/seeders/OrgStructureSeeder.php`
- `database/seeders/PermissionSampleDataSeeder.php`
- `database/seeders/PermissionSeeder.php`
- `database/seeders/RestoreFeaturesSeeder.php`
- `database/seeders/SystemSkeletonSeeder.php`
- `database/seeders/ThanhTrangDeptFinanceSeeder.php`
- `database/seeders/ThanhTrangFourMonthsSeeder.php`
- *(... và 2 files khác)*

### Job (1 files)

- `app/Jobs/SendBroadcastEmail.php`

### Config (12 files)

- `config/activitylog.php`
- `config/app.php`
- `config/auth.php`
- `config/cache.php`
- `config/database.php`
- `config/filesystems.php`
- `config/logging.php`
- `config/mail.php`
- `config/permission.php`
- `config/queue.php`
- `config/services.php`
- `config/session.php`

### Vibecode-Tool (11 files)

- `vibecode/__init__.py`
- `vibecode/__main__.py`
- `vibecode/cli.py`
- `vibecode/enricher.py`
- `vibecode/injector.py`
- `vibecode/linker.py`
- `vibecode/parser.py`
- `vibecode/scanner.py`
- `vibecode/store.py`
- `vibecode/ui_builder.py`
- `vibecode/watcher.py`

### PHP-Other (52 files)

- `app/Console/Commands/BackfillAttendanceCount.php`
- `app/Console/Commands/BackfillMemoryVerseCount.php`
- `app/Console/Commands/BackupDatabase.php`
- `app/Console/Commands/DeleteEducationFeatures.php`
- `app/Console/Commands/IndexCodebase.php`
- `app/Console/Commands/SeedDeaconData.php`
- `app/Console/Commands/SeedPortalTestData.php`
- `app/Console/Commands/SyncFeatures.php`
- `app/Console/Commands/SyncPermissions.php`
- `app/Exports/AttendanceTemplateExport.php`
- `app/Exports/MeetingExport.php`
- `app/Exports/MeetingsExport.php`
- `app/Exports/PortalMemberExport.php`
- `app/Imports/AttendanceImport.php`
- `app/Imports/MeetingsImport.php`
- `app/Imports/PortalMemberImport.php`
- `app/Mail/NewsletterEmail.php`
- `app/Notifications/DutyAssignedNotification.php`
- `app/Notifications/FinanceTransferNotification.php`
- `app/Notifications/MemberApprovalResultNotification.php`
- *(... và 32 files khác)*

### JavaScript (130 files)

- `postcss.config.js`
- `public/build/assets/ActivityLogItem-D8UmSp-M.js`
- `public/build/assets/ActivityLogs-BqMbUhsW.js`
- `public/build/assets/AdminPortalLayout-zrp31gtw.js`
- `public/build/assets/AppCard-CPbP9tOB.js`
- `public/build/assets/Assignments-Bee3vHkc.js`
- `public/build/assets/Attendance-7xaMKc1g.js`
- `public/build/assets/Attendance-DHgjOPva.js`
- `public/build/assets/AttendanceShow-DITRPlhy.js`
- `public/build/assets/Auth-DMlBpIDd.js`
- `public/build/assets/AuthenticatedLayout-D0KeaYO_.js`
- `public/build/assets/BatchEntry-lFCC_pYd.js`
- `public/build/assets/Create-C5d-GhEf.js`
- `public/build/assets/CreateMemberForm-9z61-OFq.js`
- `public/build/assets/Dashboard-CAUPQg60.js`
- `public/build/assets/Dashboard-CPRcEjfb.js`
- `public/build/assets/Dashboard-CUQLBTey.js`
- `public/build/assets/Dashboard-gfTVYx3o.js`
- `public/build/assets/Dashboard-mUOntCD0.js`
- `public/build/assets/DataToolbar-D0oadrXa.js`
- *(... và 110 files khác)*

---

## Kiến Trúc MAC V2 (Điểm Quan Trọng)

```
Request → Middleware (CheckPortalAccess / EnsureMinistryContext)
       → PortalService::canAccess(user, dept, feature)
           ├─ Level 1: FeatureAssignmentService (dept config)
           └─ Level 2: UserDepartmentFeature (user override)
```

**Files cốt lõi MAC V2:**
- `app/Http/Middleware/CheckPortalAccess.php` — Middleware cổng vào portal
- `app/Http/Middleware/PortalAccessMiddleware.php` — Gate kiểm tra feature
- `app/Services/PortalService.php` — Logic canAccess()
- `app/Services/FeatureAssignmentService.php` — Level 1 resolution
- `app/Models/UserDepartmentFeature.php` — Level 2 override table

---

## Cách Dùng File Này (Cho AI Agents)

```
Thay vì: grep cả codebase (500+ files, tốn 10k+ token)
Dùng:    Đọc KNOWLEDGE_GRAPH.md (1 file, ~3k token) → xác định đúng file → đọc file đó
```

**Ví dụ query:**
- Tìm file xử lý điểm danh → xem section `ATTENDANCE`
- Tìm file kiểm tra quyền → xem section `PERMISSIONS` + 'Files cốt lõi MAC V2'
- Tìm Vue page của portal → xem layer `VuePage-Portal`
