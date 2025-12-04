<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class RoleCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Role::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Rôle')
            ->setEntityLabelInPlural('Rôles')
            ->setPageTitle('index', 'Gestion des Rôles')
            ->setPageTitle('new', 'Créer un nouveau rôle')
            ->setPageTitle('edit', 'Modifier le rôle "%entity_as_string%"')
            ->setPageTitle('detail', 'Détails du rôle "%entity_as_string%"')
            ->setDefaultSort(['name' => 'ASC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['name', 'description'])
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->showEntityActionsInlined()
            ->setHelp('index', 'Les rôles permettent de définir des permissions personnalisées pour les utilisateurs.');
    }

    public function configureActions(Actions $actions): Actions
    {
        $manageUsers = Action::new('manageUsers', 'Gérer les utilisateurs', 'fa fa-users')
            ->linkToCrudAction('manageUsers')
            ->displayAsLink()
            ->setCssClass('btn btn-info');

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $manageUsers)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouveau rôle')->setIcon('fa fa-plus');
            })
            ->update(Crud::PAGE_INDEX, Action::EDIT, function (Action $action) {
                return $action->setLabel('Modifier')->setIcon('fa fa-edit');
            })
            ->update(Crud::PAGE_INDEX, Action::DETAIL, function (Action $action) {
                return $action->setLabel('Voir')->setIcon('fa fa-eye');
            })
            ->update(Crud::PAGE_INDEX, Action::DELETE, function (Action $action) {
                return $action->setLabel('Supprimer')->setIcon('fa fa-trash');
            })
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ->setPermission(Action::NEW, 'ROLE_ADMIN')
            ->setPermission(Action::EDIT, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(TextFilter::new('name', 'Nom du rôle'))
            ->add(DateTimeFilter::new('createdAt', 'Date de création'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield TextField::new('name', 'Nom du rôle')
            ->setColumns(6)
            ->setRequired(true)
            ->setHelp('Nom unique du rôle (ex: ROLE_MANAGER, ROLE_RECRUITER)')
            ->setFormTypeOption('attr', ['placeholder' => 'ROLE_...']);

        yield TextareaField::new('description', 'Description')
            ->setColumns(6)
            ->setHelp('Description du rôle et de ses permissions')
            ->setMaxLength(500)
            ->hideOnIndex();

        // Nombre d'utilisateurs ayant ce rôle
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            yield TextField::new('usersCount', 'Utilisateurs')
                ->formatValue(function ($value, $entity) {
                    $count = $entity->getUsers()->count();
                    $color = $count > 0 ? 'primary' : 'secondary';
                    return sprintf(
                        '<span class="badge bg-%s">%d utilisateur%s</span>',
                        $color,
                        $count,
                        $count > 1 ? 's' : ''
                    );
                });
        }

        yield AssociationField::new('users', 'Utilisateurs associés')
            ->onlyOnDetail()
            ->setTemplatePath('admin/role_users.html.twig');

        yield DateTimeField::new('createdAt', 'Créé le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();
    }

    public function manageUsers(): void
    {
        // Action personnalisée pour gérer les utilisateurs d'un rôle
        // Peut être implémentée selon vos besoins
    }
}