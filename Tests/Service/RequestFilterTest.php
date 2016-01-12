<?php

namespace Coral\SiteBundle\Tests\Service;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RequestFilterTest extends WebTestCase
{
    public function testHomepage()
    {
        $client  = static::createClient();
        $client->request('GET', '/');

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testPlaceholderController()
    {
        $client  = static::createClient();
        $client->request('GET', '/placeholder-controller');

        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals("Successfull Controller", $client->getResponse()->getContent());
    }

    public function testUnknown()
    {
        $client  = static::createClient();
        $client->request('GET', '/unknown');

        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testDot()
    {
        $client  = static::createClient();
        $client->request('GET', '/products.');

        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testContactUs()
    {
        $client  = static::createClient();
        $client->request(
            'GET',
            '/contact-us',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );

        $this->assertTrue($client->getResponse()->isSuccessful());
    }

    public function testRedirectRoot()
    {
        $client  = static::createClient();
        $client->request('GET', '/to-redirect');

        $this->assertEquals(
            \Symfony\Component\HttpFoundation\Response::HTTP_MOVED_PERMANENTLY,
            $client->getResponse()->getStatusCode()
        );
        $this->assertTrue($client->getResponse()->isRedirect('/en'));
    }

    public function testRedirectWildcard()
    {
        $client  = static::createClient();
        $client->request('GET', '/old-section/some/document');

        $this->assertEquals(
            \Symfony\Component\HttpFoundation\Response::HTTP_FOUND,
            $client->getResponse()->getStatusCode()
        );
        $this->assertTrue($client->getResponse()->isRedirect('/new-section/some/document'));
    }
}