<?php

namespace Kolyadin\SonataMediaChunkUploader\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class SonataMediaChunkUploaderExtension
 * @package Kolyadin\SonataMediaChunkUploader\DependencyInjection
 */
class SonataMediaChunkUploaderExtension extends Extension implements PrependExtensionInterface
{
    /**
     * @var array $storageServices
     */
    protected $storageServices = [];

    /**
     * @var ContainerBuilder $container
     */
    protected $container;

    /**
     * @var array $config
     */
    protected $config;

    /**
     * @param array            $configs
     * @param ContainerBuilder $container
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;

        $configuration = new Configuration();
        $this->config  = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($this->container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');
        $loader->load('controllers.yml');

        $container->setParameter('sonata.chunks.settings', $this->config['chunks']);
        $this->registerStorageService();
    }

    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');

        if (isset($bundles['SonataMediaBundle'])) {
            $sonataMediaConfig = $container->getExtensionConfig('sonata_media')[0];

            $directory = $sonataMediaConfig['filesystem']['local']['directory'];
            $directory = null === $directory ? sprintf('%s/../web/uploads/media', $this->container->getParameter('kernel.root_dir')) : $directory;

            $container->setParameter('sonata.media.adapter.filesystem.local.path', $directory);
        }
    }

    protected function registerStorageService()
    {
        $storageClass = sprintf('%%sonata.chunks.storage.%s.class%%', $this->config['storage']['type']);
        $folder       = $this->config['chunks']['chunk_folder'];

        switch ($this->config['storage']['type']) {
            case 'filesystem':
                $folder = null === $folder ? sprintf('%s/uploader/chunks', $this->container->getParameter('kernel.cache_dir')) : $folder;

                $this->container->register('sonata.chunks.storage', $storageClass)->addArgument($folder)->setPublic(true);

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Filesystem "%s" is invalid', $this->config['storage']['type']));

                break;
        }
    }

}