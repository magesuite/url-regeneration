<?php

namespace MageSuite\UrlRegeneration\Service\Category;

class UrlGenerator
{
    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator
     */
    protected $categoryUrlRewriteGenerator;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $categoryUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
        $this->categoryUrlRewriteGenerator = $categoryUrlRewriteGenerator;
        $this->categoryRepository = $categoryRepository;
    }

    public function regenerate($categoryId, $withSubcategories = false)
    {
        $stores = $this->storeManager->getStores(false);

        foreach ($stores as $store) {
            $this->deleteOldUrls($store, $categoryId, $withSubcategories);
        }

        foreach ($stores as $store) {
            $this->regenerateStoreUrls($store, $categoryId, $withSubcategories);
        }
    }

    protected function deleteOldUrls(\Magento\Store\Api\Data\StoreInterface $store, int $categoryId, bool $withSubcategories = false)
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
            $this->deleteOldUrls($store, $childCategory->getId(), true);
        }
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @param array $productIds
     */
    protected function regenerateStoreUrls(\Magento\Store\Api\Data\StoreInterface $store, int $categoryId, bool $withSubcategories = false)
    {
        $category = $this->getCategoryByStore($categoryId, $store);

        if (!$this->isCategoryInStore($category, $store)) {
            return;
        }

        $newUrls = $this->categoryUrlRewriteGenerator->generate($category);

        try {
            $this->urlPersist->replace($newUrls);
        } catch (\Exception $e) {} //phpcs:ignore

        if (!$withSubcategories || !$category->getChildrenCategories()) {
            return;
        }

        foreach ($category->getChildrenCategories() as $childCategory) {
            $this->regenerateStoreUrls($store, (int)$childCategory->getId(), true);
        }
    }

    /**
     * @param $store
     * @param $categoryId
     * @return \Magento\Catalog\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getCategoryByStore($categoryId, $store): \Magento\Catalog\Api\Data\CategoryInterface
    {
        $storeId = $store->getId();

        $category = $this->categoryRepository->get($categoryId, $storeId);

        $category->setStoreId($storeId);
        $category->load($categoryId);
        return $category;
    }

    protected function isCategoryInStore($category, $store)
    {
        return in_array($store->getRootCategoryId(), $category->getPathIds());
    }
}
