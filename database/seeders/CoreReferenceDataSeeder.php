<?php

namespace Database\Seeders;

use App\Models\EducationProgram;
use App\Models\Group;
use Illuminate\Database\Seeder;

class CoreReferenceDataSeeder extends Seeder
{
    public function run(): void
    {
        $program = EducationProgram::where('code', '25998')->first()
            ?? EducationProgram::where('name', 'Software Developer')->first();
        if ($program) {
            $program->update(['code' => '25998', 'name' => 'Software Developer']);
        } else {
            $program = EducationProgram::create(['code' => '25998', 'name' => 'Software Developer']);
        }

        $tasks = [
            ['task_number' => 'B1-K1', 'name' => 'Realiseert software', 'processes' => [
                ['code' => 'B1-K1-W1', 'name' => 'Stemt opdracht af, plant werkzaamheden en bewaakt de voortgang'],
                ['code' => 'B1-K1-W2', 'name' => 'Maakt een technisch ontwerp voor software'],
                ['code' => 'B1-K1-W3', 'name' => 'Realiseert (onderdelen van) software'],
                ['code' => 'B1-K1-W4', 'name' => 'Test software'],
                ['code' => 'B1-K1-W5', 'name' => 'Doet verbetervoorstellen voor de software'],
            ]],
            ['task_number' => 'B1-K2', 'name' => 'Voert ICT-projecten uit', 'processes' => [
                ['code' => 'B1-K2-W1', 'name' => 'Werkt samen in een projectteam'],
                ['code' => 'B1-K2-W2', 'name' => 'Presenteert het opgeleverde werk'],
                ['code' => 'B1-K2-W3', 'name' => 'Evalueert de samenwerking'],
            ]],
        ];

        $number = 1;
        foreach ($tasks as $taskData) {
            $processes = $taskData['processes'];
            unset($taskData['processes']);
            $task = $program->coreTasks()->updateOrCreate(['task_number' => $taskData['task_number']], $taskData);
            foreach ($processes as $processData) {
                $task->workProcesses()->updateOrCreate(['code' => $processData['code']], ['number' => $number++, ...$processData]);
            }
        }

        foreach ([
            'nieuw' => 'Nieuw',
            'herkansen' => 'Herkansen',
            'geslaagd' => 'Geslaagd',
            'weg' => 'Weg',
        ] as $status => $name) {
            Group::firstOrCreate(['lifecycle_status' => $status], ['name' => $name]);
        }
    }
}
