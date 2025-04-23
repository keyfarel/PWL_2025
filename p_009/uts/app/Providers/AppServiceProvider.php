<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Format untuk Rupiah
        Blade::directive('rupiah', function ($expression) {
            return "<?php echo format_rupiah($expression); ?>";
        });

        // Format untuk Ribuan
        Blade::directive('ribuan', function ($expression) {
            return "<?php echo format_ribuan($expression); ?>";
        });

        if (app()->environment('local')) {
            DB::listen(function ($query) {
                Log::info("Query Time: {$query->time}ms | SQL: {$query->sql} | Bindings: " . json_encode($query->bindings));
            });
        }
    }
}
