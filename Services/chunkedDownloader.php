<?php

namespace Bolt\Extension\SteveEMBO\ProtectDownloads\Services;

use \Exception;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ChunkedDownloader uses the Symfony StreamedResponse to return the file in chunks
 * instead of forcing PHP to read the whole file into memory before returning.
 */
class ChunkedDownloader {

	protected $filepath;
	protected $app;

	public function __construct ($app, $filepath)
	{
		//store a version of the App object
		$this->app = $app;

		//store the full filepath
		$this->filepath = $this->app['resources']->getPath("filespath") . DIRECTORY_SEPARATOR . $filepath;

		// echo "fp:" . $this->filepath;
	}

	public function chunkIt()
	{	
		//get the filepath
		$filepath = $this->filepath; 

		//check if there is a real file at this location
		if(!file_exists($filepath)) throw new Exception("You have attempted to download a file that doesn't exist at the given location");

		//define output stream
		$myStream =  new StreamedResponse();

		//set the callback function that chunks the data
		$myStream->setCallback(function () use ($filepath) {

		        //readfile($filepath);
		        $chunkSize = 1024 * 1024;
		        $handle = fopen($filepath, 'rb');
		        while(!feof($handle)){

		        	$buff = fread($handle, $chunkSize);
		        	echo $buff;
		        	ob_flush();
		        	flush();

		        }//end of while

		        //close filestream
		        fclose($handle);
		        exit;
		    
		    });
		//set status code
		$myStream->setStatusCode(200);

		//add content type
		$myStream->headers->add(array('Content-Type' => 'application/pdf'));

		//return the streamed response object
		$myStream ->send();


	}



}