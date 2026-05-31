<?php

namespace BO\Zmsentities;

class Mimepart extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static string $schema = "mimepart.json";

    /**
     * @return string[]
     *
     * @psalm-return array{mime: '', content: ''}
     */
    public function getDefaults()
    {
        return [
            'mime' => '',
            'content' => '',
            ];
    }

    public function isBase64Encoded(): bool
    {
        return ($this->base64) ? true : false;
    }

    public function isHtml(): bool
    {
        return ($this->mime == 'text/html') ? true : false;
    }

    public function isText(): bool
    {
        return ($this->mime == 'text/plain') ? true : false;
    }

    public function isIcs(): bool
    {
        return ($this->mime == 'text/calendar') ? true : false;
    }

    public function getContent()
    {
        return ($this->isBase64Encoded()) ? \base64_decode($this->content) : $this->content;
    }

    /**
     * @return null|string|string[]
     *
     * @psalm-return array<string>|null|string
     */
    public function getExtension(): array|string|null
    {
        return preg_replace('#^.*/#', '', $this->mime);
    }
}
