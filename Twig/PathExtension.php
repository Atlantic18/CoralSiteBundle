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
        $scriptName = $request->getScriptName();

        if(strpos($scriptName, '_') !== false)
        {
            return $scriptName . $path;
        }

        return $path;
    }

    public function getName()
    {
        return 'path_extension';
    }
}