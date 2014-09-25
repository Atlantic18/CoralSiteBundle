<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $page = $this->get('coral.page');

        if($page->getNode()->hasProperty('redirect'))
        {
            return $this->redirect($page->getNode()->getProperty('redirect'), 301);
        }
        if($page->getNode()->hasProperty('placeholder'))
        {
            throw new NotFoundHttpException('Page not found exception. Node is a placeholder.');
        }

        return $this->render(
            $page->getNode()->getProperty('template', 'CoralSiteBundle:Default:page.html.twig'),
            array(
                'page'     => $page,
                'renderer' => $this->get('coral.renderer')
            )
        );
    }
}
