<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model;

use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\RuntimeException;
use Magento\Csp\Model\SubresourceIntegrityFactory;
use Magento\Framework\Serialize\SerializerInterface;

/**
 * Subresource Integrity repository.
 */
class SubresourceIntegrityRepository
{
    /**
     * Cache prefix.
     *
     * @var string
     */
    private const CACHE_PREFIX = 'INTEGRITY_HASH';

    /**
     * @var CacheInterface
     */
    private CacheInterface $cache;

    /**
     * @var DeploymentConfig
     */
    private DeploymentConfig $config;

    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;

    /**
     * @var SubresourceIntegrityFactory
     */
    private SubresourceIntegrityFactory $integrityFactory;

    /**
     * constructor
     *
     * @param CacheInterface $cache
     * @param DeploymentConfig $config
     * @param SerializerInterface $serializer
     * @param SubresourceIntegrityFactory $integrityFactory
     */
    public function __construct(
        CacheInterface $cache,
        DeploymentConfig $config,
        SerializerInterface $serializer,
        SubresourceIntegrityFactory $integrityFactory
    ) {
        $this->cache = $cache;
        $this->config = $config;
        $this->serializer = $serializer;
        $this->integrityFactory = $integrityFactory;
    }

    /**
     * Gets an Integrity object by URL.
     *
     * @param string $url
     *
     * @return SubresourceIntegrity|null
     */
    public function getByUrl(string $url): ?SubresourceIntegrity
    {
        $integrity = $this->cache->load(
            self::CACHE_PREFIX . $url
        );

        if (!$integrity) {
            return null;
        }

        return $this->integrityFactory->create(
            ["data" => $this->serializer->unserialize($integrity)]
        );
    }

    /**
     * Gets all available Integrity objects.
     *
     * @return SubresourceIntegrity[]
     *
     * @throws FileSystemException
     * @throws RuntimeException
     */
    public function getAll(): array
    {
        $defaultCachePrefix = $this->config->get(
            "cache/frontend/default/id_prefix"
        );

        $cacheIds = $this->cache->getFrontend()->getLowLevelFrontend()
            ->getIdsMatchingAnyTags(
                [$defaultCachePrefix . self::CACHE_PREFIX]
            );

        $result = [];

        foreach ($cacheIds as $id) {
            $integrity = $this->cache->load($id);

            if ($integrity) {
                $result[] = $this->integrityFactory->create(
                    [
                        "data" => $this->serializer->unserialize($integrity)
                    ]
                );
            }
        }

        return $result;
    }

    /**
     * Saves Integrity object.
     *
     * @param SubresourceIntegrity $integrity
     *
     * @return bool
     */
    public function save(SubresourceIntegrity $integrity): bool
    {
        return $this->cache->save(
            $this->serializer->serialize($integrity->getData()),
            self::CACHE_PREFIX . $integrity->getUrl(),
            [self::CACHE_PREFIX]
        );
    }

    /**
     * Clear contents of cache
     *
     * @param string $url
     *
     * @return bool
     */
    public function deleteByUrl(string $url): bool
    {
        return $this->cache->clean(
            [self::CACHE_PREFIX . $url]
        );
    }
}
