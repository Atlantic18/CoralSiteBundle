<?php

namespace Coral\SiteBundle\Service;

use Coral\SiteBundle\Exception\CoralConnectException;
use Coral\CoreBundle\Utility\JsonParser;

class CoralConnectService
{
    /**
     * Coral account
     * @var string
     */
    private $_account;

    /**
     * Coral private key
     * @var string
     */
    private $_key;

    /**
     * Coral host where to connect
     * @var string
     */
    private $_host;

    /**
     * Switch ssl verification off for curl
     *
     * @var boolean
     */
    private $_disableSslVerification = true;

    public function __construct($host, $account, $key)
    {
        $this->_host = $host;
        $this->_account = $account;
        $this->_key = $key;
    }

    private function sign($dtime, $uri, $data = null)
    {
        $source =
            $this->_key . '|' .
            $dtime . '|' .
            $this->_host . $uri .
            (null === $data ? '' : '|' . $data);
        return hash('sha256', $source);
    }

    private function doCurlRequest($type, $uri, $data = null)
    {
        $dtime   = time();

        $ch = curl_init($this->_host . $uri);

        if(null !== $data)
        {
            $payload   = json_encode($data);
            $signature = $this->sign($dtime, $uri, $payload);

            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }
        else
        {
            $signature = $this->sign($dtime, $uri);
        }

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        if($this->_disableSslVerification)
        {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type:application/json',
            'X-Requested-With: XMLHttpRequest',
            'X-CORAL-ACCOUNT: ' . $this->_account,
            'X-CORAL-SIGN: ' . $signature,
            'X-CORAL-DTIME: ' .$dtime
        ));

        $rawResponse = curl_exec($ch);
        $httpCode    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if(false === $rawResponse)
        {
            throw new CoralConnectException('Unable to connect to CORAL backend. Response code: ' . $httpCode);
        }

        $parser = new JsonParser($rawResponse, true);
        if($httpCode < 200 || $httpCode > 299)
        {
            throw new CoralConnectException(
                "Error connecting to CORAL backend.
                Response code: $httpCode.
                Error: " . $parser->getMandatoryParam('message'));
        }

        return $parser;
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
        return $this->doCurlRequest('POST', $uri, $data);
    }

    /**
     * Create GET request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doGetRequest($uri)
    {
        return $this->doCurlRequest('GET', $uri);
    }

    /**
     * Create DELETE request to CORAL backend
     *
     * @param  string $uri  Service URI
     * @return JsonResponse Response
     */
    public function doDeleteRequest($uri)
    {
        return $this->doCurlRequest('DELETE', $uri);
    }
}
