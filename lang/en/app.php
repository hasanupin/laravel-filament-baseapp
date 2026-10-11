<?php

return [
    'locale' => [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ],

    'modules' => [
        'users' => 'Users',
        'roles' => 'User Roles',
        'menus' => 'User Menu',
    ],

    'menu_groups' => [
        'settings' => 'Settings',
    ],

    'actions' => [
        'view' => 'View',
        'create' => 'Create',
        'update' => 'Update',
        'delete' => 'Delete',
    ],

    'users' => [
        'model' => 'user',
        'fields' => [
            'name' => 'Name',
            'email' => 'Email',
            'role' => 'Role',
            'password' => 'Password',
            'password_confirmation' => 'Repeat password',
            'created_at' => 'Created',
        ],
        'password_keep' => 'Leave blank to keep the current password.',
        'cannot_delete_self' => 'You cannot delete your own account.',
    ],

    'roles' => [
        'model' => 'user role',
        'fields' => [
            'name' => 'Name',
            'is_super_admin' => 'Super admin',
            'users_count' => 'Users',
            'updated_at' => 'Updated',
        ],
        'super_admin_help' => 'Full access to every module, ignores the checkboxes below.',
        'permissions' => 'Permissions',
        'permissions_help' => 'Pick what this role can do in each module.',
        'in_use' => 'This role is still assigned to users.',
    ],

    'menus' => [
        'model' => 'menu item',
        'fields' => [
            'label' => 'Label',
            'icon' => 'Icon',
            'group' => 'Group',
            'sort' => 'Sort',
            'is_active' => 'Show in sidebar',
            'in_sidebar' => 'In sidebar',
        ],
        'label_help' => 'Leave blank to use the translated default.',
        'group_help' => 'Items with the same group sit together in the sidebar. Leave blank for the top level.',
        'is_active_help' => 'Hiding an item does not block access. Use User Roles for that.',
        'module' => 'Module: :key',
        'top_level' => 'Top level',
    ],

    'profile' => [
        'password_confirmation' => 'Repeat new password',
    ],

    'dashboard' => [
        'users' => 'Users',
        'new_this_month' => 'New this month',
        'since' => 'Since :date',
        'roles' => 'Roles',
        'newest_users' => 'Newest users',
        'joined' => 'Joined',
        'empty_heading' => 'No users yet',
        'empty_description' => 'Add one from Settings → Users.',
    ],
];
