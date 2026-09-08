<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');
            $table->string('name');
            $table->timestamps();
            $table->unique(['portfolio_id', 'number']);
        });

        $defaults = [
            'K1W1' => 'Afstemmen en plannen',
            'K1W2' => 'Ontwerpen',
            'K1W3' => 'Realisatie',
            'K1W4' => 'Testen',
            'K1W5' => 'Verbetervoorstellen',
            'K2W1' => 'Projectteam',
            'K2W2' => 'Presenteren',
            'K2W3' => 'Evalueer',
        ];

        foreach (DB::table('portfolios')->pluck('id') as $portfolioId) {
            foreach ($defaults as $code => $name) {
                DB::table('work_processes')->insert([
                    'portfolio_id' => $portfolioId,
                    'number' => array_search($code, array_keys($defaults), true) + 1,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        foreach ($defaults as $code => $name) {
            $number = array_search($code, array_keys($defaults), true) + 1;
            DB::table('evidence')
                ->whereIn('work_process', [$code, $code . ' - ' . $name])
                ->update(['work_process' => (string) $number]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('work_processes');
    }
};
