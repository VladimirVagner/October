<?php

namespace Wms\Settings\Classes;

use Backend\Models\User as BackendUser;
use Cms\Classes\Page as CmsPage;
use October\Rain\Parse\Yaml;
use System\Classes\PluginManager;
use Wms\Settings\Models\Settings as SettingsModel;

class Extend
{
    protected const MAIN_TAB = 'wms.settings::settings.tabs.main';
    /**
     * @var PluginManager
     */
    protected static $manager;
    protected static $settingsPermissions = [];
    protected static $settings = [];
    protected static $validator = [];
    protected static $validatorAttributeNames = [];
    protected static $defaults = [];
    protected static $casts = [];

    public static function addSettingsToTheme()
    {
        \Event::listen('cms.page.beforeRenderPage', function($controller, $page) {
            /* @var $page CmsPage */
            if (!$settings = SettingsModel::instance()) {
                return;
            }
            $page->theme->settings = $settings;
        });
    }

    public static function collectSettings()
    {
        if (!file_exists($path = __DIR__ . '/../models/settings/fields.yaml')) {
            return;
        }
        self::$manager = PluginManager::instance();

        self::getSettings($path);
        if (!count(self::$settings)) {
            return;
        }

        self::extendSettingsModel();

        \Event::listen('system.extendConfigFile', function($filePath, $config) {
            if ($filePath != '/plugins/wms/settings/models/settings/fields.yaml') {
                return $config;
            }

            return self::extendConfig($config);
        });

        return array_keys(self::$settingsPermissions);
    }

    protected static function extendConfig($config)
    {
        if (isset($config['tabs']['fields'])) {
            foreach ($config['tabs']['fields'] as $name => $field) {
                $tab = str_replace(self::MAIN_TAB, '$main', $field['tab']);
                unset($field['tab']);
                self::$settings[$tab][$name] = $field;
            }
        }

        self::sortSettings(self::$settings);

        $user = \BackendAuth::getUser();
        /* @var $user BackendUser */

        $fields = [];
        foreach (self::$settings as $tab => $settingsFields) {
            $permissions = [];
            $issetPermissions = false;
            if (isset($settingsFields['permissions'])) {
                $permissions = explode('|', $settingsFields['permissions']);
                unset($settingsFields['permissions']);
                $issetPermissions = true;
            }
            foreach ($settingsFields as $name => $field) {
                $field['tab'] = str_replace('$main', self::MAIN_TAB, $tab);
                $hasPermissions = false;
                foreach ($permissions as $permission) {
                    $hasPermissions = $hasPermissions || $user->hasPermission($permission);
                }
                if (isset($field['permissions'])) {
                    $issetPermissions = true;
                    $fieldPermissions = explode('|', $field['permissions']);
                    unset($field['permissions']);
                    if (is_array($fieldPermissions) && !empty($fieldPermissions)) {
                        foreach ($fieldPermissions as $permission) {
                            $hasPermissions = $hasPermissions || $user->hasPermission($permission);
                        }
                    }
                }
                if ($issetPermissions && !$hasPermissions) {
                    $field['hidden'] = true;
                }

                $fields[$name] = $field;
            }
        }

        unset($config['tabs']);
        $config['tabs']['fields'] = $fields;

        return $config;
    }

    protected static function extendSettingsModel()
    {
        if (count(self::$casts) || count(self::$defaults) || count(self::$validator) ||
            count(self::$validatorAttributeNames)) {
            SettingsModel::extend(function(SettingsModel $model) {
                foreach (self::$validator as $name => $rule) {
                    if (!isset($model->rules[$name])) {
                        $model->rules[$name] = $rule;
                    }
                }
                foreach (self::$defaults as $name => $val) {
                    if (!isset($model->defaults[$name])) {
                        $model->defaults[$name] = $val;
                    }
                }
                foreach (self::$validatorAttributeNames as $name => $val) {
                    $model->setValidationAttributeName($name, $val);
                }
                if (count(self::$casts)) {
                    $model->addCasts(self::$casts);
                }
            });
        }
    }

    protected static function getSettings($defaultSettings)
    {

        foreach (self::$manager->getPlugins() as $pluginName => $plugin) {
            if(self::$manager->getPluginPath($pluginName) != "/var/www/october/plugins/october/demo")
            if (file_exists($path = self::$manager->getPluginPath($pluginName) . '/settings/fields.yml') ||
                file_exists($path = self::$manager->getPluginPath($pluginName) . '/settings/fields.yaml')) {
                $array = app(Yaml::class)->parseFile($path);
                self::updateSettings($array);
            }
        }

        $array = app(Yaml::class)->parseFile($defaultSettings);
        if (isset($array['tabs'])) {
            self::updateSettings($array['tabs'], true);
            unset($array['tabs']);
        } elseif (isset($array['secondaryTabs'])) {
            self::updateSettings($array['secondaryTabs'], true);
            unset($array['secondaryTabs']);
        }
        self::updateSettings($array, true);
    }

    protected static function updateSettings(&$array, $onlyPermissions = false)
    {
        foreach ($array as $tab => $settingsFields) {
            foreach ($settingsFields as $name => $field) {
                if (!$onlyPermissions) {
                    if (isset($field['cast'])) {
                        self::$casts[$name] = $field['cast'];
                        unset($field['cast']);
                    }
                    if (isset($field['rules'])) {
                        if (is_array($field['rules'])) {
                            foreach ($field['rules'] as $ruleName => $rule) {
                                if (is_array($rule)) {
                                    if (isset($rule['rule'])) {
                                        self::$validator[$ruleName] = $rule['rule'];
                                    }
                                    if (isset($rule['name'])) {
                                        self::$validatorAttributeNames[$ruleName] = $rule['name'];
                                    }
                                } else {
                                    self::$validator[$ruleName] = $rule;
                                }
                            }
                        } else {
                            self::$validator[$name] = $field['rules'];
                        }
                        unset($field['rules']);
                    }
                    if (isset($field['default'])) {
                        self::$defaults[$name] = $field['default'];
                    } elseif (is_array($field)) {
                        self::$defaults[$name] = '';
                    }
                    self::$settings[$tab][$name] = $field;
                }

                if ($name == 'permissions') {
                    self::addPermissions($field);
                } elseif (isset($field['permissions'])) {
                    self::addPermissions($field['permissions']);
                }
            }
        }
    }

    protected static function addPermissions($permissions)
    {
        $permissions = explode('|', $permissions);
        foreach ($permissions as $permission) {
            self::$settingsPermissions[$permission] = true;
        }
    }

    protected static function sortSettings(&$settings = null)
    {
        $sort = function($arr1, $arr2) {
            $order1 = -1;
            if (isset($arr1['order'])) {
                $order1 = $arr1['order'];
                unset($arr1['order']);
            }
            $order2 = -1;
            if (isset($arr2['order'])) {
                $order2 = $arr2['order'];
                unset($arr2['order']);
            }

            return $order1 == $order2 ? 0 : ($order1 < $order2 ? -1 : 1);
        };

        if (empty($settings)) {
            $settings = &self::$settings;
        }

        if (empty($settings)) {
            return;
        }

        uasort($settings, $sort);
        foreach ($settings as $tab => $fields) {
            if (isset($fields['order'])) {
                unset($settings[$tab]['order']);
            }
            uasort($settings[$tab], $sort);
        }
    }
}
