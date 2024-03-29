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
use Coral\SiteBundle\Utility\Finder;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class PageTest extends KernelTestCase
{
    private function createRequestStack($uri)
    {
        $stack   = new \Symfony\Component\HttpFoundation\RequestStack;
        $request = \Symfony\Component\HttpFoundation\Request::create($uri);
        $stack->push($request);
        $this->getContainer()->set('request_stack', $stack);
    }

    public function testInvalidUrl()
    {
        $this->expectException('Coral\SiteBundle\Exception\SitemapException');

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
        $this->assertEquals('contact.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('default.html.twig', $page->getNode()->getProperty('tree_template'));

        $this->assertFalse($page->hasArea('main'), 'Contact-us doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Contact-us has inherited footer area');
        $this->assertTrue($page->getArea('footer') instanceof Area, 'Contact-us area is instanceof Area');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertTrue($page->getArea('footer')->isInherited(), 'Contact-us has inherited footer area');
        $this->assertFalse($page->hasArea('foo'), 'Contact-us doesn\'t have foo area');
    }

    public function testContactUsPageByFinder()
    {
        $page = $this->getContainer()->get('coral.page');
        $page->setNodeByUri('/contact-us');

        $this->assertTrue(null !== $page->getNode(), 'Node is fetched properly');
        $this->assertTrue(null === $page->getNode()->parent());
        $this->assertTrue(null === $page->getNode()->prev());
        $this->assertTrue(null === $page->getNode()->next());
        $this->assertFalse($page->getNode()->hasChildren());
        $this->assertEquals('Contact us', $page->getNode()->getName());
        $this->assertEquals('/contact-us', $page->getNode()->getUri());
    }

    public function testDuplicateSetNodeByUri()
    {
        $this->createRequestStack('/other');
        $page = $this->getContainer()->get('coral.page');
        $this->assertTrue($page->hasArea('other'), 'Contact-us does not have other area');
        $page->setNodeByUri('/contact-us');
        $this->assertFalse($page->hasArea('other'), 'Contact-us does not have other area');

    }


    public function testHomepagePage()
    {
        $this->createRequestStack('/');
        $page = $this->getContainer()->get('coral.page');

        $this->assertEquals('Homepage', $page->getNode()->getName());
        $this->assertEquals('/', $page->getNode()->getUri());
        $this->assertEquals('default.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('default.html.twig', $page->getNode()->getProperty('tree_template'));
        $this->assertEquals('acme, project, default', $page->getNode()->getProperty('keywords'));
        $this->assertEquals('ACME: see for yourself', $page->getNode()->getProperty('description'));

        $this->assertTrue($page->hasArea('main'), 'Homepage has main area');
        $this->assertFalse($page->getArea('main')->isEmpty(), 'Homepage main area is not empty');
        $this->assertFalse($page->getArea('main')->isInherited(), 'Homepage main is not inherited area');
        $this->assertTrue($page->getArea('main')->getContentByIndex(0) instanceof Content, 'Content is instanceof Content');
        $this->assertEquals('markdown', $page->getArea('main')->getContentByIndex(0)->getType(), 'Markdown content type properly read');
        $this->assertStringContainsString('/.main/perex.markdown', $page->getArea('main')->getContentByIndex(0)->getPath(), 'Path is set');
        $this->assertEquals('html', $page->getArea('main')->getContentByIndex(1)->getType(), 'HTML content type properly read');
        $this->assertStringContainsString('/.main/main_story.html', $page->getArea('main')->getContentByIndex(1)->getPath(), 'Path is set');
        $this->assertTrue($page->getArea('main')->getContentByIndex(2) === null);
        $this->assertTrue($page->hasArea('footer'), 'Homepage has footer area');
        $this->assertFalse($page->getArea('footer')->isInherited(), 'Homepage doesn\'t have inherited footer area');
        $this->assertFalse($page->hasArea('foo'), 'Homepage doesn\'t have foo area');
    }

    public function testLocationPage()
    {
        $this->createRequestStack('/contact-us/location');
        $page     = $this->getContainer()->get('coral.page');
        $renderer = $this->getContainer()->get('coral.renderer');

        $this->assertTrue(null === $page->getNode()->parent());
        $this->assertTrue(null === $page->getNode()->prev());
        $this->assertTrue(null === $page->getNode()->next());
        $this->assertFalse($page->getNode()->hasChildren());
        $this->assertEquals('Location', $page->getNode()->getName());
        $this->assertEquals('/contact-us/location', $page->getNode()->getUri());
        $this->assertFalse($page->getNode()->hasProperty('keywords'));
        $this->assertEquals('Contact Us: where to find us', $page->getNode()->getProperty('description'));
        $this->assertEquals('default.html.twig', $page->getNode()->getProperty('template'));
        $this->assertEquals('default.html.twig', $page->getNode()->getProperty('tree_template'));

        $this->assertFalse($page->hasArea('main'), 'Location doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Location has footer area');
        $this->assertFalse($page->getArea('footer')->isInherited(), 'Location footer area is not inherited');
        $this->assertFalse($page->getArea('footer')->isEmpty(), 'Location footer area is not empty');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertEquals('html', $page->getArea('footer')->getContentByIndex(0)->getType(), 'HTML content type properly read');
        $this->assertStringContainsString('/contact-us/location/.footer/footer.html', $page->getArea('footer')->getContentByIndex(0)->getPath(), 'Path is filled');
        $this->assertTrue($page->hasArea('empty'), 'Location has empty area');
        $this->assertFalse($page->getArea('empty')->isEmpty(), 'Location empty area is really empty');
        $this->assertStringContainsString('/contact-us/location/.empty/content.md', $page->getArea('empty')->getContentByIndex(0)->getPath(), 'Path is filled in empty area without sortorder');
        $this->assertFalse($page->hasArea('foo'), 'Location doesn\'t have foo area');

        $this->assertEquals(
            '<p>Different footer pure html.</p> <p><strong>included</strong></p>',
            trim($renderer->render($page->getArea('footer')->getContentByIndex(0), array()))
        );
    }

    public function testInvalid()
    {
        $this->expectException('Coral\SiteBundle\Exception\PageException');

        $this->createRequestStack('/invalid');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('main');
    }

    public function testInvalidByUri()
    {
        $this->expectException('Coral\SiteBundle\Exception\PageException');

        $page = $this->getContainer()->get('coral.page');
        $page->setNodeByUri('/invalid');
        $page->getArea('main');
    }

    public function testInvalidArea()
    {
        $this->expectException('Coral\SiteBundle\Exception\PageException');

        $this->createRequestStack('/invalid');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('invalid_area');
    }

    public function testInvalidParentValidChild()
    {
        $this->expectException('Coral\SiteBundle\Exception\SitemapException');

        $this->createRequestStack('/invalid_parent/valid_child');
        $page = $this->getContainer()->get('coral.page');
        $page->getArea('invalid_area');

        $this->assertEquals('Products', $page->getNode()->getName());
    }
}