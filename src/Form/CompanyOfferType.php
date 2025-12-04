<?php

namespace App\Form;

use App\Entity\CompanyOffer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class CompanyOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom / Prénom',
                'attr' => [
                    'placeholder' => 'Votre nom et prénom',
                    'class' => 'form-input'
                ],
                'required' => true
            ])
            ->add('company', TextType::class, [
                'label' => 'Entreprise',
                'attr' => [
                    'placeholder' => 'Nom de votre entreprise',
                    'class' => 'form-input'
                ],
                'required' => true
            ])
            ->add('email', EmailType::class, [
                'label' => 'Email',
                'attr' => [
                    'placeholder' => 'votre.email@exemple.com',
                    'class' => 'form-input'
                ],
                'required' => true
            ])
            ->add('phone', TelType::class, [
                'label' => 'Téléphone',
                'attr' => [
                    'placeholder' => '06 12 34 56 78',
                    'class' => 'form-input'
                ],
                'required' => true
            ])
            ->add('needType', ChoiceType::class, [
                'label' => 'Type de besoin',
                'choices' => [
                    '-- Sélectionnez un type --' => '',
                    'CDI' => 'cdi',
                    'Prestation' => 'prestation',
                ],
                'attr' => [
                    'class' => 'form-select'
                ],
                'required' => true,
                'placeholder' => false
            ])
            ->add('position', TextType::class, [
                'label' => 'Intitulé du poste / mission',
                'attr' => [
                    'placeholder' => 'Ex: Développeur Full Stack, Chef de projet digital...',
                    'class' => 'form-input'
                ],
                'required' => true
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description rapide',
                'attr' => [
                    'placeholder' => 'Décrivez brièvement les compétences recherchées, les missions principales, le contexte du projet...',
                    'class' => 'form-textarea',
                    'rows' => 6
                ],
                'required' => true
            ])
            ->add('attachment', FileType::class, [
                'label' => 'Pièce jointe (optionnelle)',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'class' => 'file-input',
                    'accept' => '.pdf,.doc,.docx'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                        ],
                        'mimeTypesMessage' => 'Veuillez télécharger un document valide (PDF, DOC, DOCX)',
                        'maxSizeMessage' => 'Le fichier est trop volumineux ({{ size }} {{ suffix }}). La taille maximale autorisée est {{ limit }} {{ suffix }}.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CompanyOffer::class,
        ]);
    }
}