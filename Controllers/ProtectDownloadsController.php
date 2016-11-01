<?php

namespace Bolt\Extension\SteveEMBO\ProtectDownloads\Controllers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Silex\Application;
use Silex\ControllerProviderInterface;
use \Exception;

class ProtectDownloadsController implements ControllerProviderInterface{

	protected $app;
	protected $record;
	protected $config;

	public function __construct($config){
		//get a local copy of the configuration file (this is copied from the extension
		//director to app/config/extensions when the extension is installed)
		$this->config = $config;
	
	}

/**
 * This function wires up the controller for get requests to the protect downloads URL
 * @param  Application $app a type-hinted instance of the Bolt application object
 * @return Void           Nothing
 */
	public function connect(Application $app)
	{
		//get a local copy of the app variable that contains all
		//the program's settings and functions
		$this->app = $app;

		//create a controller for this route
		$protectDownloads = $this->app['controllers_factory'];
		$protectDownloads->get('', function($type, $id){

			//get Filename and Password from record
			$details = $this->getFilepathAndPasswordFromRecord($type, $id);


			//if passwordfield is empty, just download the document
			if(empty(trim($details['password']))){ 

				return $this->returnFile($details['filepath']);

			}else{
				//create password form
				$form = $this->generateForm();
				//return password entry form to user
				return $this->app['twig']->render('downloadPassword.twig',array('form' => $form));

			}
			return;

		})->assert('type','^[a-zA-Z0-9]+$')->assert('id','\d+');
		
		//set route for form POST
		$protectDownloads->post('',function(Request $req, $type, $id){

			$details = $this->getFilepathAndPasswordFromRecord($type, $id);

			//get POSTed password
			$postData = $req->request->all();
			$submittedPassword = $postData['form']['password'];

			//If passwords match, download file, otherwise redirect to login page
			$realPassword = $details['password'];

			if($realPassword==$submittedPassword){
				return $this->returnFile($details['filepath']);
			}else{
				return $this->app['twig']->render('downloadPassword.twig',array('form' => $this->generateForm()));
			}

			return;


		});

		return $protectDownloads;
	}

	/**
	 * Protected function that handles the actual download of the file
	 * @param  strin $fileName The fully qualified file path of the file
	 * @return BinaryFileResponse The data to return to the user's browser
	 */
	protected function returnFile($fileName)
	{

		//check that filepath exists
		if(!$fileName) throw new Exception("Attempted to run returnFile without giving a file path!");

		//construct full filepath
		$fullDownloadPath = $this->app['resources']->getPath("filespath") . DIRECTORY_SEPARATOR . $fileName;
				
		//check that file exists
		if(!file_exists($fullDownloadPath)) throw new Exception("You tried to download a file that doesn't exist");

		//Download the file
		$response = new BinaryFileResponse($fullDownloadPath);
		$response->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE);

		return $response;
	}


	protected function generateForm()
	{
		$form = $this->app['form.factory']->createBuilder('form');
		$form->add('password', 'password');
		return $form->getForm()->createView();
	}

	protected function getFilepathAndPasswordFromRecord($type, $id){

			//check if there's a record in the database with this ID
			$this->record = $this->app['storage']->getContent($type, array('id' => $id, 'returnsingle' => true));
			
			//if there is no record with the chosen type or ID throw a 404 (page not found)
			if(!$this->record) throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('There is no content at this address');

			//if there is a record, get the download field name and the password field name
			//these are configured in the config.yaml file for this extension
			$fieldNames = $this->config[$this->record->contenttype["singular_name"]];

			if(!$fieldNames) throw new Exception("Configuration file for ProtectDownloads does not contain settings for this event type.");

			//these are the fields to check for the information in
			//They were configured in the config.yaml file
			$downloadFileNameField = $fieldNames["filepathfield"];
			$downloadPasswordField = $fieldNames["passwordfield"];

			//these are the values themselves
			$downloadFilepath = $this->record->$downloadFileNameField();
			$downloadPassword = $this->record->$downloadPasswordField();

			return array('filepath'=>$downloadFilepath, 'password' => $downloadPassword);


	}

}