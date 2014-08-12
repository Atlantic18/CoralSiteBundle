<?php

namespace Coral\SiteBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;

class PathExtension extends \Twig_Extension
{
    /**
     * Request stack
     *
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('coral_path', array($this, 'path')),
        );
    }

    public function path($path)
    {
        $request = $this->requestStack->getCurrentRequest();

        return $request->getScriptName() . $path;
    }

    public function getName()
    {
        return 'path_extension';
    }
}