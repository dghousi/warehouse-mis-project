<?php

declare(strict_types=1);

return [
    'tables' => [
        'users' => [
            'new_name' => 'users',
            'order_by' => 'id',
            'columns' => [
                'id' => 'id',
                'first_name' => 'first_name',
                'last_name' => 'last_name',
                'photo' => 'profile_photo_path',
                'job_title' => 'job_title',
                'report_to_id' => [
                    'target' => 'report_to_id',
                    'default' => 0,
                ],
                'email' => 'email',
                'contact_number' => 'contact_number',
                'whatsapp_number' => 'whatsapp_number',
                'password' => 'password',
                'language' => [
                    'target' => 'locale',
                    'map' => ['pa' => 'ps', 'dr' => 'dr', 'en' => 'en', 'ps' => 'ps'],
                    'default' => 'en',
                ],
                'main_organization_id' => [
                    'target' => 'main_organization_id',
                    'default' => 0,
                ],
                'rights' => [
                    'target' => 'rights',
                    'map' => ['Create' => 'create', 'Review' => 'review', 'Approval' => 'approval'],
                    'default' => 'review',
                ],
                'notifications' => [
                    'target' => 'notifications',
                    'default' => true,
                ],
                'enabled' => [
                    'target' => 'enabled',
                    'default' => true,
                ],
                'status' => [
                    'target' => 'status',
                    'map' => [
                        'Pending' => 'pending',
                        'Approved' => 'approved',
                        'Rejected' => 'rejected',
                        'UploadForm' => 'uploadForm',
                        0 => 'pending',
                        1 => 'approved',
                        2 => 'rejected',
                        3 => 'uploadForm',
                    ],
                    'default' => 'pending',
                ],
                'remember_token' => 'remember_token',
                'remarks' => 'remarks',
                'last_login_at' => 'last_login_at',
                'userform' => 'user_form_path',
                'created_by' => [
                    'target' => 'created_by',
                    'default' => null,
                ],
                'updated_by' => 'updated_by',
                'deleted_by' => 'deleted_by',
                'deleted_at' => 'deleted_at',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'modules' => [
            'new_name' => 'modules',
            'order_by' => 'id',
            'columns' => [
                'id' => 'id',
                'name' => 'name_en',
                'url' => 'url',
                'icon_path' => 'icon_path',
                'sort_order' => 'sort_order',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'roles' => [
            'new_name' => 'roles',
            'order_by' => 'id',
            'columns' => [
                'id' => 'id',
                'name' => [
                    'target' => ['name', 'display_name_en', 'display_name_dr', 'display_name_ps'],
                ],
                'module_id' => 'module_id',
                'guard_name' => 'guard_name',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'permissions' => [
            'new_name' => 'permissions',
            'order_by' => 'id',
            'columns' => [
                'id' => 'id',
                'name' => [
                    'target' => ['name', 'display_name_en', 'display_name_dr', 'display_name_ps'],
                ],
                'guard_name' => 'guard_name',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'model_has_roles' => [
            'new_name' => 'model_has_roles',
            'order_by' => 'role_id',
            'columns' => [
                'role_id' => 'role_id',
                'model_id' => 'model_id',
                'model_type' => 'model_type',
            ],
        ],
        'role_has_permissions' => [
            'new_name' => 'role_has_permissions',
            'order_by' => 'permission_id',
            'columns' => [
                'permission_id' => 'permission_id',
                'role_id' => 'role_id',
            ],
        ],
        'active_users' => [
            'new_name' => 'active_users',
            'order_by' => 'user_id',
            'columns' => [
                'user_id' => 'user_id',
                'url' => 'url',
                'lastactivity' => 'last_activity',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'department_types' => [
            'new_name' => 'department_types',
            'order_by' => 'id',
            'columns' => [
                'name' => [
                    'target' => ['name_en', 'name_dr', 'name_ps'],
                ],
                'user_id' => 'user_id',
                'hierarchy' => 'hierarchy',
                'enabled' => 'enabled',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'jobtypes' => [
            'new_name' => 'job_types',
            'order_by' => 'id',
            'columns' => [
                'name' => [
                    'target' => ['name_en', 'name_dr', 'name_ps'],
                ],
                'is_structure' => 'is_structure',
                'department_type_id' => 'department_type_id',
                'sort_order' => 'sort_order',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'categories' => [
            'new_name' => 'categories',
            'order_by' => 'id',
            'columns' => [
                'title' => [
                    'target' => ['title_en', 'title_dr', 'title_ps'],
                ],
                'sort_order' => 'sort_order',
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'locations' => [
            'new_name' => 'locations',
            'order_by' => 'id',
            'columns' => [
                'name' => [
                    'target' => ['name_en', 'name_dr', 'name_ps'],
                ],
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
        'structures' => [
            'new_name' => 'structures',
            'order_by' => 'id',
            'columns' => [
                'name' => [
                    'target' => ['name_en', 'name_dr', 'name_ps'],
                ],
                'created_at' => 'created_at',
                'updated_at' => 'updated_at',
            ],
        ],
    ],
];
