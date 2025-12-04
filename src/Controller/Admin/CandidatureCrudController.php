<?php

namespace App\Controller\Admin;

use App\Entity\Candidature;
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

class CandidatureCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Candidature::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Candidature')
            ->setEntityLabelInPlural('Candidatures')
            ->setPageTitle('index', 'Gestion des Candidatures')
            ->setPageTitle('new', 'Nouvelle candidature')
            ->setPageTitle('edit', 'Modifier la candidature #%entity_id%')
            ->setPageTitle('detail', 'Détails de la candidature #%entity_id%')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setPaginatorPageSize(25)
            ->setSearchFields(['nomPrenom', 'email', 'telephone', 'posteRecherche'])
            ->setDateTimeFormat('dd/MM/yyyy HH:mm')
            ->showEntityActionsInlined();
    }

    public function configureActions(Actions $actions): Actions
    {
        $downloadCv = Action::new('downloadCv', 'Télécharger CV', 'fa fa-download')
            ->linkToUrl(function (Candidature $candidature): string {
                return '/uploads/' . $candidature->getCv();
            })
            ->setHtmlAttributes(['target' => '_blank'])
            ->displayAsLink();

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_DETAIL, $downloadCv)
            ->update(Crud::PAGE_INDEX, Action::NEW, function (Action $action) {
                return $action->setLabel('Nouvelle candidature')->setIcon('fa fa-plus');
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
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        // Utiliser des filtres automatiques simples qui ne causent pas d'erreur
        return $filters
            ->add('statut')
            ->add('disponibilite')
            ->add('nomPrenom')
            ->add('email')
            ->add('posteRecherche')
            ->add('createdAt');
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id', 'ID')
            ->onlyOnIndex();

        yield DateTimeField::new('createdAt', 'Candidature déposée le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->hideOnForm();

        yield DateTimeField::new('updatedAt', 'Modifiée le')
            ->setFormat('dd/MM/yyyy HH:mm')
            ->onlyOnDetail();

        yield TextField::new('nomPrenom', 'Nom / Prénom')
            ->setColumns(6)
            ->setHelp('Nom et prénom du candidat');

        yield EmailField::new('email', 'Email')
            ->setColumns(6)
            ->setHelp('Email de contact');

        yield TelephoneField::new('telephone', 'Téléphone')
            ->setColumns(6)
            ->setHelp('Numéro de téléphone');

        yield TextField::new('posteRecherche', 'Poste recherché')
            ->setColumns(6)
            ->setHelp('Poste ou type de mission recherché');

        yield TextField::new('pretentionSalariale', 'Prétention salariale')
            ->setColumns(6)
            ->setHelp('Prétention salariale annuelle ou TJM')
            ->hideOnIndex();

        yield ChoiceField::new('disponibilite', 'Disponibilité')
            ->setChoices([
                'Immédiatement' => 'immediatement',
                'Dans 1 mois' => '1mois',
                'Dans 2 mois' => '2mois',
                'Dans 3 mois' => '3mois',
                'Autre' => 'autre',
            ])
            ->setColumns(6)
            ->renderAsBadges([
                'immediatement' => 'success',
                '1mois' => 'info',
                '2mois' => 'warning',
                '3mois' => 'warning',
                'autre' => 'secondary',
            ])
            ->setHelp('Disponibilité pour démarrer');

        // Affichage du label de disponibilité sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('disponibiliteLabel', 'Disponibilité')
                ->formatValue(function ($value, $entity) {
                    $badges = [
                        'immediatement' => 'success',
                        '1mois' => 'info',
                        '2mois' => 'warning',
                        '3mois' => 'warning',
                        'autre' => 'secondary',
                    ];
                    $color = $badges[$entity->getDisponibilite()] ?? 'secondary';
                    return sprintf(
                        '<span class="badge bg-%s">%s</span>',
                        $color,
                        $entity->getDisponibiliteLabel()
                    );
                });
        }

        yield ChoiceField::new('statut', 'Statut')
            ->setChoices([
                'Nouvelle' => 'nouvelle',
                'En cours' => 'en_cours',
                'Retenue' => 'retenue',
                'Refusée' => 'refusee',
                'Archivée' => 'archivee',
            ])
            ->setColumns(6)
            ->renderAsBadges([
                'nouvelle' => 'warning',
                'en_cours' => 'primary',
                'retenue' => 'success',
                'refusee' => 'danger',
                'archivee' => 'secondary',
            ])
            ->setHelp('Statut de la candidature');

        yield TextareaField::new('message', 'Message du candidat')
            ->setColumns(12)
            ->setHelp('Message accompagnant la candidature')
            ->hideOnIndex()
            ->setMaxLength(1000);

        // Champ CV pour l'affichage
        yield TextField::new('cv', 'CV')
            ->setColumns(12)
            ->onlyOnDetail()
            ->formatValue(function ($value, $entity) {
                if ($value) {
                    return sprintf(
                        '<a href="/uploads/%s" target="_blank" class="btn btn-sm btn-primary">
                            <i class="fa fa-download"></i> Télécharger le CV : %s
                        </a>',
                        $value,
                        $value
                    );
                }
                return '<span class="badge badge-secondary">Aucun CV</span>';
            });

        // Badge pour indiquer la présence d'un CV sur l'index
        if ($pageName === Crud::PAGE_INDEX) {
            yield TextField::new('cv', '📄 CV')
                ->formatValue(function ($value) {
                    return $value ? '<span class="badge bg-success">✓</span>' : '<span class="badge bg-danger">✗</span>';
                });
        }
    }
}