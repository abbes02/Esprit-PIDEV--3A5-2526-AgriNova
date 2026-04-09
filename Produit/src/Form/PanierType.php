<?php

namespace App\Form;

use App\Entity\Panier;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PanierType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idUtilisateur', IntegerType::class)
            ->add('idProduit', IntegerType::class)
            ->add('quantite', IntegerType::class)
            ->add('dateAjout', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('historique', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'show' => 'show',
                    'hide' => 'hide',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Panier::class,
        ]);
    }
}
