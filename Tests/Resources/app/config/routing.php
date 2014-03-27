<?php

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Config\FileLocator;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Doctrine\Common\Annotations\AnnotationReader;

$locator = new FileLocator();
$aLoader = new AnnotatedRouteControllerLoader(new AnnotationReader());

$loader = new AnnotationDirectoryLoader($locator, $aLoader);
return $loader->load(__DIR__ . '/../../../../Controller');