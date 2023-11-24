<?php

declare(strict_types=1);

namespace MageSuite\UrlRegeneration\Controller\Adminhtml\Category;

class Regenerate extends \Magento\Backend\App\Action
{
    protected \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository;
    protected \MageSuite\UrlRegeneration\Service\Category\UrlGenerator $categoryUrlGenerator;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository,
        \MageSuite\UrlRegeneration\Service\Category\UrlGenerator $categoryUrlGenerator
    ) {
        $this->categoryUrlGenerator = $categoryUrlGenerator;
        $this->categoryRepository = $categoryRepository;

        parent::__construct($context);
    }

    /**
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(): \Magento\Framework\App\ResponseInterface
    {
        $categoryId = (int)$this->_request->getParam('category_id');
        $withSubcategories = (bool)$this->_request->getParam('with_subcategories');

        $this->categoryUrlGenerator->regenerate($categoryId, $withSubcategories);

        $this->messageManager->addSuccessMessage(__('URLs were regenerated successfully'));

        return $this->_redirect('catalog/category/edit', ['id' => $categoryId]);
    }
}
