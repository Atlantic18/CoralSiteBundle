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
    public function testInvalidType()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('invalid', '_renderer_test_content/test_connector.connect');
        $renderer->render($content);
    }

    /**
     * @expectedException Coral\SiteBundle\Exception\RenderException
     */
    public function testInvalidFile()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('md', 'invalid file name');
        $renderer->render($content);
    }

    public function testConnector()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $this->getContainer()->get('coral.context')->set('uri_param', 'published');

        $content = new Content('connect', '_renderer_test_content/test_connector.connect');

        $this->assertContains('Config Logger', trim($renderer->render($content)));
    }

    public function testConnectorWithVariables()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', '/_renderer_test_content/test_connector_with_variables.connect');

        $this->assertEquals('<h2>Config Logger</h2>foo:bar,foo2:bar2', trim($renderer->render($content)));
    }

    public function testConnectorWithLocalTwigTemplate()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', '/_renderer_test_content/test_connector_with_local_twig_template.connect');

        $this->assertEquals('<h3>Config Logger</h3>foo:bar,foo2:bar2', trim($renderer->render($content)));
    }

    public function testTwig()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('twig', '_renderer_test_content/test_twig.twig');

        $this->assertEquals('3 = 3', trim($renderer->render($content)));
    }

    /**
     * @expectedException \Twig_Error_Runtime
     */
    public function testConnectorMissingVariables()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', '/_renderer_test_content/test_connector_missing_variables.connect');

        $this->assertEquals('<h2>Config Logger</h2>foo:bar,foo2:bar2', trim($renderer->render($content)));
    }

    public function testMarkdown()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', '/_renderer_test_content/test_markdown.markdown');

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content)));
    }

    public function testMd()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('md', '_renderer_test_content/test_md.md');

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content)));
    }

    public function testTxt()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_txt.txt');

        $this->assertEquals('Lorem Ipsum', trim($renderer->render($content)));
    }

    public function testMarkdownFlavored()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', '_renderer_test_content/test_markdown_flavored.markdown');

        $this->assertContains('<pre><code class="ruby">', trim($renderer->render($content)));
    }

    public function testHtml()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('html', '_renderer_test_content/test_html.html');

        $this->assertEquals('<h2>Test</h2>', $renderer->render($content));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderIncludeInvalidOutside()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_outside.txt');
        $renderer->render($content);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenderWithIncludeInvalid()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_invalid.txt');
        $renderer->render($content);
    }

    public function testRenderWithInclude()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $correctResult = "Lorem Ipsum <p>Copyright information for all pages.</p>";

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_1.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content)));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_2.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content)));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_3.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content)));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_4.txt');
        $this->assertEquals(
            "Lorem Ipsum {{ include_tree_footer/global_footer.markdown }}",
            $renderer->render($content)
        );
    }

    public function testRenderWithMultipleInclude()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $correctResult = "Lorem Ipsum <p>Copyright information for all pages.</p>\n<p>Copyright information for all pages.</p>";

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_multiple_include.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content)));
    }
}