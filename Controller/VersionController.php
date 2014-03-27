<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class VersionController extends Controller
{
    /**
     * @Route("/v1/version")
     * @Method("GET")
     */
    public function versionAction()
    {
        $version = 'N/A';

        $filename = $this->container->getParameter("kernel.root_dir") . '/../version';
        if(file_exists($filename))
        {
            $version = file_get_contents($filename);
        }

        return new Response('Version: ' . $version);
    }
}
