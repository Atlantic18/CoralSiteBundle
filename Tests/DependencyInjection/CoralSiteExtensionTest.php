<?php

namespace Coral\SiteBundle\Tests\DependencyInjection;

use Coral\SiteBundle\DependencyInjection\CoralSiteExtension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

class CoralSiteExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getFormats
     */
    public function testLoadEmptyConfiguration($format)
    {
        $container = $this->createContainer();
        $container->registerExtension(new CoralSiteExtension());
        $this->loadFromFile($container, 'empty', $format);
        $this->compileContainer($container);

        $this->assertEquals(dirname(__FILE__) . '/Fixtures/Resources/Content', $container->getParameter('coral.content.path'));
        $this->assertEquals(dirname(__FILE__) . '/Fixtures/Resources/Configuration', $container->getParameter('coral.config.path'));
    }

    /**
     * @dataProvider getFormats
     */
    public function testLoadFullConfiguration($format)
    {
        $container = $this->createContainer();
        $container->registerExtension(new CoralSiteExtension());
        $this->loadFromFile($container, 'full', $format);
        $this->compileContainer($container);

        $this->assertEquals('custom_path', $container->getParameter('coral.content.path'));
        $this->assertEquals('custom_config_path', $container->getParameter('coral.config.path'));
    }

    public function getFormats()
    {
        return array(
            array('yml')
        );
    }

    private function createContainer()
    {
        $container = new ContainerBuilder(new ParameterBag(array(
            'kernel.cache_dir' => __DIR__,
            'kernel.root_dir'  => __DIR__.'/Fixtures',
            'kernel.charset'   => 'UTF-8',
            'kernel.debug'     => false,
            'kernel.bundles'   => array('CoralSiteBundle' => 'Coral\\SiteBundle\\CoralSiteBundle'),
        )));

        return $container;
    }

    private function compileContainer(ContainerBuilder $container)
    {
        $container->getCompilerPassConfig()->setOptimizationPasses(array());
        $container->getCompilerPassConfig()->setRemovingPasses(array());
        $container->compile();
    }

    private function loadFromFile(ContainerBuilder $container, $file, $format)
    {
        $locator = new FileLocator(__DIR__.'/Fixtures/'.$format);

        switch ($format) {
            case 'php':
                $loader = new PhpFileLoader($container, $locator);
                break;
            case 'xml':
                $loader = new XmlFileLoader($container, $locator);
                break;
            case 'yml':
                $loader = new YamlFileLoader($container, $locator);
                break;
            default:
                throw new \InvalidArgumentException(sprintf('Unsupported format: %s', $format));
        }

        $loader->load($file.'.'.$format);
    }
}