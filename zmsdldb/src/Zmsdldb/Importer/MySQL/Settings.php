<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Settings extends Base
{
    protected bool $getCurrentEntitys = false;
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\MySQL\Entity\Setting::class;

    /** @psalm-api */
    #[\Override]
    public function runImport(): bool
    {
        try {
            $shifted = array_shift($this->importData);
            if (!is_array($shifted)) {
                throw new \RuntimeException('Invalid settings import data');
            }
            $this->importData = $shifted;

            $settings = $this->importData['settings'] ?? [];
            if (!is_array($settings)) {
                throw new \RuntimeException('Invalid settings payload');
            }
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
