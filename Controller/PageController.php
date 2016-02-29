<?php

namespace Coral\SiteBundle\Controller;

use Coral\SiteBundle\Service\Page;
use Coral\SiteBundle\Service\Renderer;
use Coral\SiteBundle\Service\Sitemap;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Templating\EngineInterface;

class PageController
{
    /**
     * @var \Symfony\Component\Templating\EngineInterface
     */
    private $templating;
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

    public function __construct(EngineInterface $templating, Page $page, Sitemap $sitemap, Renderer $renderer, AuthorizationCheckerInterface $authChecker)
    {
        $this->templating  = $templating;
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

        return $this->templating->renderResponse(
            $this->page->getNode()->getProperty('template', 'CoralSiteBundle:Default:page.html.twig'),
            array(
                'page'     => $this->page,
                'renderer' => $this->renderer
            )
        );
    }
}
