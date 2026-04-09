<?php

namespace App\Form;

use App\Entity\Materiel;
use App\Entity\Panne;

final class PanneType
{
    public static function build($builder, array $options = []): void
    {
        $materielChoices = $options['materiel_choices'] ?? [];
        $lockMateriel = (bool) ($options['lock_materiel'] ?? false);

        $severityChoices = array_flip(Panne::SEVERITY_LABELS);
        $typeChoices = array_flip(Panne::TYPE_LABELS);

        $builder
            ->add('materiel', 'Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType', [
                'class' => Materiel::class,
                'choices' => $materielChoices,
                'choice_label' => static fn (Materiel $materiel): string => sprintf(
                    '%s (%s) - %s',
                    $materiel->getNom() ?? 'N/A',
                    $materiel->getTypeLabel(),
                    $materiel->getProprietaire() ? trim(($materiel->getProprietaire()->getPrenom() ?? '') . ' ' . ($materiel->getProprietaire()->getNom() ?? '')) : 'N/A'
                ),
                'label' => 'Matériel concerné',
                'placeholder' => 'Sélectionner un matériel',
                'disabled' => $lockMateriel,
                'attr' => [
                    'class' => 'js-panne-materiel',
                ],
            ])
            ->add('severity', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'label' => 'Niveau de gravité',
                'choices' => $severityChoices,
                'expanded' => true,
                'attr' => [
                    'class' => 'js-severity-selector severity-radio-group',
                ],
            ])
            ->add('panneType', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'label' => 'Type de panne',
                'choices' => $typeChoices,
                'placeholder' => 'Sélectionner le type',
                'attr' => [
                    'class' => 'js-panne-type',
                ],
            ])
            ->add('descriptionPanne', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType', [
                'label' => 'Description du problème',
                'attr' => [
                    'placeholder' => 'Décrivez le problème en détail: symptômes, circonstances, bruit anormal...',
                    'rows' => 5,
                ],
            ]);
    }
}
