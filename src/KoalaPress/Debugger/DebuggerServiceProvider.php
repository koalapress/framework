<?php

namespace KoalaPress\Debugger;

use Illuminate\Support\ServiceProvider;
use Tracy\Debugger;

class DebuggerServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function boot(): void
    {
        $mode = !app()->isProduction() ?
            Debugger::Development :
            Debugger::Production;

        Debugger::enable($mode);
    }
}
