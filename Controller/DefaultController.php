<?php

namespace Coral\SiteBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as Controller;
use Symfony\Component\HttpFoundation\Request;
use Coral\SiteBundle\Service\Sitemap;

class DefaultController extends Controller
{
    /**
     * @Route(path="/menu/{max_level}")
     */
    public function menuAction(Request $request, Sitemap $sitemap, $max_level, $uri = null)
    {
        return $this->render(
            '@CoralSite/Default/menu.html.twig',
            array(
                'parent'      => $sitemap->getRoot(),
                'max_level'   => $max_level - 1,
                'current_url' => (null === $uri) ? $request->getPathInfo() : $uri
            )
        );
    }
}
