<?php

namespace Wms\Settings;

use System\Classes\PluginBase;
use System\Classes\SettingsManager;

/**
 * Settings Plugin Information File
 */
class Plugin extends PluginBase
{
    protected $settingsPermissions = [];

    public function pluginDetails()
    {
        return [
            'name'        => 'wms.settings::plugin.name',
            'description' => 'wms.settings::plugin.description',
            'author'      => 'WMStudio',
            'icon'        => 'icon-leaf',
        ];
    }

    public function register()
    {
        $this->settingsPermissions = Classes\Extend::collectSettings();
        Classes\Extend::addSettingsToTheme();
    }

    public function registerPermissions()
    {
        return [
            'wms.global.settings' => [
                'tab'   => 'WMStudio',
                'label' => 'wms.settings::plugin.permissions',
                'order' => 1,
            ],
        ];
    }

    public function registerSettings()
    {
        return [
            'settings' => [
                'label'       => 'wms.settings::plugin.name',
                'description' => 'wms.settings::settings.description',
                'category'    => SettingsManager::CATEGORY_CMS,
                'icon'        => 'icon-cog',
                'class'       => Models\Settings::class,
                'permissions' => $this->settingsPermissions,
                'order'       => 200,
            ],
        ];
    }
}
