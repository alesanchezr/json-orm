<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use JsonPDO\JsonPDO;

final class EmailTest extends TestCase
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

}