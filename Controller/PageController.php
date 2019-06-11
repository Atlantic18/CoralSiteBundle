<?php

namespace Coral\SiteBundle\Controller;

use Coral\SiteBundle\Service\Page;
use Coral\SiteBundle\Service\Renderer;
use Coral\SiteBundle\Service\Sitemap;
use Twig\Environment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PageController
{
    /**
     * @var \Twig\Environment
     */
    private $twig;
    /**
     * @var \Coral\SiteBundle\Service\Page
     */
    private $page;
    /**
     * @var \Coral\SiteBundle\Service\Renderer
     */
    private $renderer;
    /**
     * @var \Coral\SiteBundle\Service\Sitemap
     */
    private $sitemap;
    /**
     * @var \Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface
     */
    private $authChecker;

    public function __construct(Environment $twig, Page $page, Sitemap $sitemap, Renderer $renderer, AuthorizationCheckerInterface $authChecker)
    {
        $this->twig  = $twig;
        $this->page        = $page;
        $this->sitemap     = $sitemap;
        $this->renderer    = $renderer;
        $this->authChecker = $authChecker;
    }

    public function pageAction()
    {
        //Permission property, validate authentication
        if(
            $this->page->getNode()->hasProperty('permission')
            &&
            (false === $this->authChecker->isGranted($this->page->getNode()->getProperty('permission')))
        )
        {
            throw new AccessDeniedException('Unable to access this page!');
        }
        //Redirection property
        if($this->page->getNode()->hasProperty('redirect'))
        {
            return new RedirectResponse($this->page->getNode()->getProperty('redirect'), 301);
        }

        return new Response($this->twig->render(
            $this->page->getNode()->getProperty('template', '@CoralSite/Default/page.html.twig'),
            array(
                'page'     => $this->page,
                'renderer' => $this->renderer
            )
        ));
    }
}
