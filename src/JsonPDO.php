<?php

namespace JsonPDO;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Exception;
    
class JsonPDO{
    
    //Onyl used if the data is stored in just one json file
    var $fileName = null;
    
    //Only use when the data is stored in different files
    var $dataFiles = null;
    
    var $dataPath = null;
    
    var $logger = null;
    
    //The real content already parse, if several datafiles where provided it will be an array of objects
    var $dataContent = null;
    
    var $debug = false;
    
    function __construct($dataPath = null,$defaultContent='{}',$debug = false){
        
	    $this->debug = $debug;

	    if($dataPath){
	        $this->dataPath = rtrim($dataPath, '/') . '/';
	        $this->getDataStructure($this->dataPath, $defaultContent);
	    } 
    }
    
    function logRequests($fileURL){
        $this->logger = new Logger('requests');
        $this->logger->pushHandler(new StreamHandler($fileURL, Logger::INFO));
    }
    
    function getDataStructure($path, $defaultContent='{}'){
        
        $pathParts = pathinfo($path);
        if(!empty($pathParts['extension'])){
            $this->fileName = $path;
            $this->dataContent = $this->parseFile($this->fileName,$defaultContent);
        } 
        else
        {
            if($this->dataContent == null) $this->dataContent = [];
            if($this->dataFiles == null) $this->dataFiles = [];
            
        	$directories = scandir($path);
        	$urlparts = explode("/", $path);
        	$level = 0;
        	foreach ($directories as $value){
    		    $newPath = $path.$value;
        		if($this->debug) echo "Recorriendo: $newPath \n";
        		if($value!='.' and $value!='..' and is_dir($path)) 
        		{
        			$laspath = basename($path);
        			if(is_dir($newPath)) 
        			{
        			    if($this->debug) echo "Entro en: $newPath \n";
        			    $this->getDataStructure($newPath.'/', $defaultContent);
        			}
        			else{
        			    $newPathParts = pathinfo($newPath);
        			    if($newPathParts['extension'] == 'json')
        			    {
            			    if($this->debug) echo "Incluyendo $newPath ...\n";
            			    array_push($this->dataFiles, $newPath);
            			    $this->dataContent[$newPath] = $this->parseFile($newPath, $defaultContent);
        			    }
        			} 
        		}
        	}
        	return $this->dataFiles;
        }
    }
    
    function parseFile($fileName, $defaultContent){
        
        if(!file_exists($fileName)){
            $fh = fopen($fileName, 'a'); 
            if(is_string($defaultContent)) fwrite($fh,$defaultContent); 
            else fwrite($fh, json_encode($defaultContent)); 
            fclose($fh); 
            chmod($fileName, 0777); 
            $dataContent = (array) json_decode($defaultContent);
            if($dataContent === null or $dataContent ===false) $this->throwError('Unable to get file content: '+json_last_error());
            
            return $dataContent;
        }
        else
        {
            $jsonContent = file_get_contents($fileName);
            if(empty($jsonContent)) $this->throwError('Unable to get file content for "'.$fileName.'" is it empty?');
            $dataContent = (array) json_decode($jsonContent);
            if($dataContent === false or $dataContent === null ) $this->throwError('The file "'.$fileName.'" was imposible to parse, this is its content: '.$jsonContent);
            
            return $dataContent;
        }
    }
    
    function createDirectory($path,$qzes){
        
    	$directories = scandir($path);
    	$urlparts = explode("/", $path);
    	$level = 0;
    	foreach ($directories as $value) {
    		$newPath = $path.$value.'/';
    		//echo "Recorriendo: $newPath \n";
    		if($value!='.' and $value!='..' and is_dir($path)) 
    		{
    			$laspath = basename($path);
    			//echo "entro...$laspath... \n";
    			//if(isset($urlparts[5])) echo $urlparts[5]."\n";
    			if(is_dir($newPath)) $qzes = createDirectory($newPath,$qzes);
    			else if(isValidQuiz($newPath)){
    			    $auxQuiz = json_decode(file_get_contents($path.$value),true);
    			    $auxQuiz['info']['slug'] = substr($value,0,strlen($value)-5);
    			    if(!$auxQuiz['info']['badges']) throw new Exception('There is a Quiz without badges');
    			    if($auxQuiz = filterQuiz($auxQuiz))
    			    {
        			    $auxQuiz['info']['category'] = basename($path);
        			    if(!isset($auxQuiz['info']['status']) || $auxQuiz['info']['status']=='published')
        			    {
            				if($auxQuiz) array_push($qzes, $auxQuiz);
        			    }
    			    }
    			}
    		}
    		//else echo "No es directorio".$newPath."\n";
    	}
    	return $qzes;
    }
    
    function saveData($data){
        
        if($data === null) $this->throwError('Nothing sent to save');
        
        if(!is_array($data) && !is_object($data)) $this->throwError('The data sent to save must be an object or array');
        if(!$this->fileName) throw new Exception('You need to specify the JSON file name');
        $result = file_put_contents($this->fileName,json_encode($data));
        if(!$result) $this->throwError('Error saving data into '.$this->fileName);
    }
    
    function toFile($fileName){
        $path = $this->getPathByName($fileName);
        return new FileInterface($path);
    }
    
    function toNewFile($fileName){
        if(!$this->dataPath) throw new Exception('Missing data path', 400);
        $fileI =  new FileInterface($this->dataPath.$fileName.'.json', $create=true);
        return $fileI;
    }
    
    function deleteFile($fileName){
        if(!$this->dataPath) throw new Exception('Missing data path', 400);
        $file =  new FileInterface($this->dataPath.$fileName.'.json');
        $result = $file->delete();
        
        $this->dataContent = [];
        $this->dataFiles = [];
        $this->getDataStructure($this->dataPath);
        return $result;
    }
    
    function getJsonByName($fileName){
        
        if(empty($fileName)) throw new Exception("The name of the json you are requesting is empty");
        
        //rebuild data-structure in case there is new files
        $this->getDataStructure($this->dataPath);
        
        if(!is_array($this->dataContent)) throw new Exception("There is only one json file as data model");
        
        foreach ($this->dataContent as $key => $jsonObject) {
            $file = pathinfo($key);
            if($file['filename'] == $fileName) return $jsonObject;
        }
        
        throw new Exception("Ther json file ".$fileName." was not found");
    }
    
    function jsonExists($fileName){
        
        if(empty($fileName)) throw new Exception("The name of the json you are requesting is empty");
        
        if(!is_array($this->dataContent)) throw new Exception("There is only one json file as data model");
        
        foreach ($this->dataContent as $key => $jsonObject) {
            $file = pathinfo($key);
            if($file['filename'] == $fileName) return true;
        }
        
        return false;
    }
    
    function getPathByName($fileName){
        
        if(empty($fileName)) throw new Exception("The name of the json you are requesting is empty");
        
        if(!is_array($this->dataContent)) throw new Exception("There is only one json file as data model");
        
        foreach ($this->dataContent as $key => $jsonObject) {
            if(strpos($key,$fileName)) return $key;
        }
        
        throw new Exception("There json file ".$fileName." was not found");
    }
    
    function getAllContent(){
        return $this->dataContent;   
    }
    
    function throwError($msg){
	    throw new Exception($msg);
	}
}

class FileInterface{
    private $fileName = null;
    private $create = false;
    function __construct($fileName=null, $create=false){
        if(!$fileName) throw new Exception('Missing file name');
        if(!file_exists($fileName) && !$create) throw new Exception('JSON file '.$fileName.' does not exists');
        $this->fileName = $fileName;
        $this->create = $create;
    }
    function save($data){
        if($data === null) $this->throwError('Nothing sent to save');
        
        if(!is_array($data) && !is_object($data)) $this->throwError('The data sent to save must be an object or array');
        if(!$this->fileName) throw new Exception('You need to specify the JSON file name');
        $result = file_put_contents($this->fileName,json_encode($data));
        if(!$result) $this->throwError('Error saving data into '.$this->fileName);
        
        return $data;
    }
    function delete(){
        
        if(!$this->fileName) throw new Exception('You need to specify the JSON file name');
        $result = unlink($this->fileName);
        if(!$result) $this->throwError('Error deleting file'.$this->fileName);
        
        return $result;
    }
}