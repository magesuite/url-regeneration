<?php

namespace Creativestyle\UrlRegeneration\Console\Command;

/**
 * Class ProductUrlGeneratorCommand
 * @package Creativestyle\UrlRegeneration\Console\Command
 */
class ProductUrlGeneratorCommand
    extends \Symfony\Component\Console\Command\Command
{

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \Creativestyle\UrlRegeneration\Service\Product\UrlGeneratorFactory
     */
    protected $urlGeneratorFactory;

    /**
     * ProductUrlGeneratorCommand constructor.
     * @param \Magento\Framework\App\State $state
     * @param \Creativestyle\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory
     * @param null $name
     */
    public function __construct(
        \Magento\Framework\App\State $state,
        \Creativestyle\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory,
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
        $this->setDescription("Regenerates URL rewrites for all products.");
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

        /** @var \Creativestyle\UrlRegeneration\Service\Product\UrlGenerator $urlGenerator */
        $urlGenerator = $this->urlGeneratorFactory->create();
        $urlGenerator->regenerate();

        $output->writeln("Finish.");
    }

}
