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

use Coral\SiteBundle\Service\Page;
use Coral\SiteBundle\Content\Node;
use Coral\SiteBundle\Content\Area;
use Coral\SiteBundle\Content\Content;
use Coral\CoreBundle\Test\WebTestCase;

class PageTest extends WebTestCase
{
    private function createRequestStack($uri)
    {
        $stack   = new \Symfony\Component\HttpFoundation\RequestStack;
        $request = \Symfony\Component\HttpFoundation\Request::create($uri);
        $stack->push($request);
        $this->getContainer()->set('request_stack', $stack);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\SitemapException
     */
    public function testInvalidUrl()
    {
        $this->createRequestStack('/unknown');
        $page = $this->getContainer()->get('coral.page');
        $page->getNode();
    }

    public function testContactUsPage()
    {
        $this->createRequestStack('/contact-us');
        $page = $this->getContainer()->get('coral.page');

        //only partial node is read
        $this->assertTrue(null !== $page->getNode(), 'Node is fetched properly');
        $this->assertTrue(null === $page->getNode()->parent());
        $this->assertTrue(null === $page->getNode()->prev());
        $this->assertTrue(null === $page->getNode()->next());
        $this->assertFalse($page->getNode()->hasChildren());
        $this->assertEquals('Contact us', $page->getNode()->getName());
        $this->assertEquals('/contact-us', $page->getNode()->getUri());
        $this->assertFalse($page->getNode()->hasProperty('keywords'));
        $this->assertFalse($page->getNode()->hasProperty('description'));
        $this->assertEquals('::contact.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('::default.html.twig', $page->getNode()->getProperty('tree_template'));

        $this->assertFalse($page->hasArea('main'), 'Contact-us doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Contact-us has inherited footer area');
        $this->assertTrue($page->getArea('footer') instanceof Area, 'Contact-us area is instanceof Area');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertTrue($page->getArea('footer')->isInherited(), 'Contact-us has inherited footer area');
        $this->assertFalse($page->hasArea('foo'), 'Contact-us doesn\'t have foo area');
    }

    public function testHomepagePage()
    {
        $this->createRequestStack('/');
        $page = $this->getContainer()->get('coral.page');

        $this->assertEquals('Homepage', $page->getNode()->getName());
        $this->assertEquals('/', $page->getNode()->getUri());
        $this->assertEquals('::default.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('::default.html.twig', $page->getNode()->getProperty('tree_template'));
        $this->assertEquals('acme, project, default', $page->getNode()->getProperty('keywords'));
        $this->assertEquals('ACME: see for yourself', $page->getNode()->getProperty('description'));

        $this->assertTrue($page->hasArea('main'), 'Homepage has main area');
        $this->assertFalse($page->getArea('main')->isEmpty(), 'Homepage main area is not empty');
        $this->assertFalse($page->getArea('main')->isInherited(), 'Homepage main is not inherited area');
        $this->assertTrue($page->getArea('main')->getContentByIndex(0) instanceof Content, 'Content is instanceof Content');
        $this->assertEquals('markdown', $page->getArea('main')->getContentByIndex(0)->getType(), 'Markdown content type properly read');
        $this->assertContains('Nulla tincidunt quam dui.', $page->getArea('main')->getContentByIndex(0)->getContent(), 'Content is filled');
        $this->assertEquals('html', $page->getArea('main')->getContentByIndex(1)->getType(), 'HTML content type properly read');
        $this->assertContains('Fusce gravida mauris quam', $page->getArea('main')->getContentByIndex(1)->getContent(), 'Content is filled');
        $this->assertTrue($page->getArea('main')->getContentByIndex(2) === null);
        $this->assertTrue($page->hasArea('footer'), 'Homepage has footer area');
        $this->assertFalse($page->getArea('footer')->isInherited(), 'Homepage doesn\'t have inherited footer area');
        $this->assertFalse($page->hasArea('foo'), 'Homepage doesn\'t have foo area');
    }

    public function testLocationPage()
    {
        $this->createRequestStack('/contact-us/location');
        $page = $this->getContainer()->get('coral.page');

        $this->assertTrue(null === $page->getNode()->parent());
        $this->assertTrue(null === $page->getNode()->prev());
        $this->assertTrue(null === $page->getNode()->next());
        $this->assertFalse($page->getNode()->hasChildren());
        $this->assertEquals('Location', $page->getNode()->getName());
        $this->assertEquals('/contact-us/location', $page->getNode()->getUri());
        $this->assertFalse($page->getNode()->hasProperty('keywords'));
        $this->assertEquals('Contact Us: where to find us', $page->getNode()->getProperty('description'));
        $this->assertEquals('::default.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('::default.html.twig', $page->getNode()->getProperty('tree_template'));

        $this->assertFalse($page->hasArea('main'), 'Location doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Location has footer area');
        $this->assertFalse($page->getArea('footer')->isInherited(), 'Location footer area is not inherited');
        $this->assertFalse($page->getArea('footer')->isEmpty(), 'Location footer area is not empty');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertEquals('html', $page->getArea('footer')->getContentByIndex(0)->getType(), 'HTML content type properly read');
        $this->assertContains('Different', $page->getArea('footer')->getContentByIndex(0)->getContent(), 'Content is filled');
        $this->assertTrue($page->hasArea('empty'), 'Location has empty area');
        $this->assertFalse($page->getArea('empty')->isEmpty(), 'Location empty area is really empty');
        $this->assertContains('Lorem Ipsum', $page->getArea('empty')->getContentByIndex(0)->getContent(), 'Content is filled in empty area without sortorder');
        $this->assertFalse($page->hasArea('foo'), 'Location doesn\'t have foo area');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PageException
     */
    public function testInvalid()
    {
        $this->createRequestStack('/invalid');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('main');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\PageException
     */
    public function testInvalidArea()
    {
        $this->createRequestStack('/invalid');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('invalid_area');
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\SitemapException
     */
    public function testInvalidParentValidChild()
    {
        $this->createRequestStack('/invalid_parent/valid_child');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('invalid_area');

        $this->assertEquals('Products', $page->getNode()->getName());
    }
}