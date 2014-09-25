<?php

namespace Coral\SiteBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class DefaultControllerTest extends WebTestCase
{
    public function testMenu()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/menu/2');

        $this->assertEquals('<li>', substr($client->getResponse()->getContent(), 0, 4));
        $this->assertEquals(3, $crawler->filter('body > li')->count());
        $this->assertEquals(0, $crawler->filter('li.active')->count());
        $this->assertEquals(0, $crawler->filter('ul')->count());
        $this->assertEquals('Products', $crawler->filter('body > li:nth-child(1) > a')->text());
        $this->assertEquals('/products', $crawler->filter('body > li:nth-child(1) > a')->attr('href'));
        $this->assertEquals('About Us', $crawler->filter('body > li:nth-child(2) > strong')->text());
        $this->assertEquals('https://store.acme.com', $crawler->filter('body > li:nth-child(3) > a')->attr('href'));
    }

    public function testMenuAuthenticated()
    {
        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/menu/2',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );

        $this->assertEquals('<li>', substr($client->getResponse()->getContent(), 0, 4));
        $this->assertEquals(4, $crawler->filter('body > li')->count());
        $this->assertEquals(0, $crawler->filter('li.active')->count());
        $this->assertEquals('Products', $crawler->filter('body > li:nth-child(1) > a')->text());
        $this->assertEquals('Location', $crawler->filter('body > li:nth-child(3) > ul > li > a')->text());
        $this->assertEquals('/contact-us/location', $crawler->filter('body > li:nth-child(3) > ul > li > a')->attr('href'));
    }

    public function testMenuLevel()
    {
        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/menu/1',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );

        $this->assertEquals('<li>', substr($client->getResponse()->getContent(), 0, 4));
        $this->assertEquals(4, $crawler->filter('body > li')->count());
        $this->assertEquals(0, $crawler->filter('ul')->count());
        $this->assertEquals('Products', $crawler->filter('body > li:nth-child(1) > a')->text());
        $this->assertEquals('/contact-us', $crawler->filter('body > li:nth-child(3) > a')->attr('href'));
    }

    public function testPage()
    {
        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/contact-us/location',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals(4, $crawler->filter('.navigation > ul > li')->count());
        $this->assertEquals(1, $crawler->filter('.navigation ul ul')->count());
        $this->assertEquals('Products', $crawler->filter('.navigation > ul > li:nth-child(1) > a')->text());
        $this->assertEquals('/contact-us', $crawler->filter('.navigation > ul > li:nth-child(3) > a')->attr('href'));

        $this->assertEquals('Location', $crawler->filter('.navigation > ul li.active > a')->text());
        $this->assertEquals('/contact-us/location', $crawler->filter('.navigation > ul li.active > a')->attr('href'));

        $this->assertEquals('Location', $crawler->filter('.main h1')->text());

        $this->assertEquals('Different footer pure html.', $crawler->filter('.footer > p')->text());
    }

    public function testPageWhichHasLinkProperty()
    {
        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/buy-now',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );
        $this->assertTrue($client->getResponse()->isRedirect('https://store.acme.com'));
    }

    public function testPageWhichIsPlaceholder()
    {
        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/about-us',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );
        $this->assertTrue($client->getResponse()->isNotFound());
    }

    public function testPageTemplate()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/products');
        $this->assertTrue($client->getResponse()->isSuccessful());

        $this->assertEquals('Products', $crawler->filter('.main h1')->text());

        $this->assertEquals('Page Test', $crawler->filter('.page_test > h3')->text());
    }

    public function testPageAuthenticated()
    {
        $client  = static::createClient();
        $crawler = $client->request('GET', '/contact-us');
        $this->assertEquals(
            \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED,
            $client->getResponse()->getStatusCode()
        );

        $client  = static::createClient();
        $crawler = $client->request(
            'GET',
            '/contact-us',
            array(),
            array(),
            array('PHP_AUTH_USER' => 'user', 'PHP_AUTH_PW' => 'userpass')
        );
        $this->assertTrue($client->getResponse()->isSuccessful());
    }
}