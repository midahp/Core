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
        $password = 'testPw';
        $authString = base64_encode(sprintf('%s:%s', $username, $password));

        //with Header
        $request = $this->requestFactory->createServerRequest('GET', '/test')->withHeader('Authorization','BASIC ' . $authString);

        //not authenticated
        $this->authDriver->method('authenticate')->willReturn(false);

        $this->registry->method('getAuth')->willReturn($username);
        
        
        
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        $authHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER');

        $authenticatedUser=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER');
        
        
        
        $this->assertNull($authHeader);  
        
        
        $this->assertNull($authenticatedUser);      
        
        
    }
   
    public function testAuthenticatedwithoutHeader()  //test ob user authentifiziert
    {
        $username = 'testUser01';
        $password = 'testPw';
        $authString = base64_encode(sprintf('%s:%s', $username, $password));

        

        
     
        //authenticated USER AND PW accepted
        $this->authDriver->method('authenticate')->willReturn(true);
        
        $this->registry->method('getAuth')->willReturn($username);

        //check for header
        $request = $this->requestFactory->createServerRequest('GET', '/test');
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);

        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); 
        $authenticatedUser=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER'); 

        
        $this->assertEquals($username,$noAuthHeader);
        $this->assertNull($authenticatedUser);
        $this->assertEquals($this->defaultPayloadResponse, $response);

       
    }

    public function testAuthenticatedwithBasicHeader(){

        $username = 'testUser01';
        $password = 'testPw';
        $authString = base64_encode(sprintf('%s:%s', $username, $password));
        
        $this->authDriver->method('authenticate')->willReturn(true);

        $this->registry->method('getAuth')->willReturn($username);

        $request = $this->requestFactory->createServerRequest('GET', '/test')->withHeader('Authorization','BASIC ' . $authString);
  
        
        //with authorization header
        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        

        $auth=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER');
        $this->assertEquals($username,$auth);
       // $this->assertEquals($username,$auth);      
       $this->assertEquals($this->defaultPayloadResponse, $response);


    }

    public function testAuthenticatedNoBasicHeader(){
        $username = 'testUser01';
        $password = 'testPw';
        $authString = base64_encode(sprintf('%s:%s', $username, $password));
        
        $this->authDriver->method('authenticate')->willReturn(true);

        $this->registry->method('getAuth')->willReturn($username);
        $request = $this->requestFactory->createServerRequest('GET', '/test')->withoutHeader('Authorization','BASIC ' . $authString);

        $middleware = $this->getMiddleware();
        $response = $middleware->process($request, $this->handler);
        
        
        $noAuthHeader = $this->recentlyHandledRequest->getAttribute('NO_AUTH_HEADER'); 
        $authenticatedUser=$this->recentlyHandledRequest->getAttribute('HORDE_AUTHENTICATED_USER'); 

        
        $this->assertEquals($username,$noAuthHeader);
        $this->assertNull($authenticatedUser);
        $this->assertEquals($this->defaultPayloadResponse, $response);

  
    }

    

    
}
