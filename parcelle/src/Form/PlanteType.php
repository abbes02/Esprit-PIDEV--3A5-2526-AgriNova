<?php

namespace App\Form;

use App\Entity\Parcelle;
use App\Entity\Plante;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PlanteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nom', TextType::class, [
                'label' => 'Nom',
                'attr' => ['maxlength' => 100],
            ])
            ->add('parcelle', EntityType::class, [
                'class' => Parcelle::class,
'choice_label' => 'localisation',
                'label' => 'Parcelle',
                'placeholder' => 'Choisir une parcelle',
            ])
            ->add('type', TextType::class, [
                'label' => 'Type',
                'attr' => ['maxlength' => 50],
            ])
            ->add('image', FileType::class, [
                'label' => 'Image',
                'required' => false,
                'mapped' => false,
                'attr' => ['accept' => 'image/*'],
            ])
            ->add('quantite', IntegerType::class, [
                'label' => 'Quantité',
            ])
            ->add('surface', NumberType::class, [
                'label' => 'Surface (m²)',
                'scale' => 2,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Plante::class,
            'csrf_protection' => false,
        ]);
    }
}
?>
