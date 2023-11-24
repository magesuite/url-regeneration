<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Test\Unit\Service\Category;

class RemoveRewritesWithEmptyRequestPath extends \PHPUnit\Framework\TestCase
{
    private ?\MageSuite\UrlRegeneration\Service\Category\RemoveRewritesWithEmptyRequestPath $service = null;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();

        $this->service = $objectManager->get(\MageSuite\UrlRegeneration\Service\Category\RemoveRewritesWithEmptyRequestPath::class);
    }

    /**
     * @dataProvider rewritesDataProvider
     */
    public function testExecute(array $rewrites, array $expected): void
    {
        $actual = $this->service->execute($rewrites);

        $expectedData = array_map(function ($rewrite) {
            return $rewrite->toArray();
        }, $expected);

        $actualData = array_map(function ($rewrite) {
            return $rewrite->toArray();
        }, $actual);

        $this->assertEquals($expectedData, $actualData);
    }

    public function rewritesDataProvider(): array
    {
        return [
            [
                $this->getInputCaseOne(),
                $this->getExpectedCaseOne(),
            ]
        ];
    }

    private function getInputCaseOne(): array
    {
        // Cannot be instantiated in setUp method, as data providers are being executed before setUp.
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $rewriteFactory = $objectManager->get(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class);

        return [
            'men/accessories/hats' => $rewriteFactory->create(
                [
                    'data' => [
                        'store_id' => 1,
                        'entity_type' => 'category',
                        'entity_id' => 95,
                        'request_path' => 'men/accessories/hats',
                        'target_path' => 'catalog/category/view/id/95'
                    ]
                ]
            ),
            '' => $rewriteFactory->create(
                [
                    'data' => [
                        'store_id' => 1,
                        'entity_type' => 'category',
                        'entity_id' => 96,
                        'request_path' => '',
                        'target_path' => 'catalog/category/view/id/96'
                    ]
                ]
            ),
            'men/accessories/belts' => $rewriteFactory->create(
                [
                    'data' => [
                        'store_id' => 1,
                        'entity_type' => 'category',
                        'entity_id' => 97,
                        'request_path' => 'men/accessories/belts',
                        'target_path' => 'catalog/category/view/id/97'
                    ]
                ]
            ),
        ];
    }

    private function getExpectedCaseOne(): array
    {
        // Cannot be instantiated in setUp method, as data providers are being executed before setUp.
        $objectManager = \Magento\TestFramework\ObjectManager::getInstance();
        $rewriteFactory = $objectManager->get(\Magento\UrlRewrite\Service\V1\Data\UrlRewriteFactory::class);

        return [
            'men/accessories/hats' => $rewriteFactory->create(
                [
                    'data' => [
                        'store_id' => 1,
                        'entity_type' => 'category',
                        'entity_id' => 95,
                        'request_path' => 'men/accessories/hats',
                        'target_path' => 'catalog/category/view/id/95'
                    ]
                ]
            ),
            'men/accessories/belts' => $rewriteFactory->create(
                [
                    'data' => [
                        'store_id' => 1,
                        'entity_type' => 'category',
                        'entity_id' => 97,
                        'request_path' => 'men/accessories/belts',
                        'target_path' => 'catalog/category/view/id/97'
                    ]
                ]
            ),
        ];
    }
}
