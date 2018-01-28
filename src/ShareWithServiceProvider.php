<?php

namespace Harlekoy\ShareWith;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Harlekoy\ShareWith\Contracts\Document as DocumentContract;

class ShareWithServiceProvider extends ServiceProvider
{
    /**
     * Boot service provider.
     *
     * @param  DocumentShareWithRegistrar $shareWithLoader
     *
     * @return voide
     */
    public function boot(DocumentShareWithRegistrar $shareWithLoader)
    {
        if (isNotLumen()) {
            $this->publishes([
                __DIR__.'/../config/sharewith.php' => config_path('sharewith.php'),
            ], 'config');

            if (! class_exists('CreateShareWithTables')) {
                $timestamp = date('Y_m_d_His', time());

                $this->publishes([
                    __DIR__.'/../database/migrations/create_share_with_tables.php.stub' => $this->app->databasePath()."/migrations/{$timestamp}_create_share_with_tables.php",
                ], 'migrations');
            }
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\ShareWith::class,
            ]);
        }

        $this->registerModelBindings();

        // $shareWithLoader->registerShareWith();
    }

    /**
     * Register service provider.
     *
     * @return void
     */
    public function register()
    {
        if (isNotLumen()) {
            $this->mergeConfigFrom(
                __DIR__.'/../config/sharewith.php',
                'sharewith'
            );
        }

        // $this->registerBladeExtensions();
    }

    /**
     * Register model bindings.
     *
     * @return void
     */
    protected function registerModelBindings()
    {
        $config = $this->app->config['sharewith.models'];

        $this->app->bind(DocumentContract::class, $config['permission']);
    }

    /**
     * Register blade extensions.
     *
     * @return void
     */
    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function (BladeCompiler $bladeCompiler) {
            $bladeCompiler->directive('shared', function ($arguments) {
                list($role, $guard) = explode(',', $arguments.',');

                return "<?php if(auth()->user()->hasRole({$role})): ?>";
            });
            $bladeCompiler->directive('endshared', function () {
                return '<?php endif; ?>';
            });
        });
    }
}
