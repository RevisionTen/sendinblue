<?php

namespace RevisionTen\Mailchimp\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class SendinblueExtension extends Extension implements PrependExtensionInterface
{
    /**
     * Merge all Mailchimp configs in reverse order.
     * First (user defined) config is most important.
     *
     * @param array $configs
     *
     * @return array
     */
    private static function mergeSendinblueConfig(array $configs): array
    {
        $configs = array_reverse($configs);
        $config = [];
        foreach ($configs as $subConfig) {
            $config = array_merge($config, $subConfig);
        }

        return $config;
    }

    public function load(array $configs, ContainerBuilder $container)
    {
        $config = self::mergeSendinblueConfig($configs);
        $container->setParameter('sendinblue', $config);
    }

    public function prepend(ContainerBuilder $container)
    {
        // Load the cms bundle config.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('config.yaml');
        $loader->load('sendinblue.yaml');
    }
}
