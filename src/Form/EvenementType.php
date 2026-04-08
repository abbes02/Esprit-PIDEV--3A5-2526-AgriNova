<?php

namespace App\Form;

use App\Entity\Evenement;
use App\Entity\Formation;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EvenementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('formation', EntityType::class, [
                'class'        => Formation::class,
                'choice_label' => 'titre',
                'placeholder'  => '-- Sélectionner une formation --',
                'label'        => 'Formation',
            ])
            ->add('dateDebut', DateTimeType::class, [
                'widget' => 'single_text',
                'label'  => 'Date de début',
            ])
            ->add('dateFin', DateTimeType::class, [
                'widget' => 'single_text',
                'label'  => 'Date de fin',
            ])
            ->add('lieu', TextType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'Présentiel' => 'presentiel',
                    'En ligne'   => 'en_ligne',
                    'Hybride'    => 'hybride',
                ],
            ])
            ->add('capaciteMax', IntegerType::class, ['label' => 'Capacité max'])
            ->add('nombreInscrits', IntegerType::class, ['label' => 'Nombre d\'inscrits'])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Planifié'  => 'planifie',
                    'En cours'  => 'en_cours',
                    'Terminé'   => 'termine',
                    'Annulé'    => 'annule',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Evenement::class]);
    }
}
