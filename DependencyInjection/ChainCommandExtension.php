<?php

namespace Gurman\ChainCommandBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ChainCommandExtension extends Extension
{
    /**
     * {@inheritDoc}
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $chainsConfig = $this->processConfiguration($configuration, $configs);
        if (isset($chainsConfig['chains'])) {
            $container
                ->getDefinition('command.manager')
                ->addMethodCall('setChains', [$chainsConfig['chains']]);
        }
    }
}
