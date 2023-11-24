<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Service\Category;

class RemoveRewritesWithEmptyRequestPath
{
    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[] $rewrites
     *
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite[]
     */
    public function execute(array $rewrites): array
    {
        return array_filter($rewrites, function ($item) {
            return !empty($item->getRequestPath());
        });
    }
}
