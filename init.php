<?php

namespace Bolt\Extension\SteveEMBO\ProtectDownloads;

if (isset($app)) {
    $app['extensions']->register(new Extension($app));
}

