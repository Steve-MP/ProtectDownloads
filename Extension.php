<?php

namespace Bolt\Extension\SteveEMBO\ProtectDownloads;

use Bolt\Application;
use Bolt\BaseExtension;

class Extension extends BaseExtension
{
    public function initialize()
    {
        // $this->addCss('assets/extension.css');
        // $this->addJavascript('assets/start.js', true);
    }

    public function getName()
    {
        return "ProtectDownloads";
    }
}
