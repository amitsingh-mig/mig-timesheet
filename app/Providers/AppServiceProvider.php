<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Set default string length for database migrations
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);

        // Configure validation rules
        \Illuminate\Support\Facades\Validator::extend('strong_password', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]/', $value);
        });

        \Illuminate\Support\Facades\Validator::replacer('strong_password', function ($message, $attribute, $rule, $parameters) {
            return 'The ' . $attribute . ' must contain at least one uppercase letter, one lowercase letter, one number, and one special character.';
        });

        // Configure Blade directives for security
        \Illuminate\Support\Facades\Blade::directive('csrf', function () {
            return '<?php echo csrf_field(); ?>';
        });

        \Illuminate\Support\Facades\Blade::directive('method', function ($expression) {
            return "<?php echo method_field($expression); ?>";
        });
    }
}
