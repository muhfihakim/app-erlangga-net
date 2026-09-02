<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('settings')) {
                // MikroTik
                if ($host = \App\Models\Setting::get('mikrotik_host')) config(['mikrotik.host' => $host]);
                if ($port = \App\Models\Setting::get('mikrotik_port')) config(['mikrotik.port' => (int) $port]);
                if ($user = \App\Models\Setting::get('mikrotik_user')) config(['mikrotik.username' => $user]);
                if ($pass = \App\Models\Setting::get('mikrotik_pass')) config(['mikrotik.password' => $pass]);
                if ($iface = \App\Models\Setting::get('mikrotik_interface')) config(['mikrotik.interface' => $iface]);
                if ($timeout = \App\Models\Setting::get('mikrotik_timeout')) config(['mikrotik.timeout' => (int) $timeout]);

                // OLT
                if ($oltHost = \App\Models\Setting::get('olt_host')) config(['olt.host' => $oltHost]);
                if ($oltPort = \App\Models\Setting::get('olt_port')) config(['olt.port' => (int) $oltPort]);
                if ($oltUser = \App\Models\Setting::get('olt_username')) config(['olt.username' => $oltUser]);
                if ($oltPass = \App\Models\Setting::get('olt_password')) config(['olt.password' => $oltPass]);

                // FreeRADIUS Database
                if ($radHost = \App\Models\Setting::get('db_radius_host')) config(['database.connections.radius.host' => $radHost]);
                if ($radPort = \App\Models\Setting::get('db_radius_port')) config(['database.connections.radius.port' => $radPort]);
                if ($radDb = \App\Models\Setting::get('db_radius_database')) config(['database.connections.radius.database' => $radDb]);
                if ($radUser = \App\Models\Setting::get('db_radius_username')) config(['database.connections.radius.username' => $radUser]);
                if ($radPass = \App\Models\Setting::get('db_radius_password')) config(['database.connections.radius.password' => $radPass]);

                // Xendit Payment Gateway
                if ($xenKey = \App\Models\Setting::get('xendit_secret_key')) config(['services.xendit.secret_key' => $xenKey]);
                if ($xenToken = \App\Models\Setting::get('xendit_webhook_token')) config(['services.xendit.webhook_token' => $xenToken]);
            }
        } catch (\Throwable $e) {
            // Silently fallback if DB is not ready
        }
    }
}
