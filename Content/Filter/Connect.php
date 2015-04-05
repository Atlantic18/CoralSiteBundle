<?php

namespace Coral\SiteBundle\Content\Filter;

use Coral\CoreBundle\Service\Connector;
use Coral\CoreBundle\Utility\JsonParser;
use Twig_Environment as Environment;

class Connect implements FilterInterface
{
    private $connector;
    private $twig;

    function __construct(Connector $connector, Environment $twig)
    {
        $this->connector = $connector;
        $this->twig      = $twig;
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
        $response = $this->connector
            ->to($params->getMandatoryParam('service'))
            ->doRequest(
                $params->getMandatoryParam('method'),
                $params->getMandatoryParam('uri'),
                $params->getOptionalParam('payload', null)
            );

        return $this->twig->render(
            $params->getMandatoryParam('template'),
            array('response' => $response)
        );
    }
}