<?php

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

    protected function getOptions()
    {
        $categoryId = $this->getCategoryId();

        $splitButtonOptions[] = [
            'label' => __('Only this category'),
            'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, false)),
            'default' => true,
        ];

        $splitButtonOptions[] = [
            'label' => __('This category and subcategories'),
            'onclick' => sprintf("setLocation('%s')", $this->getActionUrl($categoryId, true)),
            'default' => false,
        ];

        return $splitButtonOptions;
    }

    /**
     * @param $categoryId
     * @return string
     */
    protected function getActionUrl($categoryId, $withSubcategories = false)
    {
        return $this->getUrl(
            'urlregeneration/category/regenerate',
            [
                'category_id' => $categoryId,
                'with_subcategories' => $withSubcategories
            ]
        );
    }
}
