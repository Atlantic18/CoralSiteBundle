<?php

namespace Coral\SiteBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    /**
     * @Route("/menu/{max_level}")
     */
    public function menuAction($max_level, $uri = null)
    {
        return $this->render(
            'CoralSiteBundle:Default:menu.html.twig',
            array(
                'parent'      => $this->get('coral.sitemap')->getRoot(),
                'max_level'   => $max_level - 1,
                'current_url' => (null === $uri) ? $this->getRequest()->getPathInfo() : $uri
            )
        );
    }
}
