<?php

namespace App\Form;

use App\Entity\ProduitIOT;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProduitIOTType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('categorie', ChoiceType::class, [
                'label' => 'Categorie',
                'required' => false,
                'choices' => array_combine(ProduitIOT::CATEGORIES, ProduitIOT::CATEGORIES),
                'placeholder' => 'Choisir une categorie',
                'attr' => [
                    'class' => 'form-control',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProduitIOT::class,
        ]);
    }

    public function getParent(): string
    {
        return ProduitType::class;
    }
}
