<?php

namespace MageSuite\UrlRegeneration\Console\Command;

class MissingProductUrlGeneratorCommand extends \Symfony\Component\Console\Command\Command
{
    protected \Magento\Framework\App\State $state;
    protected \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory;

    public function __construct(
        \Magento\Framework\App\State $state,
        \MageSuite\UrlRegeneration\Service\Product\UrlGeneratorFactory $urlGeneratorFactory,
        ?string $name = null
    ) {
        parent::__construct($name);

        $this->state = $state;
        $this->urlGeneratorFactory = $urlGeneratorFactory;
    }

    protected function configure(): void
    {
        $this->setName("catalog:product:missing-url-generation");
        $this->setDescription("Generates missing URL rewrites for products.");

        parent::configure();
    }

    protected function execute(\Symfony\Component\Console\Input\InputInterface $input, \Symfony\Component\Console\Output\OutputInterface $output): int
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

        return \Symfony\Component\Console\Command\Command::SUCCESS;
    }
}
