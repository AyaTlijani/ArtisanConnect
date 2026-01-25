<?php

namespace App\Form;

use App\Entity\Atelier;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;

class AtelierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom de l\'atelier',
                'attr' => [
                    'placeholder' => 'Entrez le nom de l\'atelier',
                    'minlength' => 3,
                    'maxlength' => 255,
                    'class' => 'form-control',
                    'help' => 'Le nom doit contenir entre 3 et 255 caractères'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le nom de l\'atelier est obligatoire'
                    ]),
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'attr' => [
                    'placeholder' => 'Décrivez l\'atelier',
                    'rows' => 4,
                    'minlength' => 10,
                    'class' => 'form-control',
                    'help' => 'La description doit contenir au moins 10 caractères'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'La description est obligatoire'
                    ]),
                ],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'attr' => [
                    'placeholder' => 'Entrez le prix',
                    'min' => 0,
                    'step' => '0.01',
                    'class' => 'form-control',
                    'help' => 'Le prix doit être un nombre positif'
                ],
                'constraints' => [
                    new Positive([
                        'message' => 'Le prix doit être positif'
                    ]),
                ],
            ])
            ->add('quantite', NumberType::class, [
                'label' => 'Quantité disponible',
                'attr' => [
                    'placeholder' => 'Entrez la quantité',
                    'min' => 0,
                    'class' => 'form-control',
                    'help' => 'La quantité doit être un nombre positif ou nul'
                ],
                'constraints' => [
                    new PositiveOrZero([
                        'message' => 'La quantité doit être positive ou nulle'
                    ]),
                ],
            ])
            ->add('disponibilite', CheckboxType::class, [
                'label' => 'Disponible',
                'required' => false,
                'attr' => [
                    'class' => 'form-check-input',
                    'help' => 'Cochez cette case si l\'atelier est disponible'
                ],
            ])
            ->add('fournisseur', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'label' => 'Fournisseur',
                'placeholder' => 'Sélectionnez un fournisseur',
                'attr' => [
                    'class' => 'form-select',
                    'help' => 'Sélectionnez le fournisseur de l\'atelier'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotNull([
                        'message' => 'Le fournisseur est obligatoire'
                    ]),
                ],
            ])
            ->add('categorie', TextType::class, [
                'label' => 'Catégorie',
                'attr' => [
                    'placeholder' => 'Entrez la catégorie',
                    'maxlength' => 50,
                    'class' => 'form-control',
                    'help' => 'La catégorie ne peut pas dépasser 50 caractères'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'La catégorie est obligatoire'
                    ]),
                ],
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => 'Image',
                'required' => false,
                'allow_delete' => true,
                'delete_label' => 'Supprimer l\'image',
                'download_uri' => false,
                'attr' => [
                    'accept' => 'image/jpeg,image/png,image/gif',
                    'class' => 'form-control',
                    'help' => 'Formats acceptés : JPEG, PNG, GIF. Taille maximale : 5MB'
                ],
            ])
            ->add('location', ChoiceType::class, [
                'label' => 'Gouvernorat',
                'placeholder' => 'Sélectionnez un gouvernorat',
                'choices' => [
                    'Ariana' => 'Ariana',
                    'Béja' => 'Béja',
                    'Ben Arous' => 'Ben Arous',
                    'Bizerte' => 'Bizerte',
                    'Gabès' => 'Gabès',
                    'Gafsa' => 'Gafsa',
                    'Jendouba' => 'Jendouba',
                    'Kairouan' => 'Kairouan',
                    'Kasserine' => 'Kasserine',
                    'Kébili' => 'Kébili',
                    'Le Kef' => 'Le Kef',
                    'Mahdia' => 'Mahdia',
                    'La Manouba' => 'La Manouba',
                    'Médenine' => 'Médenine',
                    'Monastir' => 'Monastir',
                    'Nabeul' => 'Nabeul',
                    'Sfax' => 'Sfax',
                    'Sidi Bouzid' => 'Sidi Bouzid',
                    'Siliana' => 'Siliana',
                    'Sousse' => 'Sousse',
                    'Tataouine' => 'Tataouine',
                    'Tozeur' => 'Tozeur',
                    'Tunis' => 'Tunis',
                    'Zaghouan' => 'Zaghouan'
                ],
                'attr' => [
                    'class' => 'form-select',
                    'help' => 'Sélectionnez le gouvernorat où se trouve l\'atelier'
                ],
                'constraints' => [
                    new \Symfony\Component\Validator\Constraints\NotBlank([
                        'message' => 'Le gouvernorat est obligatoire'
                    ]),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Atelier::class,
            'attr' => ['novalidate' => 'novalidate'],
        ]);
    }
}