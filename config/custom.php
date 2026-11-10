<?php

return [
    'file' => [
        'image_extensions' => explode(',', env('IMAGE_EXTENSIONS', 'jpg,jpeg,png,gif,webp,bmp')),
    ],
    'policies' => [
        'path' => app_path('Policies'),
        'tenant_path' => app_path('Policies/Tenant'),
        'methods' => [
            'viewAny', 'view', 'create', 'update', 'delete', 'restore',
            'forceDelete', 'forceDeleteAny', 'restoreAny', 'replicate', 'reorder',
        ],
        'single_parameter_methods' => [
            'viewAny',
            'create',
            'deleteAny',
            'forceDeleteAny',
            'restoreAny',
            'reorder',
        ],
    ],
];
