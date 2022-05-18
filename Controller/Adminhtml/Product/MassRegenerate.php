<?php

namespace MageSuite\UrlRegeneration\Controller\Adminhtml\Product;

class MassRegenerate extends \Magento\Backend\App\Action
{
    /**
     * @var \MageSuite\UrlRegeneration\Service\Product\UrlGenerator
     */
    protected $productUrlGenerator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\UrlRegeneration\Service\Product\UrlGenerator $productUrlGenerator,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        parent::__construct($context);

        $this->productUrlGenerator = $productUrlGenerator;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        $postData = $this->_request->getPost();

        $productsIds = $postData['selected'] ?? [];

        $this->productUrlGenerator->regenerate($productsIds);

        $this->messageManager->addSuccessMessage(__('URLs were regenerated successfully'));

        return $this->_redirect($this->_redirect->getRefererUrl());
    }
}
