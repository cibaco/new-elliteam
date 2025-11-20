<?php

namespace App\Command;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(
    name: 'app:create-user-with-roles',
    description: 'Créer un nouvel utilisateur avec gestion avancée des rôles',
)]
class CreateAdminCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email de l\'utilisateur')
            ->addOption('admin', 'a', InputOption::VALUE_NONE, 'Créer un administrateur')
            ->addOption('password', 'p', InputOption::VALUE_REQUIRED, 'Mot de passe')
            ->addOption('firstname', null, InputOption::VALUE_REQUIRED, 'Prénom')
            ->addOption('lastname', null, InputOption::VALUE_REQUIRED, 'Nom')
            ->addOption('phone', null, InputOption::VALUE_REQUIRED, 'Téléphone')
            ->addOption('role', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Rôles à attribuer (peut être utilisé plusieurs fois)')
            ->addOption('inactive', null, InputOption::VALUE_NONE, 'Créer un utilisateur inactif');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $io->title('Création d\'un nouvel utilisateur');

        // Récupération de l'email
        $email = $input->getArgument('email');
        if (!$email) {
            $question = new Question('Email de l\'utilisateur: ');
            $question->setValidator(function ($answer) {
                if (!filter_var($answer, FILTER_VALIDATE_EMAIL)) {
                    throw new \RuntimeException('L\'email n\'est pas valide');
                }
                return $answer;
            });
            $email = $io->askQuestion($question);
        }

        // Vérifier si l'utilisateur existe déjà
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        if ($existingUser) {
            $io->error('Un utilisateur avec cet email existe déjà !');
            return Command::FAILURE;
        }

        // Récupération du mot de passe
        $password = $input->getOption('password');
        if (!$password) {
            $question = new Question('Mot de passe: ');
            $question->setHidden(true);
            $question->setHiddenFallback(false);
            $question->setValidator(function ($answer) {
                if (strlen($answer) < 6) {
                    throw new \RuntimeException('Le mot de passe doit faire au moins 6 caractères');
                }
                return $answer;
            });
            $password = $io->askQuestion($question);
        }

        // Création de l'utilisateur
        $user = new User();
        $user->setEmail($email);

        // Hash du mot de passe
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);

        // Prénom et nom
        if ($firstName = $input->getOption('firstname')) {
            $user->setFirstName($firstName);
        }
        if ($lastName = $input->getOption('lastname')) {
            $user->setLastName($lastName);
        }
        if ($phone = $input->getOption('phone')) {
            $user->setPhone($phone);
        }

        // Statut actif/inactif
        $user->setIsActive(!$input->getOption('inactive'));
        $user->setIsVerified(true);

        // Gestion des rôles
        $rolesArray = [];

        // Option --admin
        if ($input->getOption('admin')) {
            $rolesArray[] = 'ROLE_ADMIN';
        }

        // Options --role
        $roleOptions = $input->getOption('role');
        if (!empty($roleOptions)) {
            $rolesArray = array_merge($rolesArray, $roleOptions);
        }

        // Si aucun rôle spécifié, demander interactivement
        if (empty($rolesArray) && $input->isInteractive()) {
            $availableRoles = $this->entityManager->getRepository(Role::class)->findAll();

            if (!empty($availableRoles)) {
                $roleChoices = array_map(fn($r) => $r->getName(), $availableRoles);
                $question = new ChoiceQuestion(
                    'Sélectionnez les rôles (séparés par des virgules, laisser vide pour ROLE_USER uniquement)',
                    $roleChoices,
                    null
                );
                $question->setMultiselect(true);

                $selectedRoles = $io->askQuestion($question);
                if (!empty($selectedRoles)) {
                    $rolesArray = $selectedRoles;
                }
            }
        }

        // Attribuer les rôles (format string JSON)
        if (!empty($rolesArray)) {
            $user->setRoles(array_unique($rolesArray));
        }

        // Attribuer les rôles (format entité Role) si disponibles
        foreach ($rolesArray as $roleName) {
            $roleEntity = $this->entityManager->getRepository(Role::class)->findOneBy(['name' => $roleName]);
            if ($roleEntity) {
                $user->addUserRole($roleEntity);
            }
        }

        // Sauvegarde
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Affichage du résumé
        $io->success('Utilisateur créé avec succès !');

        $io->table(
            ['Propriété', 'Valeur'],
            [
                ['Email', $user->getEmail()],
                ['Nom complet', $user->getFullName() ?: 'Non défini'],
                ['Téléphone', $user->getPhone() ?: 'Non défini'],
                ['Rôles', $user->getRolesAsString()],
                ['Actif', $user->isActive() ? 'Oui' : 'Non'],
                ['Vérifié', $user->isVerified() ? 'Oui' : 'Non'],
                ['Créé le', $user->getCreatedAt()->format('Y-m-d H:i:s')],
            ]
        );

        return Command::SUCCESS;
    }
}