<?php

namespace MageSuite\UrlRegeneration\Console\Command;

/**
 * @package MageSuite\UrlRegeneration\Console\Command
 */
class MissingProductUrlGeneratorCommand extends \Symfony\Component\Console\Command\Command
{
    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * @var \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory
     */
    protected $urlGeneratorFactory;

    /**
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
        $this->setName("catalog:product:missing-url-generation");
        $this->setDescription("Generates missing URL rewrites for products.");
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

        $output->writeln("Starting products URL rewrites generation ...");

        /** @var \MageSuite\UrlRegeneration\Service\Product\UrlGenerator $urlGenerator */
        $urlGenerator = $this->urlGeneratorFactory->create();
        $urlGenerator->regenerateMissing();

        $output->writeln("Finish.");
    }

}
