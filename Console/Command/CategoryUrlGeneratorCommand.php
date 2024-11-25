<?php

namespace MageSuite\UrlRegeneration\Console\Command;

class CategoryUrlGeneratorCommand extends \Symfony\Component\Console\Command\Command
{
    public const CATEGORY_ID_OPTION = 'category_id';
    public const WITH_SUBCATEGORIES_OPTION = 'with_subcategories';
    public const STORE = 'store';

    protected \Magento\Framework\App\State $state;
    protected \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory;
    protected \MageSuite\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \MageSuite\UrlRegeneration\Service\Category\UrlGeneratorFactory $urlGeneratorFactory,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->state = $state;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->urlGeneratorFactory = $urlGeneratorFactory;
    }

    protected function configure(): void
    {
        $this->setName("catalog:category:url-regeneration");
        $this->setDescription(
            "Regenerates URL rewrites for all categories, to use it for specific category use -c parameter.
            To regenerate single category with all subcategories specify category id and use -w 1 parameter. Example -c 1 -w 1"
        );
        $this->setDefinition([
            new \Symfony\Component\Console\Input\InputOption(
                self::CATEGORY_ID_OPTION,
                "-c",
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Regenerate URL rewrites for category ID"
            ),
            new \Symfony\Component\Console\Input\InputOption(
                self::WITH_SUBCATEGORIES_OPTION,
                "-w",
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Use category subcategories"
            ),
            new \Symfony\Component\Console\Input\InputOption(
                self::STORE,
                "-s",
                \Symfony\Component\Console\Input\InputOption::VALUE_IS_ARRAY | \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Regenerate URL rewrites for specific store IDs. Usage: --store=1 --store=2 --store=3"
            )
        ]);

        parent::configure();
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
    {
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
            $withSubcategories = $this->isWithSubcategories($input);
        } elseif ($categoryId && !$this->validateCategoryId($categoryId, $categoryIds)) {
            $output->writeln(sprintf("Category with ID %s does not exists.", $categoryId));
            $output->writeln("Finish.");

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $storeIds = $this->getStoreIds($input);

        foreach ($categoryIds as $categoryId) {
            $output->writeln(sprintf("Processing URL rewrite for category %s", $categoryId));
            $urlGenerator->regenerate((int)$categoryId, $withSubcategories, $storeIds);
        }

        $output->writeln("Finish.");

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }

    protected function getStoreIds(\Symfony\Component\Console\Input\InputInterface $input)
    {
        return ($storeIds = $input->getOption(self::STORE)) ? $storeIds : null;
    }

    protected function isWithSubcategories(\Symfony\Component\Console\Input\InputInterface $input): bool
    {
        $withSubcategoriesOption = $input->getOption(self::WITH_SUBCATEGORIES_OPTION);
        if (!$withSubcategoriesOption) {
            return false;
        }

        return true;
    }

    protected function getCategoryIds(): array
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

    protected function validateCategoryId(int $categoryId, array $categoryIds): bool
    {
        return in_array($categoryId, $categoryIds);
    }
}
