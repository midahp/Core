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
use Horde\Http\Request;
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
   

    public function testNotAuthenticatedWithoutHeader()  
    {
        $username = 'testUser01';

        //not authenticated
        $this->authDriver->method('authenticate')->willReturn(false); 
        //new login request
        $this->registry->method('getAuth')->willReturn($username);
        
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        //no authorization header
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); 
        //NULL = notAuthenticated
        $notAuth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER'); 
        
        $this->assertNull($notAuth); 
        $this->assertEquals($username, $noAuthHeader); 
       
        
      
    }
    
    public function testNotAuthenticatedWithHeader()  
    {
        $username = 'testUser01';
        
        //not authenticated
        $this->authDriver->method('authenticate')->willReturn(false);

        $this->registry->method('getAuth')->willReturn($username);
        
        
        //with authorization header 
        $request = $this->requestFactory->createServerRequest('GET', '/test')->withHeader('Authorization','BASIC YXNkZjphc2QxMjM=');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $authHeader = $this->recentlyHandledRequest->hasHeader('Authorization');

        $notAuth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER');
        
        
        //header available
        $this->assertTrue($authHeader);  
        
        //NULL
        $this->assertNull($notAuth);      
        
        
    }
   
    public function testAuthenticatedwithoutHeader()  //test ob user authentifiziert
    {
        $username = 'testUser01';

        
     
         //authenticated USER AND PW accepted
        $this->authDriver->method('authenticate')->willReturn(true);
        
        $this->registry->method('getAuth')->willReturn($username);

        //check for header
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);

        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); 
        $auth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER',$username); 

        $this->assertEquals($username,$noAuthHeader);
        $this->assertNotNull($auth);
    
       
    }

    public function testAuthenticatedwithHeader(){

        $username = 'testUser01';
        
        
        $this->authDriver->method('authenticate')->willReturn(true);

        $this->registry->method('getAuth')->willReturn($username);
        
        
        //with authorization header
        $request = $this->requestFactory->createServerRequest('GET', '/test')->withHeader('Authorization','BASIC dGVzdFVzZXIwMTpwYXNzd29yZA==');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $authHeader = $this->recentlyHandledRequest->hasHeader('Authorization');

        $auth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER');
        
        
        //header available
        $this->assertTrue($authHeader);  
        
        //NULL= notauthenticated
        $this->assertEquals($username,$auth);      
        
        

    }



    

    
}
