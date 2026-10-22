<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('page_type', 64)->default('other')->after('id');
            $table->string('title')->nullable()->after('page_type');
            $table->longText('content')->nullable()->after('title');
            $table->string('meta_title')->nullable()->after('content');
            $table->text('meta_description')->nullable()->after('meta_title');
        });

        foreach (DB::table('pages')->orderBy('id')->get() as $row) {
            DB::table('pages')->where('id', $row->id)->update([
                'title' => $row->name ?? 'Untitled',
                'page_type' => 'other',
            ]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->string('title')->nullable(false)->change();
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        foreach (DB::table('pages')->orderBy('id')->get() as $row) {
            DB::table('pages')->where('id', $row->id)->update([
                'name' => $row->title ?? 'Page',
            ]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn([
                'page_type',
                'title',
                'content',
                'meta_title',
                'meta_description',
            ]);
        });
    }
};
