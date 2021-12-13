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
   

    public function testNotAuthenticatedWithoutHeader()  //test ob user nicht authentifiziert
    {
        $username = 'testUser01';
        
        $this->authDriver->method('authenticate')->willReturn(false); //not authenticated
        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); //ohne authorization header
     
        $notAuth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER'); //nicht authorisiert->willreturn(false)
        
        $this->assertNull($notAuth); //not authenticated
        $this->assertEquals($username, $noAuthHeader); 
       
        
      
    }
    
    public function testNotAuthenticatedWithHeader()  //test ob user nicht authentifiziert
    {
        $username = 'testUser01';
        
        $this->authDriver->method('authenticate')->willReturn(false);
        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $AuthHeader = !$this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); //mit authorization header
        $notAuth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER');

        $this->assertFalse($AuthHeader); //not no_auth_header
        
        $this->assertNull($notAuth); //not authenticated
        
        
      
    }
   
    public function testAuthenticated()  //test ob user authentifiziert
    {
        $username = 'testUser01';
        
         
        $this->authDriver->method('authenticate')->willReturn(true);
        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); //authentifiziert
        $Auth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER',$username);
        
        print_r($noAuthHeader);
        $this->assertEquals($username, $Auth); 
        $this->assertEquals($username, $noAuthHeader); 
        
    }

    

    
}
