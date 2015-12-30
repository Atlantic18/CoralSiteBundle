<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\CoreBundle\Service\Connector;
use Coral\CoreBundle\Utility\JsonParser;
use Twig_Environment as Environment;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Coral\SiteBundle\Content\Content;

class Connect extends AbstractContentFilter implements FilterInterface
{
    private $connector;
    private $twig;
    private $context;

    function __construct(Connector $connector, Environment $twig, ParameterBag $context, $contentPath)
    {
        $this->connector = $connector;
        $this->twig      = $twig;
        $this->context   = $context;

        $this->setContentPath($contentPath);
    }

    /**
     * Convert input string to output
     *
     * @param  Content $content
     * @return string
     */
    public function render(Content $content)
    {
        $params   = new JsonParser($this->getFileContent($content));
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

        $twigTemplate = $params->getMandatoryParam('template');

        if(strpos($twigTemplate, ':') === false && $twigTemplate[0] != '@')
        {
            // Build a template path in case it is relative to current file
            $twigTemplate = '@coral' . dirname($content->getPath()) . DIRECTORY_SEPARATOR . $twigTemplate;
        }

        return $this->twig->render($twigTemplate, $variables);
    }
}