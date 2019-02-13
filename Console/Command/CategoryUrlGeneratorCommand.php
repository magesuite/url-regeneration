<?php

namespace MageSuite\UrlRegeneration\Console\Command;

/**
 * Class CategoryUrlGeneratorCommand
 * @package MageSuite\UrlRegeneration\Console\Command
 */
class CategoryUrlGeneratorCommand
    extends \Symfony\Component\Console\Command\Command
{

    const CATEGORY_ID_OPTION = 'category_id';
    const WITH_SUBCATEGORIES_OPTION = 'with_subcategories';

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \MageSuite\UrlRegeneration\Service\Category\UrlGeneratorFactory
     */
    protected $urlGeneratorFactory;

    /**
     * CategoryUrlGeneratorCommand constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \MageSuite\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageSuite\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory,
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
        $this->setDescription("Regenerates URL rewrites for all categories, to use it for specific category use -c parameter. To regenerate single category with all subcategories specify category id and use -w 1 parameter. Example -c 1 -w 1");
        $this->setDefinition([
            new \Symfony\Component\Console\Input\InputOption(
                self::CATEGORY_ID_OPTION, "-c",
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Regenerate URL rewrites for category ID"
            ),
            new \Symfony\Component\Console\Input\InputOption(
                self::WITH_SUBCATEGORIES_OPTION, "-w",
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Use category subcategories"
            )
        ]);
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

        $output->writeln("Starting categories URL rewrites regeneration ...");

        /** @var \MageSuite\UrlRegeneration\Service\Category\UrlGenerator $urlGenerator */
        $urlGenerator = $this->urlGeneratorFactory->create();
        /** @var int $categoryId */
        $categoryId = $input->getOption(self::CATEGORY_ID_OPTION);
        /** @var array $categoryIds */
        $categoryIds = $this->getCategoryIds();
        /** @var bool $withSubcategories */
        $withSubcategories = false;

        if ($categoryId && $this->validateCategoryId($categoryId, $categoryIds)) {
            $categoryIds = [];
            $categoryIds[] = $categoryId;
            $withSubcategories = $this->prapreWithSubcategories($input);
        } else if ($categoryId && !$this->validateCategoryId($categoryId, $categoryIds)) {
            $output->writeln(sprintf("Category with ID %s does not exists.", $categoryId));
            $output->writeln("Finish.");

            return false;
        }

        foreach ($categoryIds as $categoryId) {
            $output->writeln(sprintf("Processing URL rewrite for category %s", $categoryId));
            $urlGenerator->regenerate($categoryId, $withSubcategories);
        }

        $output->writeln("Finish.");

        return true;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return bool
     */
    protected function prapreWithSubcategories(\Symfony\Component\Console\Input\InputInterface $input)
    {
        $withSubcategoriesOption = $input->getOption(self::WITH_SUBCATEGORIES_OPTION);
        if (!$withSubcategoriesOption) {
            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    protected function getCategoryIds()
    {
        /** @var array $result */
        $result = [];
        /** @var \Magento\Catalog\Model\ResourceModel\Category\Collection $categoryCollection */
        $categoryCollection = $this->categoryCollectionFactory->create();
        /** @var \Magento\Catalog\Model\Category $category */
        foreach ($categoryCollection as $category) {
            $result[] = $category->getId();
        }

        return $result;
    }

    /**
     * @param int $categoryId
     * @param array $categoryIds
     * @return bool
     */
    protected function validateCategoryId(int $categoryId, array $categoryIds)
    {
        return in_array($categoryId, $categoryIds);
    }

}
