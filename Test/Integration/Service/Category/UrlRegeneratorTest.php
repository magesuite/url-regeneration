<?php

namespace MageSuite\UrlRegeneration\Test\Integration\Service\Category;

class UrlRegeneratorTest  extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \MageSuite\UrlRegeneration\Service\Category\UrlGenerator
     */
    protected $urlRegenerator;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\UrlRewrite\Model\UrlPersistInterface
     */
    protected $urlPersister;

    public function setUp()
    {
        $this->objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $this->urlRegenerator = $this->objectManager->create(\MageSuite\UrlRegeneration\Service\Category\UrlGenerator::class);
        $this->urlPersister = $this->objectManager->create(\Magento\UrlRewrite\Model\UrlPersistInterface::class);
        $this->urlFinder = $this->objectManager->create(\Magento\UrlRewrite\Model\UrlFinderInterface::class);
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testItRegeneratesUrlOnlyForSpecifiedCategory()
    {
        $this->deleteAllUrls(3);

        $this->assertCategoryUrlIsNull(3);

        $this->urlRegenerator->regenerate(3, false);

        $this->assertCategoryUrl(3, 'category-1.html');
    }

    /**
     * @magentoAppIsolation enabled
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testItRegeneratesUrlForCategoryAndItsSubcategories()
    {
        $categoryId = 3;

        $this->deleteAllUrls(3);
        $this->deleteAllUrls(4);
        $this->deleteAllUrls(5);

        $this->assertCategoryUrlIsNull(3);
        $this->assertCategoryUrlIsNull(4);
        $this->assertCategoryUrlIsNull(5);

        $this->urlRegenerator->regenerate($categoryId, true);

        $this->assertCategoryUrl(3, 'category-1.html');
        $this->assertCategoryUrl(4, 'category-1/category-1-1.html');
        $this->assertCategoryUrl(5, 'category-1/category-1-1/category-1-1-1.html');
    }

    /**
     * @param $product
     */
    protected function deleteAllUrls($categoryId)
    {
        $this->urlPersister->deleteByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $categoryId,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE
        ]);
    }

    /**
     * @param $product
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    protected function findCategoryUrl($categoryId)
    {
        return $this->urlFinder->findOneByData([
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $categoryId,
            \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator::ENTITY_TYPE
        ]);
    }

    /**
     * @param $categoryId
     */
    protected function assertCategoryUrl($categoryId, $expectedUrl)
    {
        $result = $this->findCategoryUrl($categoryId);

        $this->assertNotNull($result);
        $this->assertEquals($expectedUrl, $result->getRequestPath());
    }

    /**
     * @param $categoryId
     */
    protected function assertCategoryUrlIsNull($categoryId)
    {
        $this->assertNull($this->findCategoryUrl($categoryId));
    }
}
