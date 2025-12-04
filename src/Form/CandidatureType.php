<?php

namespace App\Form;

use App\Entity\Candidature;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CandidatureType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nomPrenom', TextType::class, [
                'label' => 'Nom et Prénom',
                'attr' => [
                    'placeholder' => 'Entrez votre nom et prénom',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'votre.email@exemple.com',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('telephone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => '06 12 34 56 78',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('posteRecherche', TextType::class, [
                'label' => 'Poste recherché',
                'attr' => [
                    'placeholder' => 'Ex: Développeur Full Stack',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('pretentionSalariale', TextType::class, [
                'label' => 'Prétention salariale',
                'attr' => [
                    'placeholder' => 'Ex: 40K - 50K €',
                    'class' => 'form-control'
                ],
                'required' => true,
            ])
            ->add('disponibilite', ChoiceType::class, [
                'label' => 'Disponibilité',
                'choices' => [
                    'Immédiatement' => 'immediatement',
                    'Dans 1 mois' => '1mois',
                    'Dans 2 mois' => '2mois',
                    'Dans 3 mois' => '3mois',
                    'Autre (préciser dans le message)' => 'autre',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'placeholder' => 'Sélectionnez votre disponibilité',
                'required' => true,
            ])
            ->add('cvFile', FileType::class, [
                'label' => 'CV (PDF uniquement)',
                'mapped' => false,
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'accept' => '.pdf'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/x-pdf',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un fichier PDF valide',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                    ])
                ],
                'help' => 'Format PDF uniquement, taille max 5Mo'
            ])
            ->add('message', TextareaType::class, [
                'label' => 'Message (optionnel)',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Parlez-nous de votre motivation...',
                    'class' => 'form-control',
                    'rows' => 5
                ],
            ])
            ->add('submit', SubmitType::class, [
                'label' => 'Envoyer ma candidature',
                'attr' => [
                    'class' => 'btn btn-primary btn-lg w-100'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Candidature::class,
        ]);
    }
}