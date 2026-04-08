<?php

namespace App\Form;

use App\Entity\Formation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FormationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('titre', TextType::class)
            ->add('description', TextareaType::class, ['attr' => ['rows' => 4]])
            ->add('domaine', TextType::class)
            ->add('niveau', ChoiceType::class, [
                'choices' => [
                    'Débutant'      => 'debutant',
                    'Intermédiaire' => 'intermediaire',
                    'Avancé'        => 'avance',
                ],
            ])
            ->add('dureeHeures', IntegerType::class, ['label' => 'Durée (heures)'])
            ->add('prix', NumberType::class, ['scale' => 2])
            ->add('dateCreation', DateType::class, [
                'widget' => 'single_text',
                'label'  => 'Date de création',
            ])
            ->add('statut', ChoiceType::class, [
                'choices' => [
                    'Actif'   => 'actif',
                    'Inactif' => 'inactif',
                    'Archivé' => 'archive',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Formation::class]);
    }
}
