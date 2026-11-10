<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions for different modules
        // $permissions = [
        //     // Customer management
        //     'view users',
        //     'create users',
        //     'edit users',
        //     'delete users',

        //     // Tenant management
        //     'view tenant',
        //     'create tenant',
        //     'edit tenant',
        //     'delete tenant',

        //     // Permission management
        //     'view permissions',
        //     'create permissions',
        //     'edit permissions',
        //     'delete permissions',

        //     // Role management
        //     'view Roles',
        //     'create Roles',
        //     'edit Roles',
        //     'delete Roles',

        //     // Financial reports
        //     'view financial reports',
        //     'export financial reports',

        //     // SaaS management
        //     'manage subscriptions',
        //     'manage plans',
        //     'view all tenants',
        //     'system settings',

        //     // Service management
        //     'view services',
        //     'create services',
        //     'edit services',
        //     'delete services',
        // ];

        // foreach ($permissions as $permission) {
        //     Permission::create(['name' => $permission]);
        // }

        // Create roles and assign permissions

        // Super Admin - full access to SaaS platform
        $superAdmin = Role::create(['name' => 'super_admin']);
        // $superAdmin->givePermissionTo(Permission::all());

        // Business Owner - owns laundry business, can manage all aspects
        $businessOwner = Role::create(['name' => 'business_owner']);
        // $businessOwner->givePermissionTo([
        //     'view tenant','create tenant','edit tenant','delete tenant',
        // ]);

        // Branch Manager - manages specific branch
        // $branchManager = Role::create(['name' => 'branch_manager']);
        // $branchManager->givePermissionTo([
        //     'view customers', 'create customers', 'edit customers',
        //     'view orders', 'create orders', 'edit orders', 'update order status',
        //     'view employees', 'edit employees',
        //     'view financial reports',
        //     'view services',
        // ]);

        // Cashier - handles daily operations
        // $cashier = Role::create(['name' => 'cashier']);
        // $cashier->givePermissionTo([
        //     'view customers', 'create customers', 'edit customers',
        //     'view orders', 'create orders', 'edit orders', 'update order status',
        //     'view services',
        // ]);


        $user = User::find(1); // user yg sudah ada

        if ($user) {
            $user->assignRole('super_admin');
        }

        $user = User::where('email', 'admin@tenant.com')->first(); // user yg sudah ada

        if ($user) {
            $user->assignRole('business_owner');
        }

        // Create default super admin user
        // $superAdminUser = User::firstOrCreate([
        //     'name' => 'Super Administrator',
        //     'email' => 'admin@admin.com',
        //     'password' => bcrypt('password'),
        //     'email_verified_at' => now(),
        // ]);
        // $superAdminUser->assignRole('super_admin');
    }
}
