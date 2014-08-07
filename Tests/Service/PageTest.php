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
    /**
     * @expectedException Symfony\Component\DependencyInjection\Exception\RuntimeException
     */
    public function testNodeNotInjected()
    {
        $page = $this->getContainer()->get('coral.page');
    }

    public function testNodeInjectedTwice()
    {
        $root = $this->getContainer()->get('coral.sitemap')->getRoot();
        $this->getContainer()->set('coral.node', $root->getChildByIndex(2));

        $page = $this->getContainer()->get('coral.page');

        $this->getContainer()->set('coral.node', $root->getChildByIndex(1));

        $this->assertTrue($page->getNode() === $root->getChildByIndex(2));
        $this->assertFalse($page->getNode() === $root->getChildByIndex(1));
    }

    public function testContactUsPage()
    {
        //inject contact-us node
        $root = $this->getContainer()->get('coral.sitemap')->getRoot();
        $this->getContainer()->set('coral.node', $root->getChildByIndex(2));

        $page = $this->getContainer()->get('coral.page');

        $this->assertTrue(null !== $page->getNode(), 'Node is injected properly');
        $this->assertTrue($page->getNode() === $root->getChildByIndex(2));

        $this->assertFalse($page->hasArea('main'), 'Contact-us doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Contact-us has inherited footer area');
        $this->assertTrue($page->getArea('footer') instanceof Area, 'Contact-us area is instanceof Area');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertTrue($page->getArea('footer')->isInherited(), 'Contact-us has inherited footer area');
        $this->assertFalse($page->hasArea('foo'), 'Contact-us doesn\'t have foo area');
    }

    public function testHomepagePage()
    {
        //inject homepage node
        $root = $this->getContainer()->get('coral.sitemap')->getRoot();
        $this->getContainer()->set('coral.node', $root);

        $page = $this->getContainer()->get('coral.page');

        $this->assertTrue(null !== $page->getNode(), 'Node is injected properly');
        $this->assertTrue($page->getNode() === $root);
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

    public function testMarkdownContentRender()
    {
        //inject homepage node
        $root = $this->getContainer()->get('coral.sitemap')->getRoot();
        $this->getContainer()->set('coral.node', $root);
        $page = $this->getContainer()->get('coral.page');
    }

    public function testLocationPage()
    {
        //inject location node
        $root = $this->getContainer()->get('coral.sitemap')->getRoot();
        $this->getContainer()->set('coral.node', $root->getChildByIndex(2)->getChildByIndex(0));

        $page = $this->getContainer()->get('coral.page');

        $this->assertFalse($page->hasArea('main'), 'Location doesn\'t have main area');
        $this->assertTrue($page->hasArea('footer'), 'Location has footer area');
        $this->assertFalse($page->getArea('footer')->isInherited(), 'Location footer area is not inherited');
        $this->assertFalse($page->getArea('footer')->isEmpty(), 'Location footer area is not empty');
        $this->assertEquals('footer', $page->getArea('footer')->getName(), 'Area name is properly set');
        $this->assertEquals('html', $page->getArea('footer')->getContentByIndex(0)->getType(), 'HTML content type properly read');
        $this->assertContains('Different', $page->getArea('footer')->getContentByIndex(0)->getContent(), 'Content is filled');
        $this->assertTrue($page->hasArea('empty'), 'Location has empty area');
        $this->assertTrue($page->getArea('empty')->isEmpty(), 'Location empty area is really empty');
        $this->assertFalse($page->hasArea('foo'), 'Location doesn\'t have foo area');
    }
}