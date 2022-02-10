<?php

namespace BO\Dldb\Importer\MySQL;

class Settings extends Base
{
    protected $getCurrentEntitys = false;
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Setting';

    public function runImport() : bool
    {
        try {
            $this->importData = array_shift($this->importData);
           
            $settings = $this->importData['settings'];
            $settings['boroughs'] = json_encode(($this->importData['boroughs'] ?? ''));
            $settings['office'] = json_encode(($this->importData['office'] ?? ''));

            foreach ($settings as $name => $value) {
                $setting = $this->createEntity(['name' => $name, 'value' => $value]);
                $setting->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }

        return true;
    }
}
