<?php

namespace Coral\SiteBundle\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;

use Coral\CoreBundle\Controller\JsonController;
use Coral\CoreBundle\Exception\JsonException;
use Coral\CoreBundle\Exception\AuthenticationException;

class RequestFilter implements EventSubscriberInterface
{
    /**
     * Root path where the content is stored
     *
     * @var string
     */
    private $contentPath;
    /**
     * Logger
     *
     * @var LoggerInterface
     */
    private $logger;

    public function __construct($contentPath, LoggerInterface $logger = null)
    {
        $this->logger      = $logger;
        $this->contentPath = $contentPath;
    }

    /**
     * Get property file name from request uri and base content path
     *
     * @param  Request $request     Request
     * @param  string  $contentPath Root content path
     * @return boolean              True if property file exists
     */
    public static function getPropertyFileName(Request $request, $contentPath)
    {
        $requestUri = $request->getRequestUri();

        if($requestUri == '/')
        {
            $propertiesFile = $contentPath . DIRECTORY_SEPARATOR . '.properties';
        }
        else
        {
            $propertiesFile = $contentPath . $requestUri . DIRECTORY_SEPARATOR . '.properties';
        }

        if((false === (strpos($requestUri, '.') && strpos($requestUri, '?'))) && file_exists($propertiesFile))
        {
            return $propertiesFile;
        }

        return false;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        if(false !== self::getPropertyFileName($request, $this->contentPath))
        {
            if (null !== $this->logger)
            {
                $this->logger->info(sprintf('Coral matched route [%s].', $request->getRequestUri()));
            }
            $request->attributes->add(array('_controller' => 'CoralSiteBundle:Default:page'));
        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 50))
        );
    }
}
