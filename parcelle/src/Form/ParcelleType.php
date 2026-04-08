<?php

namespace App\Form;

use App\Entity\Parcelle;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParcelleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('proprietaire', TextType::class, [
                'label' => 'Propriétaire',
                'attr' => ['maxlength' => 100],
            ])
            ->add('localisation', TextType::class, [
                'label' => 'Localisation',
                'attr' => ['maxlength' => 100],
            ])
            ->add('longueur', NumberType::class, [
                'label' => 'Longueur',
                'scale' => 2,
                'html5' => true,
            ])
            ->add('largeur', NumberType::class, [
                'label' => 'Largeur',
                'scale' => 2,
                'html5' => true,
            ])
            ->add('typeDeSol', TextType::class, [
                'label' => 'Type de sol',
                'attr' => ['maxlength' => 50],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Parcelle::class,
            'csrf_protection' => false,
        ]);
    }
}
?>

