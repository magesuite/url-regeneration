<?php

namespace Creativestyle\UrlRegeneration\Console\Command;

/**
 * Class CategoryUrlGeneratorCommand
 * @package Creativestyle\UrlRegeneration\Console\Command
 */
class CategoryUrlGeneratorCommand
    extends \Symfony\Component\Console\Command\Command
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Creativestyle\UrlRegeneration\Service\Category\UrlGeneratorFactory
     */
    protected $urlGeneratorFactory;

    /**
     * CategoryUrlGeneratorCommand constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Creativestyle\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Creativestyle\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory,
        $name = null
    )
    {
        $this->state = $state;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlGeneratorFactory = $urlGeneratorFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("catalog:category:url-regeneration");
        $this->setDescription("Regenerates URL rewrites for all categories.");
        parent::configure();
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return bool|int|null
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    ) {
        try {
            $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }

        $output->writeln("Starting categories URL revrites regeneration ...");
        $categoryIds = $this->getCategoryIds();
        if (!$categoryIds) {
            $output->writeln("There are no categories to process!");
            $output->writeln("Finish.");

            return false;
        }

        /** @var \Creativestyle\UrlRegeneration\Service\Category\UrlGenerator $urlGenerator */
        $urlGenerator = $this->urlGeneratorFactory->create();

        foreach ($categoryIds as $categoryId) {
            $output->writeln(sprintf("Processing URL rewrite for ctegory %s", $categoryId));
            $urlGenerator->regenerate($categoryId);
        }

        $output->writeln("Finish.");

        return true;
    }

    /**
     * @return array
     */
    protected function getCategoryIds()
    {
        $result = [];
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $result[] = $category->getId();
        }

        return $result;
    }

}
