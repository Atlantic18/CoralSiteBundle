<?php

namespace Coral\SiteBundle\Tests\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Response;

class MyTestController extends Controller
{
    /**
     * @Route(path="/placeholder-controller")
     */
    public function index()
    {
        return new Response("Successfull Controller");
    }
}
