<?php

namespace App\Controller\Admin;

use App\Entity\CompanyOffer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TelephoneField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;

class CompanyOfferCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CompanyOffer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Offre Entreprise')
            ->setEntityLabelInPlural('Offres Entreprises')
            ->setPageTitle('index', 'Gestion des Offres Entreprises')
            ->setPageTitle('new', 'Créer une nouvelle offre')
            ->setPageTitle('edit', 'Modifier l\'offre #%entity_id%')
            ->setPageTitle('detail', 'Détails de l\'offre #%entity_id%')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(20)
            ->setSearchFields(['name', 'company', 'email', 'phone', 'position', 'description'])
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->showEntityActionsInlined()
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvelle offre')->setIcon('fa fa-plus');
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
            ->remove(Crud::PAGE_DETAIL, Action::EDIT)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN')
            ;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('needType', 'Type de besoin')
                ->setChoices([
                    'CDI' => 'cdi',
                    'Prestation' => 'prestation',
                ]))
            ->add(ChoiceFilter::new('status', 'Statut')
                ->setChoices([
                    'En attente' => 'pending',
                    'En cours de traitement' => 'processing',
                    'Terminée' => 'completed',
                    'Rejetée' => 'rejected',
                ]))
            ->add(TextFilter::new('company', 'Entreprise'))
            ->add(TextFilter::new('position', 'Poste/Mission'))
            ->add(DateTimeFilter::new('createdAt', 'Date de création'))
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        // Champs communs
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield DateTimeField::new('createdAt', 'Date de dépôt')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield TextField::new('name', 'Nom / Prénom')
            ->setColumns(6)
            ->setHelp('Nom et prénom du contact');

        yield TextField::new('company', 'Entreprise')
            ->setColumns(6)
            ->setHelp('Nom de l\'entreprise');

        yield EmailField::new('email', 'Email')
            ->setColumns(6)
            ->setHelp('Email de contact');

        yield TelephoneField::new('phone', 'Téléphone')
            ->setColumns(6)
            ->setHelp('Numéro de téléphone au format français');

        yield ChoiceField::new('needType', 'Type de besoin')
            ->setChoices([
                'CDI' => 'cdi',
                'Prestation' => 'prestation',
            ])
            ->setColumns(6)
            ->renderAsBadges([
                'cdi' => 'success',
                'prestation' => 'info',
            ])
            ->setHelp('Type de besoin : CDI ou Prestation');

        yield ChoiceField::new('status', 'Statut')
            ->setChoices([
                'En attente' => 'pending',
                'En cours de traitement' => 'processing',
                'Terminée' => 'completed',
                'Rejetée' => 'rejected',
            ])
            ->setColumns(6)
            ->renderAsBadges([
                'pending' => 'warning',
                'processing' => 'primary',
                'completed' => 'success',
                'rejected' => 'danger',
            ])
            ->setHelp('Statut actuel de l\'offre');

        yield TextField::new('position', 'Intitulé du poste / mission')
            ->setColumns(12)
            ->setHelp('Titre du poste ou de la mission recherchée')
            ->hideOnIndex();

        yield TextareaField::new('description', 'Description')
            ->setColumns(12)
            ->setHelp('Description détaillée du besoin, compétences recherchées, etc.')
            ->hideOnIndex()
            ->setMaxLength(2000);

        // Champ pour l'affichage de la description tronquée sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextareaField::new('description', 'Description')
                ->setMaxLength(100)
                ->renderAsHtml();
        }

        yield TextField::new('attachmentFilename', 'Fichier joint')
            ->setColumns(12)
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                if ($value) {
                    return sprintf(
                        '<a href="/uploads/company_offers/%s" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fa fa-download"></i> Télécharger %s
                        </a>',
                        $value,
                        $value
                    );
                }
                return '<span class="badge badge-secondary">Aucun fichier</span>';
            });

        // Badge pour indiquer la présence d'un fichier sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('attachmentFilename', '📎')
                ->formatValue(function ($value) {
                    return $value ? '📎' : '';
                });
        }
    }
}