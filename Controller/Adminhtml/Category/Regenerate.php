<?php

namespace MageSuite\UrlRegeneration\Controller\Adminhtml\Category;

class Regenerate extends \Magento\Backend\App\Action
{
    /**
     * @var \MageSuite\UrlRegeneration\Service\Category\UrlGenerator
     */
    protected $categoryUrlGenerator;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    protected $categoryRepository;

    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \MageSuite\UrlRegeneration\Service\Category\UrlGenerator $categoryUrlGenerator,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepository
    )
    {
        parent::__construct($context);
        $this->categoryUrlGenerator = $categoryUrlGenerator;
        $this->categoryRepository = $categoryRepository;
    }

    public function execute()
    {
        $categoryId = $this->_request->getParam('category_id');
        $withSubcategories = (bool)$this->_request->getParam('with_subcategories');

        $this->categoryUrlGenerator->regenerate($categoryId, $withSubcategories);

        $this->messageManager->addSuccessMessage(__('URLs were regenerated successfully'));

        return $this->_redirect('catalog/category/edit', ['id' => $categoryId]);
    }
}
