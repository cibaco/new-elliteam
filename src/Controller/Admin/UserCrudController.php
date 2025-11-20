<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
            ->setPageTitle('index', 'Gestion des utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', 'Modifier un utilisateur')
            ->setPageTitle('detail', 'Détails de l\'utilisateur')
            ->setSearchFields(['email', 'firstName', 'lastName', 'phone'])
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setIcon('fa fa-user-plus')->addCssClass('btn btn-success');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setIcon('fa fa-trash');
            });
    }

    public function configureFields(string $pageName): iterable
    {
        // Champs communs
        yield EmailField::new('email', 'Email')
            ->setRequired(true)
            ->setHelp('Adresse email unique pour la connexion');

        yield TextField::new('firstName', 'Prénom')
            ->setRequired(false);

        yield TextField::new('lastName', 'Nom')
            ->setRequired(false);

        yield TextField::new('phone', 'Téléphone')
            ->setRequired(false);

        // Champ mot de passe (uniquement pour création/édition)
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            yield TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laissez vide pour ne pas changer' : 'Au moins 6 caractères')
                ->onlyOnForms();
        }

        // Champs de rôles
        yield ArrayField::new('roles', 'Rôles (JSON)')
            ->setHelp('Rôles au format JSON (ex: ROLE_ADMIN, ROLE_MODERATOR)');

        yield AssociationField::new('userRoles', 'Rôles (Entités)')
            ->setFormTypeOptions([
                'by_reference' => false,
                'multiple' => true,
            ])
            ->setHelp('Sélectionner les rôles depuis la table Role');

        // Champs booléens
        yield BooleanField::new('isActive', 'Actif')
            ->setHelp('Désactiver pour empêcher la connexion');

        yield BooleanField::new('isVerified', 'Vérifié')
            ->setHelp('Email vérifié');

        // Adresse (uniquement en édition complète)
        if ($pageName !== Crud::PAGE_INDEX) {
            yield TextareaField::new('address', 'Adresse')
                ->setRequired(false)
                ->hideOnIndex();
        }

        // Dates (lecture seule)
        yield DateTimeField::new('lastLoginAt', 'Dernière connexion')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->hideOnForm()
            ->hideOnIndex()
            ->setFormat('dd/MM/yyyy HH:mm');

        // Affichage des rôles en lecture seule sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('rolesAsString', 'Rôles')
                ->hideOnForm();
        }
    }

    /**
     * Hash le mot de passe avant la persistance
     */
    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword()) {
            $this->hashPassword($entityInstance);
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * Hash le mot de passe avant la mise à jour (si modifié)
     */
    public function updateEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPassword()) {
            $this->hashPassword($entityInstance);
        }
        parent::updateEntity($entityManager, $entityInstance);
    }

    /**
     * Hash le mot de passe
     */
    private function hashPassword(User $user): void
    {
        $plainPassword = $user->getPassword();
        if ($plainPassword) {
            $hashedPassword = $this->passwordHasher->hashPassword($user, $plainPassword);
            $user->setPassword($hashedPassword);
        }
    }
}