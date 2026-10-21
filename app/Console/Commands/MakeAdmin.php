<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class MakeAdmin extends Command
{
    protected $signature = 'app:make-admin {email : E-mailadres van het bestaande account}';
    protected $description = 'Geef een bestaand account de adminrol';

    public function handle(): int
    {
        $user = User::where('email', strtolower($this->argument('email')))->first();
        if (!$user) {
            $this->error('Geen account gevonden met dit e-mailadres.');
            return self::FAILURE;
        }

        $user->update(['role' => 'admin']);
        $this->info("{$user->name} heeft nu de adminrol.");
        return self::SUCCESS;
    }
}
