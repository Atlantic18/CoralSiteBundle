<?php

namespace Coral\SiteBundle\Service;

use Coral\CoreBundle\Exception\CoralConnectException;
use Coral\CoreBundle\Utility\JsonParser;

class CoralConnectServiceMockup
{
    protected $container;

    public function __construct(\Symfony\Component\DependencyInjection\Container $container)
    {
        $this->container = $container;
    }

    public function readFile($uri)
    {
        $controller = $this->container->get('request')->attributes->get('_controller');
        $controller = substr($controller, 0, strpos($controller, 'Bundle') + 6);
        $controller = str_replace('\\', '/', $controller);
        $filePath   = $this->container->get('kernel')->getRootDir() . '/../src/' . $controller . '/Tests/coral_connect' . $uri;

        if(!file_exists($filePath))
        {
            throw new CoralConnectException('Unable to find file: ' . $filePath);
        }

        $content = file_get_contents($filePath);

        if(false === $content)
        {
            throw new CoralConnectException('Unable to load file: ' . $filePath);
        }

        return new JsonParser($content, true);
    }

    /**
     * Create POST request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @param  array  $data Datat to be sent
     * @return JsonResponse Response
     */
    public function doPostRequest($uri, $data = null)
    {
        return $this->readFile($uri);
    }

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doGetRequest($uri)
    {
        return $this->readFile($uri);
    }

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri)
    {
        return $this->readFile($uri);
    }
}
