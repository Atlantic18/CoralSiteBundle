<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{
    /**
     * @Route("/menu/{max_level}")
     */
    public function menuAction($max_level, $uri = null)
    {
        $sitemap = $this->get('coral.sitemap');

        return $this->render(
            'CoralSiteBundle:Default:menu.html.twig',
            array(
                'parent'      => $sitemap->getRoot(),
                'max_level'   => $max_level - 1,
                'current_url' => (null === $uri) ? $this->getRequest()->getPathInfo() : $uri
            )
        );
    }

    public function pageAction()
    {
        return $this->render(
            'CoralSiteBundle:Default:page.html.twig',
            array(
                'page'     => $this->get('coral.page'),
                'renderer' => $this->get('coral.renderer')
            )
        );
    }
}
