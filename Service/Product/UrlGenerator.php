<?php

namespace MageSuite\UrlRegeneration\Service\Product;

class UrlGenerator
{
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator
     */
    protected $productUrlRewriteGenerator;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersist;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->productUrlRewriteGenerator = $productUrlRewriteGenerator;
        $this->urlPersist = $urlPersist;
        $this->storeManager = $storeManager;
    }

    public function regenerate($productIds = []) {
        $stores = $this->storeManager->getStores(false);

        foreach ($stores as $store) {
            $this->regenerateStoreUrls($store, $productIds);
        }
    }

    /**
     * @param \Magento\Store\Model\Store $store
     * @param array $productIds
     */
    protected function regenerateStoreUrls($store, $productIds = [])
    {
        $collection = $this->collectionFactory->create();

        $storeId = $store->getId();

        $collection->addStoreFilter($storeId)
            ->setStoreId($storeId);

        if (!empty($productIds)) {
            $collection->addIdFilter($productIds);
        }

        $collection->addAttributeToSelect(['url_path', 'url_key']);

        $products = $collection->load();

        foreach ($products as $product) {
            $product->setStoreId($storeId);

            $this->urlPersist->deleteByData([
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE,
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $storeId
            ]);

            $newUrls = $this->productUrlRewriteGenerator->generate($product);

            try {
                $this->urlPersist->replace($newUrls);
            } catch (\Exception $e) {}
        }
    }
}