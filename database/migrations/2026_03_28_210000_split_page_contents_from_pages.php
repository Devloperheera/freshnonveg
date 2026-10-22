<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('page_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });

        Schema::table('pages', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
        });

        $typeLabels = [
            'privacy_policy' => 'Privacy Policy',
            'terms_conditions' => 'Terms & Conditions',
            'about_us' => 'About Us',
            'shipping_policy' => 'Shipping Policy',
            'return_policy' => 'Return & Refund Policy',
            'faq' => 'FAQ',
            'contact' => 'Contact Info',
            'other' => 'Other',
        ];

        foreach (DB::table('pages')->orderBy('id')->get() as $row) {
            $name = $row->title
                ?? ($typeLabels[$row->page_type ?? ''] ?? null)
                ?? 'Page '.$row->id;

            DB::table('pages')->where('id', $row->id)->update(['name' => $name]);

            DB::table('page_contents')->insert([
                'page_id' => $row->id,
                'title' => $row->title ?? $name,
                'content' => $row->content,
                'meta_title' => $row->meta_title,
                'meta_description' => $row->meta_description,
                'status' => $row->status ?? 1,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn(['page_type', 'title', 'content', 'meta_title', 'meta_description']);
        });
    }

    public function down(): void
    {
        Schema::table('pages', function (Blueprint $table) {
            $table->string('page_type', 64)->default('other')->after('id');
            $table->string('title')->nullable()->after('page_type');
            $table->longText('content')->nullable()->after('title');
            $table->string('meta_title')->nullable()->after('content');
            $table->text('meta_description')->nullable()->after('meta_title');
        });

        foreach (DB::table('page_contents')->orderBy('id')->get() as $content) {
            $page = DB::table('pages')->where('id', $content->page_id)->first();
            if (! $page) {
                continue;
            }
            DB::table('pages')->where('id', $page->id)->update([
                'title' => $content->title,
                'content' => $content->content,
                'meta_title' => $content->meta_title,
                'meta_description' => $content->meta_description,
                'status' => $content->status,
            ]);
        }

        Schema::table('pages', function (Blueprint $table) {
            $table->dropColumn('name');
        });

        Schema::dropIfExists('page_contents');
    }
};
