<?php

namespace Coral\SiteBundle\Tests\Resources\app;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class AppKernel extends BaseKernel
{
    use MicroKernelTrait;

    public function getProjectDir()
    {
        $refl = new \ReflectionClass($this);
        $fname = $refl->getFileName();
        $kernelDir = dirname($fname);

        return $kernelDir;
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/cache';
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/logs';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);

        $loader->load($this->getProjectDir().'/config/config.yaml');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->import($this->getProjectDir().'/config/routes.yaml');
    }
}