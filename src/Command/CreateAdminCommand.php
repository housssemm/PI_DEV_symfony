<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateAdminCommand extends Command
{
    protected static $defaultName = 'app:make-admin';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        parent::__construct();
        $this->em = $em;
    }

    protected function configure()
    {
        $this->setDescription('Met à jour un utilisateur en admin');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => 'farahbenyedder111@gmail.com']);

        if (!$user) {
            $io->error('Utilisateur non trouvé');
            return Command::FAILURE;
        }

        $user->setRoles(['ROLE_ADMIN']);
        $user->setCertificatValide(true);
        
        $this->em->flush();

        $io->success('Utilisateur mis à jour en admin avec succès!');

        return Command::SUCCESS;
    }
}
