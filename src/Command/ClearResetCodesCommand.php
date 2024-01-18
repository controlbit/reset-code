<?php

declare(strict_types=1);

namespace Choks\ResetCode\Command;

use Choks\ResetCode\Service\ResetCodeManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

// TODO: Add this command to bundle, not registred currently
#[AsCommand(
    name       : 'reset-code:clear',
    description: 'Clears reset-codes',
)]
final class ClearResetCodesCommand extends Command
{
    public const EXPIRED = 'EXPIRED';
    public const ALL     = 'ALL';

    public function __construct(private readonly ResetCodeManager $resetCodeManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('which', InputArgument::OPTIONAL, 'Which? (EXPIRED, All)', null, self::EXPIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io    = new SymfonyStyle($input, $output);
        $which = $input->getArgument('which') ?? self::EXPIRED;

        switch ($which) {
            case self::ALL:
                $this->resetCodeManager->clearAll();
                break;
            case self::EXPIRED:
                $this->resetCodeManager->clearExpired();
                break;
        }

        $io->success(\sprintf('Clearing codes using mode: %s, successfully completed', $which));

        return Command::SUCCESS;
    }
}
