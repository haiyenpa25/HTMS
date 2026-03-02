<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class SyncPermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'permissions:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync all system-wide permissions and assign them to Super_Admin, Super Admin, BTS_Admin, and Pastor roles.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $permissions = [
            // Members
            'view_members',
            'create_members',
            'edit_members',
            'delete_members',

            // Departments
            'view_departments',
            'create_departments',
            'edit_departments',
            'delete_departments',

            // Meetings
            'view_meetings',
            'create_meetings',
            'edit_meetings',
            'delete_meetings',

            // Attendance
            'view_attendance',
            'mark_attendance',
            'bypass_attendance_lock',

            // Portal
            'access_department_portal',
            'portal_view_members',
            'portal_manage_attendance',

            // Portal Members
            'portal_view_board',
            'portal_manage_board',
            'portal_view_all_members',
            'portal_export_members',

            // Speakers
            'view_speakers',
            'create_speakers',
            'edit_speakers',
            'delete_speakers',

            // Roles & Users
            'manage_users',
            'manage_roles'
        ];

        $this->info('Starting permission synchronization...');

        // Create or get permissions
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
            $this->line("Synced permission: {$perm}");
        }

        $this->info('Permissions defined in database.');

        // Assign all permissions to Global Roles
        $globalRoles = ['Super_Admin', 'Super_Admin', 'BTS_Admin', 'Pastor'];

        foreach ($globalRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $role->syncPermissions($permissions);
            $this->line("Assigned all permissions to role: {$roleName}");
        }

        // Clear the permissions cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->info('Successfully synced all system permissions and assigned them to Global Admins.');
    }
}

