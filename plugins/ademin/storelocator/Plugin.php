<?php

namespace ADemin\StoreLocator;

use Backend;
use System\Classes\PluginBase;

class Plugin extends PluginBase
{

    public function pluginDetails()
    {
        return [
            'name'        => 'Store Locator',
            'description' => 'Manage and display your stores on Google Maps',
            'author'      => 'Alexey Demin',
            'icon'        => 'icon-map-marker',
            'homepage'    => 'https://github.com/alexeydemin/storelocator'
        ];
    }

    public function register()
    {

    }

    public function boot()
    {

    }

    public function registerComponents()
    {
        return [
            'ADemin\StoreLocator\Components\StoreLocator' => 'storeLocator',
        ];
    }

    public function registerPermissions()
    {
        return [];
    }

    public function registerNavigation()
    {
        return [
            'storelocator' => [
                'label'       => 'Филиалы',
                'url'         => Backend::url('ademin/storelocator/stores'),
                'icon'        => 'icon-map-marker',
                'permissions' => ['ademin.storelocator.*'],
                'order'       => 500,
            ],
        ];
    }

    public function registerFormWidgets()
    {
        return [
            'ADemin\StoreLocator\FormWidgets\AddressFinder' => [
                'label' => 'Поиск адреса',
                'code'  => 'addressfinder'
            ]
        ];
    }

    public function registerPageSnippets()
    {
        return [
            '\ADemin\StoreLocator\Components\StoreLocator' => 'storelocator'
        ];
    }

    public function registerSettings()
    {
        return [
            'storelocator' => [
                'label'       => 'Филиалы',
                'description' => 'Управление настройками плагина',
                'category'    => 'Plugins',
                'icon'        => 'icon-globe',
                //'url'         => Backend::url('ademin/storelocator/settings'),
                'class'       => 'ADemin\StoreLocator\Models\Settings',
                'order'       => 500,
                'keywords'    => 'store location map'
            ]
        ];
    }
}
