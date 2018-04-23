<?php

$loader = new \Phalcon\Loader();

/**
 * We're a registering a set of directories taken from the configuration file
 */
$loader->registerDirs(
    [
        $config->application->controllersDir,
        $config->application->modelsDir,
        // $config->application->library, // ลงทะเบียน loader ให้สามารถเรียกใช้งาน class ภายใน file นั้นๆได้
    ]
)->register();
