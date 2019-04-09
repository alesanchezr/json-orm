<?php

use PHPUnit\Framework\TestCase;
use JsonPDO\JsonPDO;

class EmailTest extends TestCase
{
    public function testFindsJSON(){
        $pdo = new JsonPDO('./tests/data');
        $jsonFile = $pdo->getJsonByName('phones');
        $this->assertTrue(is_array($jsonFile));
    }
    
    public function testSaveFile(){
        $pdo = new JsonPDO('./tests/data');
        
        $content = [ "hello" => "hello" ];
        
        $pdo->toFile('phones')->save($content);
        
        $phones = $pdo->getJsonByName('phones');
        $this->assertTrue($content["hello"] == $phones["hello"]);
    }
    
    public function testSaveToNewFile(){
        $pdo = new JsonPDO('./tests/data');
        
        $content = [ "ve" => "venezuela" ];
        
        $file = $pdo->toNewFile('countries');
        $file->save($content);
        $phones = $pdo->getJsonByName('countries');
        $this->assertTrue($content["ve"] == $phones["ve"]);
        
    }
    
    public function testDeleteFile(){
        
        $pdo = new JsonPDO('./tests/data');
        $phones = $pdo->getJsonByName('countries');
        $this->assertTrue("venezuela" == $phones["ve"]);
        
        $pdo->deleteFile('countries');
        
        $this->expectException(Exception::class);
        $data = $pdo->getJsonByName('countries');
    }
}