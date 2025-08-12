<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Service\Product;

class UrlGenerator
{
    public function __construct(
        protected \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        protected \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator $productUrlRewriteGenerator,
        protected \Magento\UrlRewrite\Model\UrlPersistInterface $urlPersist,
        protected \Magento\Store\Model\StoreManagerInterface $storeManager,
        protected \Magento\Framework\App\ResourceConnection $resourceModel
    ) {
    }

    public function regenerate(array $productIds = []): void
    {
        $stores = $this->storeManager->getStores(false);

        foreach ($stores as $store) {
            $this->regenerateStoreUrls($store, $productIds);
        }
    }

    public function regenerateMissing(): void
    {
        $stores = $this->storeManager->getStores(false);

        foreach ($stores as $store) {
            $productIds = $this->getMissingProductsIds($store);
            $this->regenerateStoreUrls($store, $productIds);
        }
    }

    public function getMissingProductsIds(\Magento\Store\Model\Store $store): array
    {
        $productTable = $this->resourceModel->getTableName('catalog_product_entity');
        $urlRewriteTable = $this->resourceModel->getTableName('url_rewrite');
        $productRelationTable = $this->resourceModel->getTableName('catalog_product_relation');

        $connection = $this->resourceModel->getConnection();
        $joinUrlRewriteCondition = $connection->quoteInto(
            'p.entity_id = u.entity_id AND u.entity_type = \'product\' AND u.store_id = ? AND u.redirect_type = 0',
            (int)$store->getId()
        );

        $dbSelect = $connection
            ->select()
            ->distinct()
            ->from(['p' => $productTable], 'entity_id')
            ->joinLeft(['u' => $urlRewriteTable], $joinUrlRewriteCondition)
            ->joinLeft(['r' => $productRelationTable], 'p.entity_id = r.child_id')
            ->where('u.`url_rewrite_id` IS NULL AND r.`parent_id` IS NULL');

        return $this->resourceModel->getConnection()->fetchCol($dbSelect);
    }

    protected function regenerateStoreUrls(\Magento\Store\Model\Store $store, array $productIds = []): void
    {
        $collection = $this->collectionFactory->create();

        $storeId = $store->getId();

        $collection->addStoreFilter($storeId)
            ->setStoreId($storeId);

        if (!empty($productIds)) {
            $collection->addIdFilter($productIds);
        }

        $collection
            ->addAttributeToFilter('visibility', ['neq' => \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE])
            ->addAttributeToSelect(['url_path', 'url_key']);

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
            } catch (\Exception $e) {} //phpcs:ignore
        }
    }
}
