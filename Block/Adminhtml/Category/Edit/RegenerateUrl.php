<?php
declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Block\Adminhtml\Category\Edit;

class RegenerateUrl extends \Magento\Catalog\Block\Adminhtml\Category\AbstractCategory implements \Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface
{
    public function getButtonData()
    {
        return [
            'label' => __('Regenerate URLs'),
            'class' => 'regenerate',
            'sort_order' => 10,
            'class_name' => \MageSuite\UrlRegeneration\Block\Widget\Button\SecondarySplitButton::class,
            'options' => $this->getOptions(),
        ];
    }

    protected function getOptions(): array
    {
        $categoryId = $this->getCategoryId();

        $splitButtonOptions = [
            [
                'label' => __('Only this category'),
                'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, false)),
                'default' => true,
            ],
            [
                'label' => __('This category and subcategories'),
                'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, true)),
                'default' => false,
            ],
        ];

        if ($storeId = $this->getStoreId()) {
            $splitButtonOptions[] = [
                'label' => __('This category for current store'),
                'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, false, $storeId)),
                'default' => false,
            ];
            $splitButtonOptions[] = [
                'label' => __('This category and subcategories for current store'),
                'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, true, $storeId)),
                'default' => false,
            ];
        }

        return $splitButtonOptions;
    }

    protected function getStoreId(): ?int
    {
        return (int)$this->getRequest()->getParam('store') ?? null;
    }

    protected function getActionUrl($categoryId, $withSubcategories = false, ?int $storeId = null): string
    {
        $params = [
            'category_id' => $categoryId,
            'with_subcategories' => $withSubcategories,
        ];

        if ($storeId !== null) {
            $params['store_id'] = $storeId;
        }

        return $this->getUrl(
            'urlregeneration/category/regenerate',
            $params
        );
    }
}
