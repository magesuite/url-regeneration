<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Service\Category;

class UrlGenerator
{
    protected \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist;
    protected \Magento\Store\Model\StoreManagerInterface $storeManager;
    protected \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator;
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected \Psr\Log\LoggerInterface $logger;
    protected \Magento\Framework\App\RequestInterface $request;

    public function __construct(
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->categoryRepository = $categoryRepository;
        $this->logger = $logger;
        $this->request = $request;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function regenerate(int $categoryId, bool $withSubcategories = false, ?array $storeIds = null): void
    {
        $stores = $this->getStores($storeIds);

        foreach ($stores as $store) {
            $this->deleteOldUrls($store, $categoryId, $withSubcategories);
            $this->regenerateStoreUrls($store, $categoryId, $withSubcategories);
        }
    }

    public function getStores(?array $storeIds = null): array
    {
        $stores = $this->storeManager->getStores();

        if ($storeIds !== null) {
            $stores = array_intersect_key($stores, array_flip($storeIds));
        }

        return $stores;
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function deleteOldUrls(\Magento\Store\Api\Data\StoreInterface $store, int $categoryId, bool $withSubcategories = false): void
    {
        $category = $this->getCategoryByStore($categoryId, $store);

        if (!$this->isCategoryInStore($category, $store)) {
            return;
        }

        $this->urlPersist->deleteByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $category->getId(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $store->getId()
        ]);

        if (!$withSubcategories || !$category->getChildrenCategories()) {
            return;
        }

        foreach ($category->getChildrenCategories() as $childCategory) {
            $this->deleteOldUrls($store, (int)$childCategory->getId(), true);
        }
    }

    protected function regenerateStoreUrls(\Magento\Store\Api\Data\StoreInterface $store, int $categoryId, bool $withSubcategories = false): void
    {
        $category = $this->getCategoryByStore($categoryId, $store);

        if (!$this->isCategoryInStore($category, $store)) {
            return;
        }

        $rewrites = $this->categoryUrlRewriteGenerator->generate($category);
        $rewrites = array_filter($rewrites, function ($rewrite) {
            return !empty($rewrite->getRequestPath());
        });

        try {
            $this->urlPersist->replace($rewrites);
        } catch (\Exception $e) {
            $this->logger->error('Exception during regenerating url rewrites: ' . $e->getMessage(), $e->getTrace());
        }

        if (!$withSubcategories || !$category->getChildrenCategories()) {
            return;
        }

        foreach ($category->getChildrenCategories() as $childCategory) {
            $this->regenerateStoreUrls($store, (int)$childCategory->getId(), true);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategoryByStore(int $categoryId, \Magento\Store\Api\Data\StoreInterface $store): \Magento\Catalog\Api\Data\CategoryInterface
    {
        $storeId = $store->getId();

        $category = $this->categoryRepository->get($categoryId, $storeId);

        $category->setStoreId($storeId);
        $category->load($categoryId);
        return $category;
    }

    protected function isCategoryInStore(\Magento\Catalog\Api\Data\CategoryInterface $category, \Magento\Store\Api\Data\StoreInterface $store): bool
    {
        return in_array($store->getRootCategoryId(), $category->getPathIds());
    }
}
