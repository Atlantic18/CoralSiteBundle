<?php

namespace Coral\SiteBundle\Service;

use Doctrine\Common\Cache\Cache;
use Coral\SiteBundle\Exception\ConfigurationException;

class Redirection
{
    /**
     * Redirection array
     *
     * @var array
     */
    private $redirections;

    public function __construct($configPath)
    {
        $filePath = $configPath . '/redirections.json';
        if((false === file_exists($filePath)) || (false === is_readable($filePath)))
        {
            throw new ConfigurationException("File not found or not readable: [$configPath/redirections.json'].");
        }
        if(false !== ($string = file_get_contents($filePath)))
        {
            $redirections = json_decode($string, true);

            if(null === $redirections)
            {
                throw new ConfigurationException("Unable to parse: [$configPath/redirections.json'].");
            }

            if(!array_key_exists('redirections', $redirections))
            {
                throw new ConfigurationException("Invalid redirections format missing key redirections in: [$configPath/redirections.json'].");
            }

            $this->redirections = $redirections['redirections'];

            $this->validateRedirections();
        }
        // @codeCoverageIgnoreStart
        else
        {
            throw new ConfigurationException("Unable to read: [$configPath/redirections.json'].");
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * Validate redirections configuration
     *
     * @throws ConfigurationException
     * @return void
     */
    private function validateRedirections()
    {
        foreach ($this->redirections as $redirection)
        {
            if(!(
                array_key_exists('source', $redirection)
                &&
                array_key_exists('target', $redirection)
                &&
                array_key_exists('type', $redirection)
            ))
            {
                throw new ConfigurationException("Invalid redirection line: " . implode(',', $redirection) . ".");
            }
        }
    }

    /**
     * Returns true if there's a redirect for source
     *
     * @param  string  $source Source url
     * @return boolean         true if the redirect for source exists
     */
    public function hasRedirect($source)
    {
        return (null !== $this->getRedirect($source));
    }

    /**
     * Returns redirection for source
     *
     * @param  string  $source Source url
     * @return array           array('redirection target', 301|302)
     */
    public function getRedirect($source)
    {
        foreach ($this->redirections as $redirection)
        {
            if(false !== ($wildcardPos = strpos($redirection['source'], '*')))
            {
                if(substr($source, 0, $wildcardPos) == substr($redirection['source'], 0, $wildcardPos))
                {
                    return array(
                        substr($redirection['target'], 0, $wildcardPos) . substr($source, $wildcardPos),
                        $redirection['type']
                    );
                }
            }
            else
            {
                if($source == $redirection['source'])
                {
                    return array($redirection['target'], $redirection['type']);
                }
            }
        }
        return null;
    }
}