<?php

declare(strict_types=1);

namespace ControlBit\ResetCode\Command;

use ControlBit\ResetCode\Service\ResetCodeManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class ClearResetCodesCommand extends Command
{
    public const EXPIRED = 'EXPIRED';
    public const ALL     = 'ALL';

    /**
     * @param  iterable<ResetCodeManager>  $resetCodeManagers
     */
    public function __construct(private readonly iterable $resetCodeManagers)
    {
        parent::__construct('reset-code:clear');
    }

    protected function configure(): void
    {
        $this->addArgument('which', InputArgument::OPTIONAL, 'Which? (EXPIRED as default, All)', self::EXPIRED);
        $this->setDescription('Clear reset code with all available reset code managers.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);

        /** @var string $which */
        $which = $input->getArgument('which') ?? self::EXPIRED;

        foreach ($this->resetCodeManagers as $resetCodeManager) {
            switch ($which) {
                case self::ALL:
                    $resetCodeManager->clearAll();
                    break;
                case self::EXPIRED:
                    $resetCodeManager->clearExpired();
                    break;
            }
        }

        $io->success(\sprintf('Clearing codes using mode: %s, successfully completed', $which));

        return Command::SUCCESS;
    }
}
