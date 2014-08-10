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

    public function testMarkdown()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', "## Header 2");

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content)));
    }

    public function testHtml()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('html', "<h2>Test</h2>");

        $this->assertEquals('<h2>Test</h2>', $renderer->render($content));
    }
}