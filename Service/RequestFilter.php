<?php

namespace Coral\SiteBundle\Service;

use Coral\CoreBundle\Controller\JsonController;
use Coral\CoreBundle\Exception\AuthenticationException;
use Coral\CoreBundle\Exception\JsonException;
use Coral\SiteBundle\Service\Page;
use Coral\SiteBundle\Utility\Finder;
use Coral\SiteBundle\Utility\PropertiesParser;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;

class RequestFilter implements EventSubscriberInterface
{
    /**
     * Root path where the content is stored
     *
     * @var string
     */
    private $contentPath;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var Redirection
     */
    private $redirection;
    /**
     * @var ParameterBag
     */
    private $context;
    /**
     * @var Page
     */
    private $page;

    public function __construct($contentPath, ParameterBag $context, Page $page, LoggerInterface $logger = null, Redirection $redirection = null)
    {
        $this->logger      = $logger;
        $this->contentPath = $contentPath;
        $this->redirection = $redirection;
        $this->context     = $context;
        $this->page        = $page;
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

    /**
     * Overload this function in your RequestFilter
     * implementation so you can add context based
     * on your project
     */
    protected function customFillContext(Request $request)
    {
        //Place here code
    }

    /**
     * Detect OS based on User-Agent header
     *
     * @return string
     */
    protected function getShortOsName(Request $request)
    {
        $os = strtolower($request->headers->get('User-Agent'));

        if(false !== strpos($os, 'linux'))
        {
           return 'linux';
        }

        if((false !== strpos($os, 'macintosh')) || (false !== strpos($os, 'mac os x')))
        {
            return 'mac';
        }

        return 'windows';
    }

    /**
     * Detect country based on IP
     *
     * @return string
     */
    protected function getCountry(Request $request)
    {
        if($request->headers->get('CF-IPCountry'))
        {
            return strtolower($request->headers->get('CF-IPCountry'));
        }
        // @codeCoverageIgnoreStart
        if(is_callable('geoip_country_code_by_name'))
        {
            if($countryCode = @geoip_country_code_by_name($request->getClientIp()))
            {
                return strtolower($countryCode);
            }
        }
        // @codeCoverageIgnoreEnd


        return 'unknown';
    }

    protected function fillContext(Request $request)
    {
        foreach($request->query->all() as $key => $value)
        {
            $this->context->set('request.query.' . $key, $value);
        }
        $this->context->set('request.os', $this->getShortOsName($request));
        $this->context->set('request.country', $this->getCountry($request));

        $this->customFillContext($request);
    }

    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        if($event->isMainRequest())
        {
            $this->fillContext($request);

            $finder = self::getFinder($request, $this->contentPath);
            /**
             * In case the Page is a placeholder do not set controller so Symfony
             * can handle 404. Useful when mapping a Controller path to the same
             * path as content.
             */
            if(
                (false !== ($finder && $finder->getPropertiesPath())) &&
                !$this->page->getNode()->getProperty('placeholder')
            )
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
                    // @codeCoverageIgnoreStart
                    throw new \Coral\SiteBundle\Exception\ConfigurationException(
                        'Unable to change Coral controller, already set, please change services configuration priority.'
                    );
                    // @codeCoverageIgnoreEnd
                }
                $request->attributes->add(array('_controller' => 'coral.controller::pageAction'));
            }

            if(null !== ($redirection = $this->redirection->getRedirect($request->getPathInfo())))
            {
                $event->setResponse(new RedirectResponse($redirection[0], $redirection[1]));
            }
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
