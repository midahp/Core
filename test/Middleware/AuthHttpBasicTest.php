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

namespace Horde\Core\Test\Middleware;

use Horde\Core\Middleware\AuthHttpBasic;

use Horde\Test\TestCase;

class AuthHttpBasicTest extends TestCase
{
    use SetUpTrait;

    protected function getMiddleware()
    {
        return new AuthHttpBasic(
            $this->authDriver,
            $this->registry
        );
    }
   

    public function testNotAuthenticated()  //test ob user nicht authentifiziert
    {
        $username = 'testUser01';
        
        $this->authDriver->method('authenticate')->willReturn(false);
        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); // ohne authorization header
   
        $yesAuthHeader= $this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER'); //nicht authentifiziert mit authorization Header
       
        $notAuth=$this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER')->withAttribute('HORDE_AUTHENTICATED_USER','');
        $this->assertNull($notAuth); //not authenticated
        $this->assertEquals($username, $noAuthHeader);
        $this->assertNull($yesAuthHeader); //has header but did not authenticate
        
      
    }
    
   
    public function testAuthenticated()  //test ob user authentifiziert
    {
        $username = 'testUser01';
        
         
        $this->authDriver->method('authenticate')->willReturn(true);
        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); //authentifiziert ohne authheader
       // $Auth=$this->recentlyHandledRequest->withheader('Authorization')->getAttribute('HORDE_AUTHENTICATED_USER');
        $this->assertEquals($username, $noAuthHeader);
       // $this->assertNull($Auth);
       
    }
    
}
