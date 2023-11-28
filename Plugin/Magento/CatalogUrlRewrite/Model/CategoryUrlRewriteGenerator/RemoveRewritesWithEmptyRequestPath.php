<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Plugin\Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator;

/**
 * Empty url target path is returned intentionally by Magento for root category in @see \Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator::getUrlPath
 */
class RemoveRewritesWithEmptyRequestPath
{
    protected \MageSuite\UrlRegeneration\Service\Category\RemoveRewritesWithEmptyRequestPath $removeRewritesWithEmptyRequestPath;

    public function __construct(
        \MageSuite\UrlRegeneration\Service\Category\RemoveRewritesWithEmptyRequestPath $removeRewritesWithEmptyRequestPath
    ) {
        $this->removeRewritesWithEmptyRequestPath = $removeRewritesWithEmptyRequestPath;
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $result
     *
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function afterGenerate(
        \Magento\CatalogUrlRewrite\Model\CategoryUrlRewriteGenerator $subject,
        array $result
    ): array {
        return $this->removeRewritesWithEmptyRequestPath->execute($result);
    }
}
