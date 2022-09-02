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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RendererTest extends KernelTestCase
{
    public function testInvalidType()
    {
        $this->expectException('Coral\SiteBundle\Exception\RenderException');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('invalid', '_renderer_test_content/test_connector.connect');
        $renderer->render($content, array());
    }

    public function testInvalidFile()
    {
        $this->expectException('Coral\SiteBundle\Exception\RenderException');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('md', 'invalid file name');
        $renderer->render($content, array());
    }

    public function testMissingContentPath()
    {
        $this->expectException('Coral\SiteBundle\Exception\ConfigurationException');

        new \Coral\SiteBundle\Content\Filter\Passthru('invalid');
    }

    public function testConnector()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $this->getContainer()->get('coral.context')->set('uri_param', 'published');

        $content = new Content('connect', '_renderer_test_content/test_connector.connect');

        $this->assertStringContainsString('Config Logger', trim($renderer->render($content, array())));
    }

    public function testConnectorWithVariables()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', '/_renderer_test_content/test_connector_with_variables.connect');

        $this->assertEquals('<h2>Config Logger</h2>foo:bar,foo2:bar2', trim($renderer->render($content, array())));
    }

    public function testConnectorWithLocalTwigTemplate()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $this->getContainer()->get('coral.context')->set('foo_param', 'foo');

        $content = new Content('connect', '/_renderer_test_content/test_connector_with_local_twig_template.connect');

        $this->assertEquals('<h3>Config Logger</h3>foo:bar,foo2:bar2', trim($renderer->render($content, array())));
    }

    public function testConnectorWithLocalTwigTemplateMissingFooParam()
    {
        $this->expectException('Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException');

        $content = new Content('connect', '/_renderer_test_content/test_connector_with_local_twig_template.connect');
        $this->getContainer()->get('coral.renderer')->render($content, array());
    }

    public function testTwig()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('twig', '_renderer_test_content/test_twig.twig');

        $this->assertEquals('3 = 3', trim($renderer->render($content, array())));
    }

    public function testTwigParameters()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('twig', '_renderer_test_content/test_twig_parameters.twig');

        $this->assertEquals('foo = bar', trim($renderer->render($content, array('foo' => 'bar'))));
    }

    public function testConnectorMissingVariables()
    {
        $this->expectException('Twig\Error\RuntimeError');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('connect', '/_renderer_test_content/test_connector_missing_variables.connect');

        $this->assertEquals('<h2>Config Logger</h2>foo:bar,foo2:bar2', trim($renderer->render($content, array())));
    }

    public function testMarkdown()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', '/_renderer_test_content/test_markdown.markdown');

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content, array())));
    }

    public function testMd()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('md', '_renderer_test_content/test_md.md');

        $this->assertEquals('<h2>Header 2</h2>', trim($renderer->render($content, array())));
    }

    public function testTxt()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_txt.txt');

        $this->assertEquals('Lorem Ipsum', trim($renderer->render($content, array())));
    }

    public function testMarkdownFlavored()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('markdown', '_renderer_test_content/test_markdown_flavored.markdown');
        $this->assertStringContainsString('<pre><code class="language-ruby">', trim($renderer->render($content, array())));
    }

    public function testHtml()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('html', '_renderer_test_content/test_html.html');

        $this->assertEquals('<h2>Test</h2>', $renderer->render($content, array()));
    }

    public function testRenderIncludeInvalidOutside()
    {
        $this->expectException('InvalidArgumentException');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_outside.txt');
        $renderer->render($content, array());
    }

    public function testRenderWithIncludeInvalid()
    {
        $this->expectException('InvalidArgumentException');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_invalid.txt');
        $renderer->render($content, array());
    }

    public function testRenderWithIncludeInvalid2()
    {
        $this->expectException('InvalidArgumentException');

        $renderer = $this->getContainer()->get('coral.renderer');

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_invalid_2.txt');
        $renderer->render($content, array());
    }

    public function testRenderWithInclude()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $correctResult = "Lorem Ipsum <p>Copyright information for all pages.</p>";

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_1.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content, array())));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_2.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content, array())));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_3.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content, array())));

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_include_4.txt');
        $this->assertEquals(
            "Lorem Ipsum {{ include_tree_footer/global_footer.markdown }}",
            $renderer->render($content, array())
        );
    }

    public function testRenderWithMultipleInclude()
    {
        $renderer = $this->getContainer()->get('coral.renderer');

        $correctResult = "Lorem Ipsum <p>Copyright information for all pages.</p>\n<p>Copyright information for all pages.</p>";

        $content = new Content('txt', '_renderer_test_content/test_renderer_with_multiple_include.txt');
        $this->assertEquals($correctResult, trim($renderer->render($content, array())));
    }
}