<?php

namespace NewIDC\Virtualmin;

use Illuminate\Support\ServiceProvider;
use NewIDC\Plugin\Facade\PluginManager;

class VirtualminServiceProvider extends ServiceProvider
{
    public function boot()
    {
        PluginManager::register(new Plugin());
    }
}