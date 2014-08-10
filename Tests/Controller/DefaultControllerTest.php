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
        $this->assertEquals('/buy-now', $crawler->filter('body > li:nth-child(3) > a')->attr('href'));
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

        $this->assertEquals('<li>', substr($client->getResponse()->getContent(), 0, 4));
        $this->assertEquals(4, $crawler->filter('body > li')->count());
        $this->assertEquals(0, $crawler->filter('ul')->count());
        $this->assertEquals('Products', $crawler->filter('body > li:nth-child(1) > a')->text());
        $this->assertEquals('/contact-us', $crawler->filter('body > li:nth-child(3) > a')->attr('href'));
    }
}