<?php namespace ADemin\StoreLocator\Components;

use Cms\Classes\ComponentBase;

class StoreLocator extends ComponentBase
{
    public function componentDetails()
    {
        return [
            'name'        => 'Компоненты карты филиалов',
            'description' => 'Google Maps с отмеченными филиалами'
        ];
    }

    public function defineProperties()
    {
        return [];
    }

    public function onRun()
    {
        $this->addCss('/plugins/ademin/storelocator/assets/css/store-locator.css');
    }
}
