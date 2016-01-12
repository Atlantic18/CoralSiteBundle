<?php

use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Config\FileLocator;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\Routing\Route;

$locator    = new FileLocator();
$aLoader    = new AnnotatedRouteControllerLoader(new AnnotationReader());

$loader     = new AnnotationDirectoryLoader($locator, $aLoader);
$collection = $loader->load(__DIR__ . '/../../../../Controller');
$collection->add('placeholder_controller', new Route('/placeholder-controller', array(
    '_controller' => 'my_test_controller:indexAction',
)));
return $collection;