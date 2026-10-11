<?php

return [
    'locale' => [
        'en' => 'English',
        'id' => 'Bahasa Indonesia',
    ],

    'modules' => [
        'users' => 'Pengguna',
        'roles' => 'Peran Pengguna',
        'menus' => 'Menu Pengguna',
    ],

    'menu_groups' => [
        'settings' => 'Pengaturan',
    ],

    'actions' => [
        'view' => 'Lihat',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
    ],

    'users' => [
        'model' => 'pengguna',
        'fields' => [
            'name' => 'Nama',
            'email' => 'Email',
            'role' => 'Peran',
            'password' => 'Kata sandi',
            'password_confirmation' => 'Ulangi kata sandi',
            'created_at' => 'Dibuat',
        ],
        'password_keep' => 'Kosongkan untuk tetap memakai kata sandi saat ini.',
        'cannot_delete_self' => 'Anda tidak dapat menghapus akun Anda sendiri.',
    ],

    'roles' => [
        'model' => 'peran pengguna',
        'fields' => [
            'name' => 'Nama',
            'is_super_admin' => 'Super admin',
            'users_count' => 'Pengguna',
            'updated_at' => 'Diperbarui',
        ],
        'super_admin_help' => 'Akses penuh ke semua modul, kotak centang di bawah diabaikan.',
        'permissions' => 'Hak akses',
        'permissions_help' => 'Pilih apa yang boleh dilakukan peran ini di setiap modul.',
        'in_use' => 'Peran ini masih dipakai oleh pengguna.',
    ],

    'menus' => [
        'model' => 'item menu',
        'fields' => [
            'label' => 'Label',
            'icon' => 'Ikon',
            'group' => 'Grup',
            'sort' => 'Urutan',
            'is_active' => 'Tampilkan di sidebar',
            'in_sidebar' => 'Di sidebar',
        ],
        'label_help' => 'Kosongkan untuk memakai nama bawaan sesuai bahasa.',
        'group_help' => 'Item dengan grup yang sama dikelompokkan di sidebar. Kosongkan untuk level teratas.',
        'is_active_help' => 'Menyembunyikan item tidak memblokir akses. Gunakan Peran Pengguna untuk itu.',
        'module' => 'Modul: :key',
        'top_level' => 'Level teratas',
    ],

    'profile' => [
        'password_confirmation' => 'Ulangi kata sandi baru',
    ],

    'dashboard' => [
        'users' => 'Pengguna',
        'new_this_month' => 'Baru bulan ini',
        'since' => 'Sejak :date',
        'roles' => 'Peran',
        'newest_users' => 'Pengguna terbaru',
        'joined' => 'Bergabung',
        'empty_heading' => 'Belum ada pengguna',
        'empty_description' => 'Tambahkan dari Pengaturan → Pengguna.',
    ],
];
