<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TenantMasterPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \Illuminate\Support\Facades\DB::table('permissions')->delete();
        
        \Illuminate\Support\Facades\DB::table('permissions')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'viewAny:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'view:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'create:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'update:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'delete:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'restore:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'forceDelete:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            7 => 
            array (
                'id' => 8,
                'name' => 'forceDeleteAny:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            8 => 
            array (
                'id' => 9,
                'name' => 'restoreAny:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            9 => 
            array (
                'id' => 10,
                'name' => 'replicate:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            10 => 
            array (
                'id' => 11,
                'name' => 'reorder:attendance_correction',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            11 => 
            array (
                'id' => 12,
                'name' => 'viewAny:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            12 => 
            array (
                'id' => 13,
                'name' => 'view:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            13 => 
            array (
                'id' => 14,
                'name' => 'create:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            14 => 
            array (
                'id' => 15,
                'name' => 'update:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            15 => 
            array (
                'id' => 16,
                'name' => 'delete:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            16 => 
            array (
                'id' => 17,
                'name' => 'restore:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            17 => 
            array (
                'id' => 18,
                'name' => 'forceDelete:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            18 => 
            array (
                'id' => 19,
                'name' => 'forceDeleteAny:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            19 => 
            array (
                'id' => 20,
                'name' => 'restoreAny:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            20 => 
            array (
                'id' => 21,
                'name' => 'replicate:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            21 => 
            array (
                'id' => 22,
                'name' => 'reorder:attendance',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            22 => 
            array (
                'id' => 23,
                'name' => 'viewAny:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            23 => 
            array (
                'id' => 24,
                'name' => 'view:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            24 => 
            array (
                'id' => 25,
                'name' => 'create:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            25 => 
            array (
                'id' => 26,
                'name' => 'update:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            26 => 
            array (
                'id' => 27,
                'name' => 'delete:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            27 => 
            array (
                'id' => 28,
                'name' => 'restore:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            28 => 
            array (
                'id' => 29,
                'name' => 'forceDelete:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            29 => 
            array (
                'id' => 30,
                'name' => 'forceDeleteAny:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            30 => 
            array (
                'id' => 31,
                'name' => 'restoreAny:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            31 => 
            array (
                'id' => 32,
                'name' => 'replicate:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            32 => 
            array (
                'id' => 33,
                'name' => 'reorder:branch',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            33 => 
            array (
                'id' => 34,
                'name' => 'viewAny:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            34 => 
            array (
                'id' => 35,
                'name' => 'view:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:25',
                'updated_at' => '2026-08-03 11:20:25',
            ),
            35 => 
            array (
                'id' => 36,
                'name' => 'create:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            36 => 
            array (
                'id' => 37,
                'name' => 'update:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            37 => 
            array (
                'id' => 38,
                'name' => 'delete:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            38 => 
            array (
                'id' => 39,
                'name' => 'restore:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            39 => 
            array (
                'id' => 40,
                'name' => 'forceDelete:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            40 => 
            array (
                'id' => 41,
                'name' => 'forceDeleteAny:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            41 => 
            array (
                'id' => 42,
                'name' => 'restoreAny:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            42 => 
            array (
                'id' => 43,
                'name' => 'replicate:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            43 => 
            array (
                'id' => 44,
                'name' => 'reorder:brand',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            44 => 
            array (
                'id' => 45,
                'name' => 'viewAny:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            45 => 
            array (
                'id' => 46,
                'name' => 'view:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            46 => 
            array (
                'id' => 47,
                'name' => 'create:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            47 => 
            array (
                'id' => 48,
                'name' => 'update:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            48 => 
            array (
                'id' => 49,
                'name' => 'delete:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            49 => 
            array (
                'id' => 50,
                'name' => 'restore:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            50 => 
            array (
                'id' => 51,
                'name' => 'forceDelete:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            51 => 
            array (
                'id' => 52,
                'name' => 'forceDeleteAny:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            52 => 
            array (
                'id' => 53,
                'name' => 'restoreAny:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            53 => 
            array (
                'id' => 54,
                'name' => 'replicate:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            54 => 
            array (
                'id' => 55,
                'name' => 'reorder:category',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            55 => 
            array (
                'id' => 56,
                'name' => 'viewAny:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            56 => 
            array (
                'id' => 57,
                'name' => 'view:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            57 => 
            array (
                'id' => 58,
                'name' => 'create:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            58 => 
            array (
                'id' => 59,
                'name' => 'update:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            59 => 
            array (
                'id' => 60,
                'name' => 'delete:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            60 => 
            array (
                'id' => 61,
                'name' => 'restore:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            61 => 
            array (
                'id' => 62,
                'name' => 'forceDelete:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            62 => 
            array (
                'id' => 63,
                'name' => 'forceDeleteAny:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            63 => 
            array (
                'id' => 64,
                'name' => 'restoreAny:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            64 => 
            array (
                'id' => 65,
                'name' => 'replicate:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            65 => 
            array (
                'id' => 66,
                'name' => 'reorder:client',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:26',
                'updated_at' => '2026-08-03 11:20:26',
            ),
            66 => 
            array (
                'id' => 67,
                'name' => 'viewAny:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            67 => 
            array (
                'id' => 68,
                'name' => 'view:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            68 => 
            array (
                'id' => 69,
                'name' => 'create:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            69 => 
            array (
                'id' => 70,
                'name' => 'update:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            70 => 
            array (
                'id' => 71,
                'name' => 'delete:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            71 => 
            array (
                'id' => 72,
                'name' => 'restore:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            72 => 
            array (
                'id' => 73,
                'name' => 'forceDelete:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            73 => 
            array (
                'id' => 74,
                'name' => 'forceDeleteAny:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            74 => 
            array (
                'id' => 75,
                'name' => 'restoreAny:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            75 => 
            array (
                'id' => 76,
                'name' => 'replicate:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            76 => 
            array (
                'id' => 77,
                'name' => 'reorder:employee_assignment',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            77 => 
            array (
                'id' => 78,
                'name' => 'viewAny:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            78 => 
            array (
                'id' => 79,
                'name' => 'view:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            79 => 
            array (
                'id' => 80,
                'name' => 'create:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            80 => 
            array (
                'id' => 81,
                'name' => 'update:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            81 => 
            array (
                'id' => 82,
                'name' => 'delete:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            82 => 
            array (
                'id' => 83,
                'name' => 'restore:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            83 => 
            array (
                'id' => 84,
                'name' => 'forceDelete:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            84 => 
            array (
                'id' => 85,
                'name' => 'forceDeleteAny:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            85 => 
            array (
                'id' => 86,
                'name' => 'restoreAny:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            86 => 
            array (
                'id' => 87,
                'name' => 'replicate:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            87 => 
            array (
                'id' => 88,
                'name' => 'reorder:employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            88 => 
            array (
                'id' => 89,
                'name' => 'viewAny:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:27',
                'updated_at' => '2026-08-03 11:20:27',
            ),
            89 => 
            array (
                'id' => 90,
                'name' => 'view:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            90 => 
            array (
                'id' => 91,
                'name' => 'create:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            91 => 
            array (
                'id' => 92,
                'name' => 'update:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            92 => 
            array (
                'id' => 93,
                'name' => 'delete:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            93 => 
            array (
                'id' => 94,
                'name' => 'restore:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            94 => 
            array (
                'id' => 95,
                'name' => 'forceDelete:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            95 => 
            array (
                'id' => 96,
                'name' => 'forceDeleteAny:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            96 => 
            array (
                'id' => 97,
                'name' => 'restoreAny:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            97 => 
            array (
                'id' => 98,
                'name' => 'replicate:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            98 => 
            array (
                'id' => 99,
                'name' => 'reorder:goods_receipts',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            99 => 
            array (
                'id' => 100,
                'name' => 'viewAny:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            100 => 
            array (
                'id' => 101,
                'name' => 'view:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            101 => 
            array (
                'id' => 102,
                'name' => 'create:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            102 => 
            array (
                'id' => 103,
                'name' => 'update:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            103 => 
            array (
                'id' => 104,
                'name' => 'delete:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            104 => 
            array (
                'id' => 105,
                'name' => 'restore:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            105 => 
            array (
                'id' => 106,
                'name' => 'forceDelete:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            106 => 
            array (
                'id' => 107,
                'name' => 'forceDeleteAny:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            107 => 
            array (
                'id' => 108,
                'name' => 'restoreAny:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            108 => 
            array (
                'id' => 109,
                'name' => 'replicate:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:28',
                'updated_at' => '2026-08-03 11:20:28',
            ),
            109 => 
            array (
                'id' => 110,
                'name' => 'reorder:holiday',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            110 => 
            array (
                'id' => 111,
                'name' => 'viewAny:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            111 => 
            array (
                'id' => 112,
                'name' => 'view:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            112 => 
            array (
                'id' => 113,
                'name' => 'create:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            113 => 
            array (
                'id' => 114,
                'name' => 'update:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            114 => 
            array (
                'id' => 115,
                'name' => 'delete:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            115 => 
            array (
                'id' => 116,
                'name' => 'restore:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            116 => 
            array (
                'id' => 117,
                'name' => 'forceDelete:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            117 => 
            array (
                'id' => 118,
                'name' => 'forceDeleteAny:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            118 => 
            array (
                'id' => 119,
                'name' => 'restoreAny:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            119 => 
            array (
                'id' => 120,
                'name' => 'replicate:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            120 => 
            array (
                'id' => 121,
                'name' => 'reorder:leave_request',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            121 => 
            array (
                'id' => 122,
                'name' => 'viewAny:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            122 => 
            array (
                'id' => 123,
                'name' => 'view:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            123 => 
            array (
                'id' => 124,
                'name' => 'create:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            124 => 
            array (
                'id' => 125,
                'name' => 'update:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            125 => 
            array (
                'id' => 126,
                'name' => 'delete:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            126 => 
            array (
                'id' => 127,
                'name' => 'restore:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:29',
                'updated_at' => '2026-08-03 11:20:29',
            ),
            127 => 
            array (
                'id' => 128,
                'name' => 'forceDelete:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            128 => 
            array (
                'id' => 129,
                'name' => 'forceDeleteAny:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            129 => 
            array (
                'id' => 130,
                'name' => 'restoreAny:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            130 => 
            array (
                'id' => 131,
                'name' => 'replicate:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            131 => 
            array (
                'id' => 132,
                'name' => 'reorder:leave_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            132 => 
            array (
                'id' => 133,
                'name' => 'viewAny:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            133 => 
            array (
                'id' => 134,
                'name' => 'view:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            134 => 
            array (
                'id' => 135,
                'name' => 'create:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            135 => 
            array (
                'id' => 136,
                'name' => 'update:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            136 => 
            array (
                'id' => 137,
                'name' => 'delete:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            137 => 
            array (
                'id' => 138,
                'name' => 'restore:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            138 => 
            array (
                'id' => 139,
                'name' => 'forceDelete:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            139 => 
            array (
                'id' => 140,
                'name' => 'forceDeleteAny:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            140 => 
            array (
                'id' => 141,
                'name' => 'restoreAny:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            141 => 
            array (
                'id' => 142,
                'name' => 'replicate:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            142 => 
            array (
                'id' => 143,
                'name' => 'reorder:permission',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            143 => 
            array (
                'id' => 144,
                'name' => 'viewAny:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:30',
                'updated_at' => '2026-08-03 11:20:30',
            ),
            144 => 
            array (
                'id' => 145,
                'name' => 'view:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            145 => 
            array (
                'id' => 146,
                'name' => 'create:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            146 => 
            array (
                'id' => 147,
                'name' => 'update:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            147 => 
            array (
                'id' => 148,
                'name' => 'delete:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            148 => 
            array (
                'id' => 149,
                'name' => 'restore:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            149 => 
            array (
                'id' => 150,
                'name' => 'forceDelete:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            150 => 
            array (
                'id' => 151,
                'name' => 'forceDeleteAny:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            151 => 
            array (
                'id' => 152,
                'name' => 'restoreAny:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            152 => 
            array (
                'id' => 153,
                'name' => 'replicate:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            153 => 
            array (
                'id' => 154,
                'name' => 'reorder:product',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            154 => 
            array (
                'id' => 155,
                'name' => 'viewAny:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            155 => 
            array (
                'id' => 156,
                'name' => 'view:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            156 => 
            array (
                'id' => 157,
                'name' => 'create:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            157 => 
            array (
                'id' => 158,
                'name' => 'update:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            158 => 
            array (
                'id' => 159,
                'name' => 'delete:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:31',
                'updated_at' => '2026-08-03 11:20:31',
            ),
            159 => 
            array (
                'id' => 160,
                'name' => 'restore:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            160 => 
            array (
                'id' => 161,
                'name' => 'forceDelete:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            161 => 
            array (
                'id' => 162,
                'name' => 'forceDeleteAny:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            162 => 
            array (
                'id' => 163,
                'name' => 'restoreAny:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            163 => 
            array (
                'id' => 164,
                'name' => 'replicate:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            164 => 
            array (
                'id' => 165,
                'name' => 'reorder:purchase_order',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            165 => 
            array (
                'id' => 166,
                'name' => 'viewAny:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            166 => 
            array (
                'id' => 167,
                'name' => 'view:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            167 => 
            array (
                'id' => 168,
                'name' => 'create:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            168 => 
            array (
                'id' => 169,
                'name' => 'update:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            169 => 
            array (
                'id' => 170,
                'name' => 'delete:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            170 => 
            array (
                'id' => 171,
                'name' => 'restore:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            171 => 
            array (
                'id' => 172,
                'name' => 'forceDelete:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            172 => 
            array (
                'id' => 173,
                'name' => 'forceDeleteAny:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            173 => 
            array (
                'id' => 174,
                'name' => 'restoreAny:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:32',
                'updated_at' => '2026-08-03 11:20:32',
            ),
            174 => 
            array (
                'id' => 175,
                'name' => 'replicate:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            175 => 
            array (
                'id' => 176,
                'name' => 'reorder:role',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            176 => 
            array (
                'id' => 177,
                'name' => 'viewAny:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            177 => 
            array (
                'id' => 178,
                'name' => 'view:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            178 => 
            array (
                'id' => 179,
                'name' => 'create:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            179 => 
            array (
                'id' => 180,
                'name' => 'update:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            180 => 
            array (
                'id' => 181,
                'name' => 'delete:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            181 => 
            array (
                'id' => 182,
                'name' => 'restore:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            182 => 
            array (
                'id' => 183,
                'name' => 'forceDelete:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            183 => 
            array (
                'id' => 184,
                'name' => 'forceDeleteAny:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            184 => 
            array (
                'id' => 185,
                'name' => 'restoreAny:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            185 => 
            array (
                'id' => 186,
                'name' => 'replicate:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:33',
                'updated_at' => '2026-08-03 11:20:33',
            ),
            186 => 
            array (
                'id' => 187,
                'name' => 'reorder:skill_employee',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            187 => 
            array (
                'id' => 188,
                'name' => 'viewAny:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            188 => 
            array (
                'id' => 189,
                'name' => 'view:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            189 => 
            array (
                'id' => 190,
                'name' => 'create:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            190 => 
            array (
                'id' => 191,
                'name' => 'update:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            191 => 
            array (
                'id' => 192,
                'name' => 'delete:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            192 => 
            array (
                'id' => 193,
                'name' => 'restore:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            193 => 
            array (
                'id' => 194,
                'name' => 'forceDelete:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            194 => 
            array (
                'id' => 195,
                'name' => 'forceDeleteAny:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            195 => 
            array (
                'id' => 196,
                'name' => 'restoreAny:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            196 => 
            array (
                'id' => 197,
                'name' => 'replicate:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            197 => 
            array (
                'id' => 198,
                'name' => 'reorder:skill_level',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            198 => 
            array (
                'id' => 199,
                'name' => 'viewAny:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:34',
                'updated_at' => '2026-08-03 11:20:34',
            ),
            199 => 
            array (
                'id' => 200,
                'name' => 'view:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            200 => 
            array (
                'id' => 201,
                'name' => 'create:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            201 => 
            array (
                'id' => 202,
                'name' => 'update:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            202 => 
            array (
                'id' => 203,
                'name' => 'delete:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            203 => 
            array (
                'id' => 204,
                'name' => 'restore:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            204 => 
            array (
                'id' => 205,
                'name' => 'forceDelete:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            205 => 
            array (
                'id' => 206,
                'name' => 'forceDeleteAny:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            206 => 
            array (
                'id' => 207,
                'name' => 'restoreAny:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            207 => 
            array (
                'id' => 208,
                'name' => 'replicate:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            208 => 
            array (
                'id' => 209,
                'name' => 'reorder:skill_specialization',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            209 => 
            array (
                'id' => 210,
                'name' => 'viewAny:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            210 => 
            array (
                'id' => 211,
                'name' => 'view:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:35',
                'updated_at' => '2026-08-03 11:20:35',
            ),
            211 => 
            array (
                'id' => 212,
                'name' => 'create:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            212 => 
            array (
                'id' => 213,
                'name' => 'update:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            213 => 
            array (
                'id' => 214,
                'name' => 'delete:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            214 => 
            array (
                'id' => 215,
                'name' => 'restore:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            215 => 
            array (
                'id' => 216,
                'name' => 'forceDelete:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            216 => 
            array (
                'id' => 217,
                'name' => 'forceDeleteAny:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            217 => 
            array (
                'id' => 218,
                'name' => 'restoreAny:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            218 => 
            array (
                'id' => 219,
                'name' => 'replicate:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            219 => 
            array (
                'id' => 220,
                'name' => 'reorder:stock_opname',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            220 => 
            array (
                'id' => 221,
                'name' => 'viewAny:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            221 => 
            array (
                'id' => 222,
                'name' => 'view:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:36',
                'updated_at' => '2026-08-03 11:20:36',
            ),
            222 => 
            array (
                'id' => 223,
                'name' => 'create:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            223 => 
            array (
                'id' => 224,
                'name' => 'update:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            224 => 
            array (
                'id' => 225,
                'name' => 'delete:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            225 => 
            array (
                'id' => 226,
                'name' => 'restore:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            226 => 
            array (
                'id' => 227,
                'name' => 'forceDelete:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            227 => 
            array (
                'id' => 228,
                'name' => 'forceDeleteAny:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            228 => 
            array (
                'id' => 229,
                'name' => 'restoreAny:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            229 => 
            array (
                'id' => 230,
                'name' => 'replicate:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            230 => 
            array (
                'id' => 231,
                'name' => 'reorder:stock',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            231 => 
            array (
                'id' => 232,
                'name' => 'viewAny:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            232 => 
            array (
                'id' => 233,
                'name' => 'view:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:37',
                'updated_at' => '2026-08-03 11:20:37',
            ),
            233 => 
            array (
                'id' => 234,
                'name' => 'create:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            234 => 
            array (
                'id' => 235,
                'name' => 'update:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            235 => 
            array (
                'id' => 236,
                'name' => 'delete:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            236 => 
            array (
                'id' => 237,
                'name' => 'restore:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            237 => 
            array (
                'id' => 238,
                'name' => 'forceDelete:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            238 => 
            array (
                'id' => 239,
                'name' => 'forceDeleteAny:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            239 => 
            array (
                'id' => 240,
                'name' => 'restoreAny:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            240 => 
            array (
                'id' => 241,
                'name' => 'replicate:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            241 => 
            array (
                'id' => 242,
                'name' => 'reorder:supplier',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            242 => 
            array (
                'id' => 243,
                'name' => 'viewAny:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:38',
                'updated_at' => '2026-08-03 11:20:38',
            ),
            243 => 
            array (
                'id' => 244,
                'name' => 'view:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            244 => 
            array (
                'id' => 245,
                'name' => 'create:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            245 => 
            array (
                'id' => 246,
                'name' => 'update:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            246 => 
            array (
                'id' => 247,
                'name' => 'delete:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            247 => 
            array (
                'id' => 248,
                'name' => 'restore:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            248 => 
            array (
                'id' => 249,
                'name' => 'forceDelete:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            249 => 
            array (
                'id' => 250,
                'name' => 'forceDeleteAny:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            250 => 
            array (
                'id' => 251,
                'name' => 'restoreAny:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            251 => 
            array (
                'id' => 252,
                'name' => 'replicate:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            252 => 
            array (
                'id' => 253,
                'name' => 'reorder:unit',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:39',
                'updated_at' => '2026-08-03 11:20:39',
            ),
            253 => 
            array (
                'id' => 254,
                'name' => 'viewAny:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            254 => 
            array (
                'id' => 255,
                'name' => 'view:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            255 => 
            array (
                'id' => 256,
                'name' => 'create:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            256 => 
            array (
                'id' => 257,
                'name' => 'update:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            257 => 
            array (
                'id' => 258,
                'name' => 'delete:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            258 => 
            array (
                'id' => 259,
                'name' => 'restore:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            259 => 
            array (
                'id' => 260,
                'name' => 'forceDelete:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            260 => 
            array (
                'id' => 261,
                'name' => 'forceDeleteAny:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            261 => 
            array (
                'id' => 262,
                'name' => 'restoreAny:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            262 => 
            array (
                'id' => 263,
                'name' => 'replicate:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:40',
                'updated_at' => '2026-08-03 11:20:40',
            ),
            263 => 
            array (
                'id' => 264,
                'name' => 'reorder:user',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            264 => 
            array (
                'id' => 265,
                'name' => 'viewAny:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            265 => 
            array (
                'id' => 266,
                'name' => 'view:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            266 => 
            array (
                'id' => 267,
                'name' => 'create:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            267 => 
            array (
                'id' => 268,
                'name' => 'update:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            268 => 
            array (
                'id' => 269,
                'name' => 'delete:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            269 => 
            array (
                'id' => 270,
                'name' => 'restore:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            270 => 
            array (
                'id' => 271,
                'name' => 'forceDelete:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            271 => 
            array (
                'id' => 272,
                'name' => 'forceDeleteAny:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            272 => 
            array (
                'id' => 273,
                'name' => 'restoreAny:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:41',
                'updated_at' => '2026-08-03 11:20:41',
            ),
            273 => 
            array (
                'id' => 274,
                'name' => 'replicate:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            274 => 
            array (
                'id' => 275,
                'name' => 'reorder:work_schedule_override',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            275 => 
            array (
                'id' => 276,
                'name' => 'viewAny:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            276 => 
            array (
                'id' => 277,
                'name' => 'view:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            277 => 
            array (
                'id' => 278,
                'name' => 'create:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            278 => 
            array (
                'id' => 279,
                'name' => 'update:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            279 => 
            array (
                'id' => 280,
                'name' => 'delete:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            280 => 
            array (
                'id' => 281,
                'name' => 'restore:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            281 => 
            array (
                'id' => 282,
                'name' => 'forceDelete:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            282 => 
            array (
                'id' => 283,
                'name' => 'forceDeleteAny:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:42',
                'updated_at' => '2026-08-03 11:20:42',
            ),
            283 => 
            array (
                'id' => 284,
                'name' => 'restoreAny:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            284 => 
            array (
                'id' => 285,
                'name' => 'replicate:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            285 => 
            array (
                'id' => 286,
                'name' => 'reorder:work_schedule',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            286 => 
            array (
                'id' => 287,
                'name' => 'viewAny:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            287 => 
            array (
                'id' => 288,
                'name' => 'view:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            288 => 
            array (
                'id' => 289,
                'name' => 'create:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            289 => 
            array (
                'id' => 290,
                'name' => 'update:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            290 => 
            array (
                'id' => 291,
                'name' => 'delete:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            291 => 
            array (
                'id' => 292,
                'name' => 'restore:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:43',
                'updated_at' => '2026-08-03 11:20:43',
            ),
            292 => 
            array (
                'id' => 293,
                'name' => 'forceDelete:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:44',
                'updated_at' => '2026-08-03 11:20:44',
            ),
            293 => 
            array (
                'id' => 294,
                'name' => 'forceDeleteAny:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:44',
                'updated_at' => '2026-08-03 11:20:44',
            ),
            294 => 
            array (
                'id' => 295,
                'name' => 'restoreAny:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:44',
                'updated_at' => '2026-08-03 11:20:44',
            ),
            295 => 
            array (
                'id' => 296,
                'name' => 'replicate:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:44',
                'updated_at' => '2026-08-03 11:20:44',
            ),
            296 => 
            array (
                'id' => 297,
                'name' => 'reorder:work_shift',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-03 11:20:44',
                'updated_at' => '2026-08-03 11:20:44',
            ),
            297 => 
            array (
                'id' => 299,
                'name' => 'viewAny:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:38',
                'updated_at' => '2026-08-24 00:09:38',
            ),
            298 => 
            array (
                'id' => 300,
                'name' => 'view:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:38',
                'updated_at' => '2026-08-24 00:09:38',
            ),
            299 => 
            array (
                'id' => 301,
                'name' => 'create:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:38',
                'updated_at' => '2026-08-24 00:09:38',
            ),
            300 => 
            array (
                'id' => 302,
                'name' => 'update:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:38',
                'updated_at' => '2026-08-24 00:09:38',
            ),
            301 => 
            array (
                'id' => 303,
                'name' => 'delete:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:38',
                'updated_at' => '2026-08-24 00:09:38',
            ),
            302 => 
            array (
                'id' => 304,
                'name' => 'restore:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            303 => 
            array (
                'id' => 305,
                'name' => 'forceDelete:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            304 => 
            array (
                'id' => 306,
                'name' => 'forceDeleteAny:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            305 => 
            array (
                'id' => 307,
                'name' => 'restoreAny:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            306 => 
            array (
                'id' => 308,
                'name' => 'replicate:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            307 => 
            array (
                'id' => 309,
                'name' => 'reorder:compensation_component',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            308 => 
            array (
                'id' => 310,
                'name' => 'viewAny:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            309 => 
            array (
                'id' => 311,
                'name' => 'view:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:39',
                'updated_at' => '2026-08-24 00:09:39',
            ),
            310 => 
            array (
                'id' => 312,
                'name' => 'create:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            311 => 
            array (
                'id' => 313,
                'name' => 'update:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            312 => 
            array (
                'id' => 314,
                'name' => 'delete:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            313 => 
            array (
                'id' => 315,
                'name' => 'restore:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            314 => 
            array (
                'id' => 316,
                'name' => 'forceDelete:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            315 => 
            array (
                'id' => 317,
                'name' => 'forceDeleteAny:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            316 => 
            array (
                'id' => 318,
                'name' => 'restoreAny:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            317 => 
            array (
                'id' => 319,
                'name' => 'replicate:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:40',
                'updated_at' => '2026-08-24 00:09:40',
            ),
            318 => 
            array (
                'id' => 320,
                'name' => 'reorder:compensation_package',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            319 => 
            array (
                'id' => 321,
                'name' => 'viewAny:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            320 => 
            array (
                'id' => 322,
                'name' => 'view:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            321 => 
            array (
                'id' => 323,
                'name' => 'create:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            322 => 
            array (
                'id' => 324,
                'name' => 'update:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            323 => 
            array (
                'id' => 325,
                'name' => 'delete:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            324 => 
            array (
                'id' => 326,
                'name' => 'restore:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            325 => 
            array (
                'id' => 327,
                'name' => 'forceDelete:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            326 => 
            array (
                'id' => 328,
                'name' => 'forceDeleteAny:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:41',
                'updated_at' => '2026-08-24 00:09:41',
            ),
            327 => 
            array (
                'id' => 329,
                'name' => 'restoreAny:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            328 => 
            array (
                'id' => 330,
                'name' => 'replicate:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            329 => 
            array (
                'id' => 331,
                'name' => 'reorder:device_type',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            330 => 
            array (
                'id' => 332,
                'name' => 'viewAny:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            331 => 
            array (
                'id' => 333,
                'name' => 'view:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            332 => 
            array (
                'id' => 334,
                'name' => 'create:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            333 => 
            array (
                'id' => 335,
                'name' => 'update:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            334 => 
            array (
                'id' => 336,
                'name' => 'delete:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:42',
                'updated_at' => '2026-08-24 00:09:42',
            ),
            335 => 
            array (
                'id' => 337,
                'name' => 'restore:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            336 => 
            array (
                'id' => 338,
                'name' => 'forceDelete:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            337 => 
            array (
                'id' => 339,
                'name' => 'forceDeleteAny:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            338 => 
            array (
                'id' => 340,
                'name' => 'restoreAny:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            339 => 
            array (
                'id' => 341,
                'name' => 'replicate:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            340 => 
            array (
                'id' => 342,
                'name' => 'reorder:discount',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            341 => 
            array (
                'id' => 343,
                'name' => 'viewAny:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            342 => 
            array (
                'id' => 344,
                'name' => 'view:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:43',
                'updated_at' => '2026-08-24 00:09:43',
            ),
            343 => 
            array (
                'id' => 345,
                'name' => 'create:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            344 => 
            array (
                'id' => 346,
                'name' => 'update:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            345 => 
            array (
                'id' => 347,
                'name' => 'delete:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            346 => 
            array (
                'id' => 348,
                'name' => 'restore:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            347 => 
            array (
                'id' => 349,
                'name' => 'forceDelete:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            348 => 
            array (
                'id' => 350,
                'name' => 'forceDeleteAny:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:44',
                'updated_at' => '2026-08-24 00:09:44',
            ),
            349 => 
            array (
                'id' => 351,
                'name' => 'restoreAny:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            350 => 
            array (
                'id' => 352,
                'name' => 'replicate:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            351 => 
            array (
                'id' => 353,
                'name' => 'reorder:payment_method',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            352 => 
            array (
                'id' => 354,
                'name' => 'viewAny:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            353 => 
            array (
                'id' => 355,
                'name' => 'view:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            354 => 
            array (
                'id' => 356,
                'name' => 'create:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            355 => 
            array (
                'id' => 357,
                'name' => 'update:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:45',
                'updated_at' => '2026-08-24 00:09:45',
            ),
            356 => 
            array (
                'id' => 358,
                'name' => 'delete:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            357 => 
            array (
                'id' => 359,
                'name' => 'restore:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            358 => 
            array (
                'id' => 360,
                'name' => 'forceDelete:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            359 => 
            array (
                'id' => 361,
                'name' => 'forceDeleteAny:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            360 => 
            array (
                'id' => 362,
                'name' => 'restoreAny:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            361 => 
            array (
                'id' => 363,
                'name' => 'replicate:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            362 => 
            array (
                'id' => 364,
                'name' => 'reorder:repair',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:46',
                'updated_at' => '2026-08-24 00:09:46',
            ),
            363 => 
            array (
                'id' => 365,
                'name' => 'viewAny:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            364 => 
            array (
                'id' => 366,
                'name' => 'view:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            365 => 
            array (
                'id' => 367,
                'name' => 'create:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            366 => 
            array (
                'id' => 368,
                'name' => 'update:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            367 => 
            array (
                'id' => 369,
                'name' => 'delete:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            368 => 
            array (
                'id' => 370,
                'name' => 'restore:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            369 => 
            array (
                'id' => 371,
                'name' => 'forceDelete:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:47',
                'updated_at' => '2026-08-24 00:09:47',
            ),
            370 => 
            array (
                'id' => 372,
                'name' => 'forceDeleteAny:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            371 => 
            array (
                'id' => 373,
                'name' => 'restoreAny:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            372 => 
            array (
                'id' => 374,
                'name' => 'replicate:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            373 => 
            array (
                'id' => 375,
                'name' => 'reorder:stock_movement',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            374 => 
            array (
                'id' => 376,
                'name' => 'viewAny:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            375 => 
            array (
                'id' => 377,
                'name' => 'view:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            376 => 
            array (
                'id' => 378,
                'name' => 'create:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            377 => 
            array (
                'id' => 379,
                'name' => 'update:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:48',
                'updated_at' => '2026-08-24 00:09:48',
            ),
            378 => 
            array (
                'id' => 380,
                'name' => 'delete:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            379 => 
            array (
                'id' => 381,
                'name' => 'restore:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            380 => 
            array (
                'id' => 382,
                'name' => 'forceDelete:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            381 => 
            array (
                'id' => 383,
                'name' => 'forceDeleteAny:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            382 => 
            array (
                'id' => 384,
                'name' => 'restoreAny:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            383 => 
            array (
                'id' => 385,
                'name' => 'replicate:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            384 => 
            array (
                'id' => 386,
                'name' => 'reorder:stock_transfer',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:49',
                'updated_at' => '2026-08-24 00:09:49',
            ),
            385 => 
            array (
                'id' => 387,
                'name' => 'viewAny:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            386 => 
            array (
                'id' => 388,
                'name' => 'view:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            387 => 
            array (
                'id' => 389,
                'name' => 'create:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            388 => 
            array (
                'id' => 390,
                'name' => 'update:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            389 => 
            array (
                'id' => 391,
                'name' => 'delete:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            390 => 
            array (
                'id' => 392,
                'name' => 'restore:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            391 => 
            array (
                'id' => 393,
                'name' => 'forceDelete:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:50',
                'updated_at' => '2026-08-24 00:09:50',
            ),
            392 => 
            array (
                'id' => 394,
                'name' => 'forceDeleteAny:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:51',
                'updated_at' => '2026-08-24 00:09:51',
            ),
            393 => 
            array (
                'id' => 395,
                'name' => 'restoreAny:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:51',
                'updated_at' => '2026-08-24 00:09:51',
            ),
            394 => 
            array (
                'id' => 396,
                'name' => 'replicate:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:51',
                'updated_at' => '2026-08-24 00:09:51',
            ),
            395 => 
            array (
                'id' => 397,
                'name' => 'reorder:tax',
                'guard_name' => 'tenant',
                'created_at' => '2026-08-24 00:09:51',
                'updated_at' => '2026-08-24 00:09:51',
            ),
        ));
        
        
    }
}
