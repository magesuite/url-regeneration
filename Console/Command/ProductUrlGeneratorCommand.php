<?php

namespace MageSuite\UrlRegeneration\Console\Command;

/**
 * Class ProductUrlGeneratorCommand
 * @package MageSuite\UrlRegeneration\Console\Command
 */
class ProductUrlGeneratorCommand
    extends \Symfony\Component\Console\Command\Command
{

    const PRODUCT_IDS_OPTION = 'product_ids';

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory
     */
    protected $urlGeneratorFactory;

    /**
     * ProductUrlGeneratorCommand constructor.
     * @param \Magento\Framework\App\State $state
     * @param \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory,
        $name = null
    )
    {
        $this->state = $state;
        $this->urlGeneratorFactory = $urlGeneratorFactory;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName("catalog:product:url-regeneration");
        $this->setDescription("Regenerates URL rewrites for all products. If you need to regenerate one or more you can pass -p parameter. For example -p 1,2,3");
        $this->setDefinition([
            new \Symfony\Component\Console\Input\InputOption(
                self::PRODUCT_IDS_OPTION, "-p",
                \Symfony\Component\Console\Input\InputOption::VALUE_OPTIONAL,
                "Regenerate URL rewrites for product ids array"
            )
        ]);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(
        \Symfony\Component\Console\Input\InputInterface $input,
        \Symfony\Component\Console\Output\OutputInterface $output
    )
    {
        try {
            $this->state->getAreaCode();
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);
        }

        $output->writeln("Starting products URL rewrites regeneration ...");

        /** @var \MageSuite\UrlRegeneration\Service\Product\UrlGenerator $urlGenerator */
        $urlGenerator = $this->urlGeneratorFactory->create();
        $urlGenerator->regenerate($this->prepareProductIds($input));

        $output->writeln("Finish.");
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @return array
     */
    protected function prepareProductIds(\Symfony\Component\Console\Input\InputInterface $input)
    {
        $productIdsOption = $input->getOption(self::PRODUCT_IDS_OPTION);
        if (!$productIdsOption) {
            return [];
        }

        return explode(",", $productIdsOption);
    }

}
