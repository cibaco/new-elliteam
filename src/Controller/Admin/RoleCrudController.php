<?php

namespace App\Controller\Admin;

use App\Entity\Role;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

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
            ->setPageTitle('index', 'Gestion des rôles')
            ->setPageTitle('new', 'Créer un rôle')
            ->setPageTitle('edit', 'Modifier un rôle')
            ->setSearchFields(['name', 'description'])
            ->setDefaultSort(['name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->hideOnForm();

        yield TextField::new('name', 'Nom du rôle')
            ->setHelp('Format: ROLE_XXX (ex: ROLE_MANAGER)')
            ->setRequired(true);

        yield TextareaField::new('description', 'Description')
            ->setHelp('Description du rôle et de ses permissions')
            ->setRequired(false);

        yield AssociationField::new('users', 'Utilisateurs')
            ->hideOnForm()
            ->formatValue(function ($value, $entity) {
                return $entity->getUsers()->count() . ' utilisateur(s)';
            });

        yield DateTimeField::new('createdAt', 'Créé le')
            ->hideOnForm()
            ->setFormat('dd/MM/yyyy HH:mm');
    }
}