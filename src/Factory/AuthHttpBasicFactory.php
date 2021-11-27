<?php

declare(strict_types=1);

namespace Horde\Core\Factory;

use Horde\Core\Middleware\AuthHttpBasic;
use Horde_Core_Factory_Injector as InjectorFactory;
use Horde_Injector as Injector;

class AuthHttpBasicFactory extends InjectorFactory
{
    /**
     * Create an instance of the AuthHttpBasic middleware
     *
     * This will leverage the configured horde base authentication driver
     *
     * @param Injector $injector
     * @return AuthHttpBasic
     */
    public function create(Injector $injector): AuthHttpBasic
    {
        $driver = $injector->getInstance('Horde_Core_Factory_Auth')->create();
        return new AuthHttpBasic($driver);
    }
}
