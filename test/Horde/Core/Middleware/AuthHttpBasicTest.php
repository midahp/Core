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
    
    public function testNotAuthenticated()  //test ob user nicht authentifiziert
    {
        
        
        $this->authDriver->method('authenticate')->willReturn(false);   //prüfmethode ob authenticate-->false ausgibt
        $this->registry->method('getAuth')->willReturn('testUser01');   //sollte da eig. nix ausgegeben werden? --> getAuth sucht user in registry
        $middleware = $this->getMiddleware();                          //kompakt in $middleware
        
        $this->assertEquals('testUser01',$this->registry->getAuth()); //über methode getAuth wird geschaut ob wirklich testuser1 ausgegeben wird
        $this->assertEquals(false,$this->authDriver->authenticate('falscheruser','falschespw')); //wtf prüfen der funktion in method()
        $this->assertTrue(true);

    }

    public function testAuthenticated()
    {
        $this->authDriver->method('authenticate')->willReturn(true);        
      
        $middleware = $this->getMiddleware();

        $this->assertEquals(true,$this->authDriver->authenticate(true,true));  //wenn richtige id und pw dann authenticate will retrun true

        $this->assertTrue(true);
    }     

  

}