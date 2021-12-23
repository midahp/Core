<?php

declare(strict_types=1);

namespace Horde\Core;

use Horde_Url;
use Horde;
use Horde_Registry;

class UrlStore
{
    protected Horde_Registry $registry;
    public function __construct(Horde_Registry $registry)
    {
        $this->registry = $registry;
    }

    public function getInitialPage(string $app, bool $full): Horde_Url
    {
        return Horde::Url($this->registry->getInitialPage($app), $full);
    }
}