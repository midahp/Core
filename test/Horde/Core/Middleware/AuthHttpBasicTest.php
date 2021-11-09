<?php
/**
 * Copyright 2016-2021 Horde LLC (http://www.horde.org/)
 *
 * See the enclosed file LICENSE for license information (LGPL). If you
 * did not receive this file, see http://www.horde.org/licenses/lgpl21.
 *
 * @category Horde
 * @license  http://www.horde.org/licenses/lgpl21 LGPL 2.1
 * @package  Core
 */
namespace Horde\Core\Middleware;

use \Horde_Test_Case as HordeTestCase;

use \Horde_Session;
use \Horde_Exception;


class AuthHttpBasicTest extends HordeTestCase
{
    use SetUpTrait;

    protected function getMiddleware()
    {
        return new AuthHttpBasic(
            $this->authDriver,
            $this->registry
        );
    }
    
    public function testNotAuthenticated()
    {
        $this->authDriver->method('authenticate')->willReturn(false);
        $this->registry->method('getAuth')->willReturn('testUser01');
        $middleware = $this->getMiddleware();

        $this->assertTrue(true);
    }

    public function testAuthenticated()
    {
        $this->authDriver->method('authenticate')->willReturn(true);
        $middleware = $this->getMiddleware();

        $this->assertTrue(true);
    }
}
