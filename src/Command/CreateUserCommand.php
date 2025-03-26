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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'create-user',
    description: 'Create an user',
)]
class CreateUserCommand extends Command
{
    public function __construct(private readonly EntityManagerInterface $emi, private readonly AccountRepository $accountRepository, private readonly UserPasswordHasherInterface $hash)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('pseudo', InputArgument::REQUIRED, 'Pseudonym')
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('pswd', InputArgument::REQUIRED, 'Password')
            ->addArgument('role', InputArgument::OPTIONAL, 'Role')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $pseudo = $input->getArgument('pseudo');
        $email = $input->getArgument('email');
        $pswd = $input->getArgument('pswd');
        $role = $input->getArgument('role');

        if ($pseudo) {
            $io->note(sprintf('Pseudonyme, check! : %s', $pseudo));
        }
        if ($email) {
            $io->note(sprintf('Email, check! : %s', $email));
        }
        if ($pswd) {
            $io->note(sprintf('Mot de passe, check! : %s', $pswd));
        }
        if ($role) {
            $io->note(sprintf('Role, check! : %s', $role));
        }


        try {
            if (!$this->accountRepository->findOneBy(["email" => $email])) {
                $account = new Account();
                $account->setPseudonym($pseudo);
                $account->setEmail($email);
                $account->setPassword($this->hash->hashPassword($account, $pswd));

                if ($role) {
                    $account->setRoles([$role]);
                } else {
                    $io->info("Aucun rôle trouvé en argument, application du rôle de base");
                    $account->setRoles(["ROLE_USER"]);
                }

                $this->emi->persist($account);
                $this->emi->flush();

                $io->success('Vous avez crée le compte de ' . $account->getPseudonym() . ' !');

                return Command::SUCCESS;
            } else {
                throw new \Exception("Le compte existe déja");
            }
        } catch (\Exception $e) {
            $io->error('Il y a eu une erreur : ' . $e->getMessage());
            return Command::FAILURE;
        }


    }
}
