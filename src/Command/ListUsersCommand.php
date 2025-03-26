<?php

namespace App\Command;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'list-users',
    description: 'Liste des utilisateurs',
)]
class ListUsersCommand extends Command
{
    public function __construct(private readonly AccountRepository $accountRepository)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        printf("=== Liste des utilisateurs ===");

        $accounts = $this->accountRepository->findAll();

        foreach ($accounts as $key => $value) {
            $io->definitionList(
                ["Id : " => $value->getId()],
                ['Pseudonyme' => $value->getPseudonym()],
                ['Email' => $value->getEmail()],
                ['Password' => $value->getPassword()],
                "UUID : " . $value->getUserIdentifier(),
                new TableSeparator(),
            );
        }

        if (count($accounts) > 0) {
            $io->success(count($accounts) . " utilisateurs trouvés !");
        } else {
            $io->warning("Aucun utilisateur trouvé");

        }


        return Command::SUCCESS;
    }
}
