<?php

namespace App\Form;

use App\Entity\Produit;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'required' => false,
                'attr' => ['maxlength' => 100]
            ])
            ->add('description', TextareaType::class, [
                'required' => false
            ])
            ->add('prix', NumberType::class, [
                'required' => false,
                'scale' => 2,
                'attr' => ['min' => 0]
            ])
            ->add('disponibilite', CheckboxType::class, [
                'required' => false
            ])
            ->add('idArtisan', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'required' => false
            ])
            ->add('categorie', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Clothing & Textiles' => 'Clothing & Textiles',
                    'Home Décor' => 'Home Décor',
                    'Jewelry & Fashion Accessories' => 'Jewelry & Fashion Accessories',
                    'Handicrafts & Pottery' => 'Handicrafts & Pottery',
                    'Beauty & Wellness' => 'Beauty & Wellness',
                    'Gifts & Souvenirs' => 'Gifts & Souvenirs',
                ],
                'placeholder' => 'Select a category',
                'attr' => ['class' => 'form-control']
            ])
            ->add('quantite', NumberType::class, [
                'required' => false,
                'attr' => ['min' => 0]
            ])
            ->add('imageFile', VichFileType::class, [
                'label' => 'Image du produit',
                'required' => false,
                'allow_delete' => false,
                'download_uri' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'form-control',
                    'help' => 'Sélectionnez une image pour le produit'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}