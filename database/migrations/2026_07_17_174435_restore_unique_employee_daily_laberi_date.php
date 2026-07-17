<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Keep the newest active row when duplicates exist.
        $duplicates = DB::table('employee_daily_laberi_entries')
            ->select('employee_id', 'laberi_date')
            ->whereNull('deleted_at')
            ->groupBy('employee_id', 'laberi_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            $keepId = DB::table('employee_daily_laberi_entries')
                ->where('employee_id', $duplicate->employee_id)
                ->whereDate('laberi_date', $duplicate->laberi_date)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->value('id');

            DB::table('employee_daily_laberi_entries')
                ->where('employee_id', $duplicate->employee_id)
                ->whereDate('laberi_date', $duplicate->laberi_date)
                ->whereNull('deleted_at')
                ->where('id', '!=', $keepId)
                ->delete();
        }

        Schema::table('employee_daily_laberi_entries', function (Blueprint $table) {
            $table->string('active_laberi_key')->nullable()->after('daily_shift');
        });

        DB::table('employee_daily_laberi_entries')
            ->whereNull('deleted_at')
            ->orderBy('id')
            ->chunkById(100, function ($rows): void {
                foreach ($rows as $row) {
                    DB::table('employee_daily_laberi_entries')
                        ->where('id', $row->id)
                        ->update([
                            'active_laberi_key' => $row->employee_id.'|'.$row->laberi_date,
                        ]);
                }
            });

        Schema::table('employee_daily_laberi_entries', function (Blueprint $table) {
            $table->unique('active_laberi_key', 'employee_daily_laberi_active_key_unique');
        });
    }

    public function down(): void
    {
        Schema::table('employee_daily_laberi_entries', function (Blueprint $table) {
            $table->dropUnique('employee_daily_laberi_active_key_unique');
            $table->dropColumn('active_laberi_key');
        });
    }
};
