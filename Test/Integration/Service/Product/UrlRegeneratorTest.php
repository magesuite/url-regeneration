<?php

namespace MageSuite\UrlRegeneration\Test\Integration\Service\Product;

class UrlRegeneratorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\UrlRegeneration\Service\Product\UrlGenerator
     */
    protected $urlRegenerator;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersister;

    public function setUp(): void
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->urlRegenerator = $this->objectManager->create(\MageSuite\UrlRegeneration\Service\Product\UrlGenerator::class);
        $this->productRepository = $this->objectManager->create(\Magento\Catalog\Api\ProductRepositoryInterface::class);
        $this->urlPersister = $this->objectManager->create(\Magento\UrlRewrite\Model\UrlPersistInterface::class);
        $this->urlFinder = $this->objectManager->create(\Magento\UrlRewrite\Model\UrlFinderInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/products_for_search.php
     */
    public function testItRegeneratesUrlForSpecifiedProduct()
    {
        $product = $this->productRepository->get('search_product_1');

        $this->deleteAllUrls($product);

        $result = $this->findProductUrl($product);

        $this->assertNull($result);

        $this->urlRegenerator->regenerate([$product->getId()]);

        $result = $this->findProductUrl($product);

        $this->assertNotNull($result);
        $this->assertEquals('search-product-1.html', $result->getRequestPath());
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/multiple_mixed_products.php
     */
    public function testItRegeneratesUrlsOnlyForVisibleProducts()
    {
        $notVisibleProductSku = 'simple_41';
        $product = $this->productRepository->get($notVisibleProductSku);

        $this->deleteAllUrls($product);

        $result = $this->findProductUrl($product);

        $this->assertNull($result);

        $this->urlRegenerator->regenerate([$product->getId()]);

        $result = $this->findProductUrl($product);

        $this->assertNull($result);
    }

    /**
     * @param $product
     */
    protected function deleteAllUrls($product)
    {
        $this->urlPersister->deleteByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE
        ]);
    }

    /**
     * @param $product
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    protected function findProductUrl($product)
    {
        return $this->urlFinder->findOneByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(),
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE
        ]);
    }
}
