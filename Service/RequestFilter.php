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
use Symfony\Component\HttpFoundation\RedirectResponse;

use Coral\SiteBundle\Utility\Finder;
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
    /**
     * Redirection
     *
     * @var Redirection
     */
    private $redirection;

    public function __construct($contentPath, LoggerInterface $logger = null, Redirection $redirection = null)
    {
        $this->logger      = $logger;
        $this->contentPath = $contentPath;
        $this->redirection = $redirection;
    }

    /**
     * Get property file name from request uri and base content path
     *
     * @param  Request $request     Request
     * @param  string  $contentPath Root content path
     * @return boolean              True if property file exists
     */
    public static function getFinder(Request $request, $contentPath)
    {
        $requestUri = $request->getPathInfo();
        //Reject paths with . and ?
        if(false !== (strpos($requestUri, '.') || strpos($requestUri, '?')))
        {
            return false;
        }

        if($requestUri == '/')
        {
            return new Finder($contentPath);
        }

        return new Finder($contentPath . $requestUri);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $finder = self::getFinder($request, $this->contentPath);
        if(false !== ($finder && $finder->getPropertiesPath()))
        {
            if (null !== $this->logger)
            {
                $this->logger->info(sprintf('Coral matched route [%s].', $request->getRequestUri()));
            }
            if($request->attributes->has('_controller'))
            {
                /* Services.xml
                 *
                 * <service id="coral.listener.route_resolve" class="%coral.request_filter.class%">
                 *   <argument>%coral.content.path%</argument>
                 *   <argument type="service" id="logger"/>
                 *   <argument type="service" id="coral.redirection"/>
                 *
                 *   <tag name="kernel.event_listener" event="kernel.request" method="onKernelRequest" priority="100" />
                 * </service>
                 */
                throw new \Coral\SiteBundle\Exception\ConfigurationException(
                    'Unable to change Coral controller, already set, please change services configuration priority.'
                );
            }
            $request->attributes->add(array('_controller' => 'CoralSiteBundle:Default:page'));
        }

        if(null !== ($redirection = $this->redirection->getRedirect($request->getPathInfo())))
        {
            $event->setResponse(new RedirectResponse($redirection[0], $redirection[1]));
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 50))
        );
    }
}
