<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\CoreBundle\Service\Connector;
use Coral\CoreBundle\Utility\JsonParser;
use Twig_Environment as Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class Connect implements FilterInterface
{
    private $connector;
    private $twig;
    private $context;

    function __construct(Connector $connector, Environment $twig, ParameterBag $context)
    {
        $this->connector = $connector;
        $this->twig      = $twig;
        $this->context   = $context;
    }

    /**
     * Convert input string to output
     *
     * @param  string $input
     * @return string
     */
    public function render($input)
    {
        $params   = new JsonParser($input);
        $uri      = $this->context->resolveString($params->getMandatoryParam('uri'));
        $payload  = $params->getOptionalParam('payload', null);

        if(null !== $payload)
        {
            $payload = $this->context->resolveValue($payload);
        }

        $response = $this->connector
            ->to($params->getMandatoryParam('service'))
            ->doRequest($params->getMandatoryParam('method'), $uri, $payload);

        $variables = $params->getOptionalParam('variables', array());
        $variables['response'] = $response;

        return $this->twig->render(
            $params->getMandatoryParam('template'),
            $variables
        );
    }
}