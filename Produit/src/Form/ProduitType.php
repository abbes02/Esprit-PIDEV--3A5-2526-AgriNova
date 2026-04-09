<?php

namespace App\Form;

use App\Entity\Produit;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ref', TextType::class, [
                'label' => 'Reference',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Ex: REF-001',
                    'required' => true,
                ],
            ])
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'required' => true,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Nom du produit',
                    'required' => true,
                ],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Description du produit (optionnel)',
                    'rows' => 4,
                ],
            ])
            ->add('image', TextType::class, [
                'label' => 'Image',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'C:\\Users\\...\\image.jpg',
                ],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantite',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Quantite en stock',
                    'min' => 0,
                ],
            ])
            ->add('prix', NumberType::class, [
                'label' => 'Prix',
                'required' => false,
                'scale' => 2,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'Prix en TND',
                    'min' => 0,
                    'step' => '0.01',
                    'inputmode' => 'decimal',
                    'pattern' => '[0-9]+([.,][0-9]+)?',
                    'oninput' => "this.value=this.value.replace(/[^0-9.,]/g,'').replace(/(.*[.,].*)[.,].*/,'$1');",
                ],
            ])
            ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Produit::class,
        ]);
    }
}
