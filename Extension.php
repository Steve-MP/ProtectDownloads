<?php

namespace Bolt\Extension\SteveEMBO\ProtectDownloads;

use Bolt\Application;
use Bolt\BaseExtension;
use Bolt\Extension\SteveEMBO\ProtectDownloads\Controllers\ProtectDownloadsController;

class Extension extends BaseExtension
{
    public function initialize()
    {
        //when user navigates to /getfile/record_id, use the ProtectDownloadsController to fetch the downloadable file for this record
    	$this->app->mount('/getfile/{type}/{id}', new ProtectDownloadsController($this->config));

    	//define new Twig function to create password protected download link
    	//e.g. {{ createDownloadLink(event.contenttype.singular_name, event.id) }}
    	$this->addTwigFunction('createDownloadLink', 'twigCreateDownloadLink');

    }

    public function twigCreateDownloadLink($type, $recordId){

    	//get the base url for this site
    	$baseUrl = $this->app["resources"]->getUrl("rooturl");
    	//generate the download link
    	$link = $baseUrl . 'getfile/'. $type . '/' . $recordId;
    	
    	return $link;

    }


    public function getName()
    {
        return "ProtectDownloads";
    }
}
