<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * A blank label/known group now means "use the translated default", so the sidebar follows the active language.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->string('label')->nullable()->change();
        });

        // Seeded English defaults become translation-driven; admin-renamed labels are kept.
        DB::table('menus')->whereIn('label', ['Users', 'User Roles', 'User Menu'])->update(['label' => null]);
        DB::table('menus')->where('group', 'Settings')->update(['group' => 'settings']);
    }

    public function down(): void
    {
        DB::table('menus')->whereNull('label')->update(['label' => DB::raw('`key`')]);
        DB::table('menus')->where('group', 'settings')->update(['group' => 'Settings']);

        Schema::table('menus', function (Blueprint $table) {
            $table->string('label')->nullable(false)->change();
        });
    }
};
