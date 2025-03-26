<?php

namespace App\Command;

use App\Entity\Account;
use App\Repository\AccountRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'remove-user',
    description: 'Supprime un utilisateur',
)]
class RemoveUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $emi, private readonly AccountRepository $accountRepository, )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('userId', InputArgument::REQUIRED, "Id de l'utilisateur à supprimer")
            ->addOption(
                'confirm',
                'confirm',
                InputOption::VALUE_OPTIONAL,
                'Forcer la suppression sans confirmation',
                null
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $userId = $input->getArgument('userId');
        $account = $this->accountRepository->findOneBy(["id" => $userId]);

        if (!empty($account)) {

            $optionValue = $input->getOption('confirm');

            printf($optionValue);
            if ($optionValue === null) {
                $optionValue = $io->ask('Êtes-vous sûr de supprimer cet utilisateur ? (y/n)', 'n');
            } else {
                $optionValue === "y";
            }

            switch ($optionValue) {
                case "y":
                    $io->success("Supression de l'utilisateur" . $account->getPseudonym() . " !");

                    $this->emi->remove($account);
                    $this->emi->flush();
                    return Command::SUCCESS;

                case "n":
                    $io->caution("Annulation !");
                    break;

                default:
                    $io->caution("Confirmation absente ou mal comprise (y/n)... Annulation !");
                    break;
            }

            return Command::INVALID;
        } else {
            $io->error("Utilisateur (" . $userId . ") non trouvé...");
            return Command::FAILURE;
        }




    }
}
