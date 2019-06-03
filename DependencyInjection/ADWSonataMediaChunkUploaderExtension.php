<?php

namespace ADW\SonataMediaChunkUploader\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class ADWSonataMediaChunkUploaderExtension
 * @package ADW\SonataMediaChunkUploader\DependencyInjection
 */
class ADWSonataMediaChunkUploaderExtension extends Extension
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
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $this->container = $container;

        $configuration = new Configuration();
        $this->config  = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($this->container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('adw.sonata.chunks.settings', $this->config['chunks']);
        $this->registerStorageService();
    }

    protected function registerStorageService()
    {
        $storageClass = sprintf('%%adw.sonata.chunks.storage.%s.class%%', $this->config['storage']['type']);
        $folder       = $this->config['chunks']['chunk_folder'];

        switch ($this->config['storage']['type']) {
            case 'filesystem':
                $folder = null === $folder ? sprintf('%s/uploader/chunks', $this->container->getParameter('kernel.cache_dir')) : $folder;

                $this->container->register('adw.sonata.chunks.storage', $storageClass)->addArgument($folder)->setPublic(true);

                break;

            default:
                throw new \InvalidArgumentException(sprintf('Filesystem "%s" is invalid', $this->config['storage']['type']));

                break;
        }
    }

}