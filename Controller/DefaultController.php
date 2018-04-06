<?php

namespace Coral\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/menu/{max_level}")
     */
    public function menuAction($max_level, $uri = null, Request $request)
    {
        return $this->render(
            '@CoralSite/Default/menu.html.twig',
            array(
                'parent'      => $this->get('coral.sitemap')->getRoot(),
                'max_level'   => $max_level - 1,
                'current_url' => (null === $uri) ? $request->getPathInfo() : $uri
            )
        );
    }
}
