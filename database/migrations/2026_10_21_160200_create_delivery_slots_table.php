<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('slot_key', 20)->unique();
            $table->string('label', 80);
            $table->time('start_time');
            $table->time('end_time');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->unsignedTinyInteger('status')->default(1);
            $table->timestamps();
        });

        $now = now();
        $rows = [];
        $start = Carbon::createFromTime(9, 0);

        for ($i = 1; $i <= 12; $i++) {
            $end = (clone $start)->addHour();
            $rows[] = [
                'slot_key' => $start->format('H:i').'-'.$end->format('H:i'),
                'label' => $start->format('h:i A').' – '.$end->format('h:i A'),
                'start_time' => $start->format('H:i:s'),
                'end_time' => $end->format('H:i:s'),
                'sort_order' => $i,
                'status' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
            $start = $end;
        }

        DB::table('delivery_slots')->insert($rows);
    }

    public function down(): void
    {
        Schema::dropIfExists('delivery_slots');
    }
};
