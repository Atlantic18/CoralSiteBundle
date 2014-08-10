<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/menu/{max_level}/{active_node}")
     */
    public function menuAction($max_level, $active_node = null)
    {
        $sitemap = $this->get('coral.sitemap');

        return $this->render(
            'CoralSiteBundle:Default:menu.html.twig',
            array(
                'parent'      => $sitemap->getRoot(),
                'max_level'   => $max_level - 1,
                'current_url' => $this->getRequest()->getRequestUri()
            )
        );
    }
}
