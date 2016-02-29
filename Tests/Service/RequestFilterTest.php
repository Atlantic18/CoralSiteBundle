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

        //Test how to override placeholder content with Controller
        $this->assertTrue($client->getResponse()->isSuccessful());
        $this->assertEquals("Successfull Controller", $client->getResponse()->getContent());
    }

    public function testPlaceholder()
    {
        $client  = static::createClient();
        $client->request('GET', '/placeholder/child');

        $this->assertTrue($client->getResponse()->isNotFound());
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

    public function testContextParams()
    {
        $crawler = static::createClient()->request(
            'GET',
            '/dynamic?param1=bar',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT'   => 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X 10_5_7; en-us) AppleWebKit/530.19.2 (KHTML, like Gecko) Version/4.0.2 Safari/530.19',
                'HTTP_CF-IPCountry' => 'nl'
            )
        );
        $this->assertEquals('request.query.param1 = bar', $crawler->filter('h4.query')->text());
        $this->assertEquals('request.os = mac', $crawler->filter('h4.os')->text());
        $this->assertEquals('request.country = nl', $crawler->filter('h4.country')->text());

        $crawler = static::createClient()->request(
            'GET',
            '/dynamic?param1=bar',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/34.0.1847.137 Safari/4E423F'
            )
        );
        $this->assertEquals('request.os = linux', $crawler->filter('h4.os')->text());

        $crawler = static::createClient()->request(
            'GET',
            '/dynamic?param1=bar',
            array(),
            array(),
            array(
                'HTTP_USER_AGENT' => 'Mozilla/5.0 (Windows NT 5.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/35.0.3319.102 Safari/537.36'
            )
        );
        $this->assertEquals('request.os = windows', $crawler->filter('h4.os')->text());
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