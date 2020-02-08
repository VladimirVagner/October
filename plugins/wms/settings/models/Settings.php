<?php

namespace Wms\Settings\Models;

use Model;
use October\Rain\Database\Traits\Validation;

class Settings extends Model
{
    use Validation;

    /**
     * @var string The database table used by the model.
     */
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'wms_settings';

    public $settingsFields = 'fields.yaml';

    public $rules = [
        'site_name' => 'string|min:2',
        'site_name_delimiter' => 'string|min:1',
    ];

    public $attributeNames = [];

    /**
     * @var array Guarded fields
     */
    protected $guarded = ['*'];
    public $defaults = [
        'site_name' => 'Influx CMS',
        'site_name_delimiter' => '-',
        'privacy_policy' => '',
        'personal_data' => '',
    ];
    public $casts = [];

    /**
     * @var array Relations
     */
    public $morphToMany = [];
    public $hasOne = [];
    public $hasMany = [];
    public $belongsTo = [];
    public $belongsToMany = [];
    public $morphTo = [];
    public $morphOne = [];
    public $morphMany = [];
    public $attachOne = [];
    public $attachMany = [];

    private function useDefaults()
    {
        if (array_key_exists('value', $this->original)) {
            $this->original = json_decode($this->original['value'], true);
        }

        foreach ($this->defaults as $name => $value) {
            if (!isset($this->$name)) {
                $this->$name = $value;
            }
        }
    }

    public function initSettingsData()
    {
        $this->useDefaults();
    }

    public function afterFetch()
    {
        $this->useDefaults();
    }

    public function getOriginal($key = null, $default = null)
    {
        if ($default === null && array_key_exists($key, $this->defaults)) {
            $default = $this->defaults[$key];
        }

        return parent::getOriginal($key, $default);
    }

    public function filterFields($fields, $context = null)
    {
        \Event::fire('wms.settings::filterFields', [$fields, $context]);
    }
}
