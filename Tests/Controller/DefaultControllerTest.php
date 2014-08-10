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
        $this->assertEquals(4, $crawler->filter('body > li')->count());
        $this->assertEquals(0, $crawler->filter('li.active')->count());
        $this->assertEquals('Products', $crawler->filter('body > li:nth-child(1) > a')->text());
        $this->assertEquals('Location', $crawler->filter('body > li:nth-child(3) > ul > li > a')->text());
        $this->assertEquals('/contact-us/location', $crawler->filter('body > li:nth-child(3) > ul > li > a')->attr('href'));
    }
}