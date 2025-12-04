<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
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
            ->setPageTitle('index', 'Gestion des Utilisateurs')
            ->setPageTitle('new', 'Créer un utilisateur')
            ->setPageTitle('edit', 'Modifier l\'utilisateur #%entity_id%')
            ->setPageTitle('detail', 'Profil de %entity_as_string%')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->setSearchFields(['email', 'firstName', 'lastName', 'phone'])
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $viewProfile = Action::new('viewProfile', 'Voir le profil', 'fa fa-user')
            ->linkToCrudAction(Action::DETAIL);

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $viewProfile)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvel utilisateur')->setIcon('fa fa-user-plus');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier')->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Détails')->setIcon('fa fa-eye');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer')->setIcon('fa fa-trash');
            })
            ->setPermission(Action::DELETE, 'ROLE_SUPER_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('isActive', 'Actif'))
            ->add(BooleanFilter::new('isVerified', 'Vérifié'))
            ->add(TextFilter::new('firstName', 'Prénom'))
            ->add(TextFilter::new('lastName', 'Nom'))
            ->add(DateTimeFilter::new('createdAt', 'Date d\'inscription'))
            ->add(DateTimeFilter::new('lastLoginAt', 'Dernière connexion'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield EmailField::new('email', 'Email')
            ->setColumns(6)
            ->setHelp('Adresse email (identifiant unique)');

        // Champ mot de passe uniquement en création et édition
        if ($pageName === Crud::PAGE_NEW || $pageName === Crud::PAGE_EDIT) {
            yield TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->setColumns(6)
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->setHelp($pageName === Crud::PAGE_EDIT ? 'Laisser vide pour ne pas modifier' : 'Choisissez un mot de passe fort')
                ->onlyOnForms();
        }

        yield TextField::new('firstName', 'Prénom')
            ->setColumns(6);

        yield TextField::new('lastName', 'Nom')
            ->setColumns(6);

        yield TextField::new('fullName', 'Nom complet')
            ->onlyOnIndex();

        yield TelephoneField::new('phone', 'Téléphone')
            ->setColumns(6)
            ->hideOnIndex();

        yield TextareaField::new('address', 'Adresse')
            ->setColumns(12)
            ->hideOnIndex()
            ->setMaxLength(500);

        // Rôles Symfony (JSON)
        yield ChoiceField::new('roles', 'Rôles système')
            ->setChoices([
                'Utilisateur' => 'ROLE_USER',
                'Administrateur' => 'ROLE_ADMIN',
                'Super Admin' => 'ROLE_SUPER_ADMIN',
            ])
            ->allowMultipleChoices()
            ->setColumns(6)
            ->renderAsBadges([
                'ROLE_USER' => 'secondary',
                'ROLE_ADMIN' => 'warning',
                'ROLE_SUPER_ADMIN' => 'danger',
            ])
            ->setHelp('Rôles Symfony pour les permissions')
            ->hideOnIndex();

        // Rôles personnalisés (relation)
        yield AssociationField::new('userRoles', 'Rôles personnalisés')
            ->setColumns(6)
            ->hideOnIndex()
            ->setHelp('Rôles métier personnalisés');

        // Affichage des rôles sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('rolesAsString', 'Rôles')
                ->formatValue(function ($value, $entity) {
                    $badges = [];
                    foreach ($entity->getRoles() as $role) {
                        $color = match($role) {
                            'ROLE_SUPER_ADMIN' => 'danger',
                            'ROLE_ADMIN' => 'warning',
                            default => 'secondary',
                        };
                        if ($role !== 'ROLE_USER' || count($entity->getRoles()) === 1) {
                            $badges[] = sprintf('<span class="badge bg-%s">%s</span>', $color, $role);
                        }
                    }
                    return implode(' ', $badges);
                });
        }

        yield BooleanField::new('isActive', 'Actif')
            ->setColumns(3)
            ->renderAsSwitch(false)
            ->setHelp('Peut se connecter');

        yield BooleanField::new('isVerified', 'Vérifié')
            ->setColumns(3)
            ->renderAsSwitch(false)
            ->setHelp('Email vérifié');

        yield DateTimeField::new('lastLoginAt', 'Dernière connexion')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('createdAt', 'Inscrit le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Modifié le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnDetail();
    }

    public function persistEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            // Hasher le mot de passe si présent
            if ($entityInstance->getPassword()) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $entityInstance->getPassword()
                );
                $entityInstance->setPassword($hashedPassword);
            }
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity($entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            // Hasher le mot de passe seulement s'il a été modifié
            $plainPassword = $entityInstance->getPassword();
            if ($plainPassword && !str_starts_with($plainPassword, '$2y$')) {
                $hashedPassword = $this->passwordHasher->hashPassword(
                    $entityInstance,
                    $plainPassword
                );
                $entityInstance->setPassword($hashedPassword);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}