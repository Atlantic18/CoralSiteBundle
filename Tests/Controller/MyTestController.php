<?php

namespace Coral\SiteBundle\Tests\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

class MyTestController extends Controller
{
    /**
     * @Route("/placeholder-controller")
     */
    public function index()
    {
        return new Response("Successfull Controller");
    }
}
