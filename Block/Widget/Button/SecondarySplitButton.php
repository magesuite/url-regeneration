<?php

namespace MageSuite\UrlRegeneration\Block\Widget\Button;

class SecondarySplitButton extends \Magento\Backend\Block\Widget\Button\SplitButton
{
    public function getButtonAttributesHtml()
    {
        $buttonAttributesHtml = parent::getButtonAttributesHtml();
        $buttonAttributesHtml = str_replace('primary', 'secondary', $buttonAttributesHtml);

        return $buttonAttributesHtml;
    }

    public function getToggleAttributesHtml()
    {
        $toggleAttributesHtml = parent::getToggleAttributesHtml();
        $toggleAttributesHtml = str_replace('primary', 'secondary', $toggleAttributesHtml);

        return $toggleAttributesHtml;
    }
}