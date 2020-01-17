<?php

namespace Coral\SiteBundle\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Coral\SiteBundle\Content\Node;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class PathExtension extends AbstractExtension
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

    /**
     * @codeCoverageIgnore
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('coral_path', array($this, 'path')),
        );
    }

    public function path(Node $node)
    {
        if($node->hasProperty('redirect'))
        {
            return $node->getProperty('redirect');
        }
        $request = $this->requestStack->getCurrentRequest();
        $path = $node->getUri();
        $scriptName = $request->getScriptName();

        if((strpos($scriptName, '_dev') !== false) || ((strpos($scriptName, '_test') !== false)))
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