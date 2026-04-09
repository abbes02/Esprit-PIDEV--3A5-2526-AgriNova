<?php

namespace App\Form;

use App\Entity\ProduitAgricole;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitAgricoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorieAgricole', ChoiceType::class, [
                'label' => 'Categorie agricole',
                'required' => false,
                'choices' => [
                    'Fruit' => 'Fruit',
                    'Legume' => 'Legume',
                    'Herbe' => 'Herbe',
                    'Cereale' => 'Cereale',
                    'Produits animaux' => 'Produits animaux',
                ],
                'placeholder' => 'Choisir une categorie',
                'attr' => [
                    'class' => 'form-control',
                ],
            ])
            ->add('dateExpiration', DateType::class, [
                'label' => 'Date d\'expiration',
                'required' => true,
                'widget' => 'single_text',
                'html5' => true,
                'format' => 'yyyy-MM-dd',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProduitAgricole::class,
        ]);
    }

    public function getParent(): string
    {
        return ProduitType::class;
    }
}
