<?php

/*
 * This file is part of the Coral package.
 *
 * (c) Frantisek Troster <frantisek.troster@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Coral\SiteBundle\Tests\Service;

use Coral\SiteBundle\Service\SitemapService;
use Coral\SiteBundle\Content\Node;

class SitemapServiceTest extends \PHPUnit_Framework_TestCase
{
    public function testSitemapTree()
    {
        $sitemap = new SitemapService(dirname(__FILE__) . '/../Resources/fixtures/AcmeContent/content');
        $root = $sitemap->getRoot();

        $this->assertTrue($root instanceof Node);

        //root
        $this->assertTrue($root->hasChildren());
        $this->assertTrue(null === $root->next());
        $this->assertTrue(null === $root->parent());
        $this->assertTrue(null === $root->prev());
        $this->assertEquals('Homepage', $root->getName());
        $this->assertEquals('/', $root->getUri());
        $this->assertEquals('default.html.twig', $root->getProperty('template'));
        $this->assertEquals('default.html.twig', $root->getProperty('tree_template'));
        $this->assertEquals('acme, project, default', $root->getProperty('keywords'));
        $this->assertEquals('ACME: see for yourself', $root->getProperty('description'));

        //products
        $products = $root->getChildByIndex(0);
        $this->assertTrue(null === $root->prev());
        $this->assertTrue($root === $products->parent());
        $this->assertFalse($products->hasChildren());
        $this->assertEquals('Products', $products->getName());
        $this->assertEquals('/products', $products->getUri());
        $this->assertFalse($products->hasProperty('keywords'));
        $this->assertFalse($products->hasProperty('description'));
        $this->assertEquals('default.html.twig', $products->getProperty('template'));
        $this->assertEquals('default.html.twig', $products->getProperty('tree_template'));

        //about-us
        $aboutUs = $root->getChildByIndex(1);
        $this->assertTrue($root === $aboutUs->parent());
        $this->assertTrue($products === $aboutUs->prev());
        $this->assertTrue($aboutUs === $products->next());
        $this->assertFalse($aboutUs->hasChildren());
        $this->assertEquals('About Us', $aboutUs->getName());
        $this->assertEquals('/about-us', $aboutUs->getUri());
        $this->assertFalse($aboutUs->hasProperty('keywords'));
        $this->assertFalse($aboutUs->hasProperty('description'));
        $this->assertEquals('default.html.twig', $aboutUs->getProperty('template'));
        $this->assertEquals('default.html.twig', $aboutUs->getProperty('tree_template'));

        //contact-us
        $contactUs = $root->getChildByIndex(2);
        $this->assertTrue($root === $contactUs->parent());
        $this->assertTrue($aboutUs === $contactUs->prev());
        $this->assertTrue($contactUs === $aboutUs->next());
        $this->assertTrue($contactUs->hasChildren());
        $this->assertEquals('Contact us', $contactUs->getName());
        $this->assertEquals('/contact-us', $contactUs->getUri());
        $this->assertFalse($contactUs->hasProperty('keywords'));
        $this->assertFalse($contactUs->hasProperty('description'));
        $this->assertEquals('contact.html.twig', $contactUs->getProperty('template'));
        $this->assertEquals('default.html.twig', $contactUs->getProperty('tree_template'));

        //location
        $location = $contactUs->getChildByIndex(0);
        $this->assertTrue($contactUs === $location->parent());
        $this->assertTrue(null === $location->prev());
        $this->assertTrue(null === $location->next());
        $this->assertFalse($location->hasChildren());
        $this->assertEquals('Location', $location->getName());
        $this->assertEquals('/contact-us/location', $location->getUri());
        $this->assertFalse($location->hasProperty('keywords'));
        $this->assertEquals('Contact Us: where to find us', $location->getProperty('description'));
        $this->assertEquals('default.html.twig', $location->getProperty('template'));
        $this->assertEquals('default.html.twig', $location->getProperty('tree_template'));

        //buy-now
        $buyNow = $root->getChildByIndex(3);
        $this->assertTrue($root === $buyNow->parent());
        $this->assertTrue($contactUs === $buyNow->prev());
        $this->assertTrue($buyNow === $contactUs->next());
        $this->assertFalse($buyNow->hasChildren());
        $this->assertEquals('Buy Now', $buyNow->getName());
        $this->assertEquals('/buy-now', $buyNow->getUri());
        $this->assertFalse($buyNow->hasProperty('keywords'));
        $this->assertFalse($buyNow->hasProperty('description'));
        $this->assertEquals('default.html.twig', $buyNow->getProperty('template'));
        $this->assertEquals('default.html.twig', $buyNow->getProperty('tree_template'));
        $this->assertEquals('https://store.acme.com', $buyNow->getProperty('target'));

        $this->assertTrue(null === $root->getChildByIndex(4));
    }
}