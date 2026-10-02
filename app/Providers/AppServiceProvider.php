<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        \Illuminate\Auth\Notifications\ResetPassword::toMailUsing(function ($user, string $token) {
            $url = route('password.reset', [
                'token' => $token,
                'email' => $user->getEmailForPasswordReset(),
            ]);
            $minutes = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

            return (new \Illuminate\Notifications\Messages\MailMessage)
                ->subject('Stel je nieuwe wachtwoord in')
                ->greeting('Hallo '.$user->name.',')
                ->line('Je hebt een nieuw wachtwoord aangevraagd voor je studentenportfolio.')
                ->action('Nieuw wachtwoord kiezen', $url)
                ->line('Deze link is '.$minutes.' minuten geldig en kan één keer worden gebruikt.')
                ->line('Heb je dit niet aangevraagd? Dan hoef je niets te doen.')
                ->salutation('Met vriendelijke groet, '.config('app.name'));
        });
    }
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

}
