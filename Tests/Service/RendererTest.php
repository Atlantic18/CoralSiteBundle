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

use Coral\SiteBundle\Content\Content;
use Coral\CoreBundle\Test\WebTestCase;

class RendererTest extends WebTestCase
{
    /**
     * @expectedException Coral\SiteBundle\Exception\RenderException
     */
    public function testInvalid()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('invalid', 'content');
        $renderer->render($content);
    }

    public function testConnector()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', json_encode(array(
            'service'  => 'coral',
            'method'   => 'GET',
            'uri'      => '/v1/node/detail/published/config-logger',
            'template' => 'connect_test.twig'
        )));

        $this->assertEquals('<h2>Config Logger</h2>', trim($renderer->render($content)));
    }

    public function testMarkdown()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', "## Header 2");

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content)));
    }

    public function testMd()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('md', "## Header 2");

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content)));
    }

    public function testTxt()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', "Lorem Ipsum");

        $this->assertEquals('Lorem Ipsum', trim($renderer->render($content)));
    }

    public function testMarkdownFlavored()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', '```ruby
require \'redcarpet\'
markdown = Redcarpet.new("Hello World!")
puts markdown.to_html
```');

        $this->assertContains('<pre><code class="ruby">', trim($renderer->render($content)));
    }

    public function testHtml()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('html', "<h2>Test</h2>");

        $this->assertEquals('<h2>Test</h2>', $renderer->render($content));
    }
}