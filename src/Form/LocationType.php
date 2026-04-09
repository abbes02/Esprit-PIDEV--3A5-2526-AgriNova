<?php

namespace App\Form;

use App\Entity\Materiel;
final class LocationType
{
    public static function build($builder, array $options = []): void
    {
        $materielChoices = $options['materiel_choices'] ?? [];
        $lockMateriel = (bool) ($options['lock_materiel'] ?? false);

        $builder
            ->add('materiel', 'Symfony\\Bridge\\Doctrine\\Form\\Type\\EntityType', [
                'class' => Materiel::class,
                'choices' => $materielChoices,
                'choice_label' => static fn (Materiel $materiel): string => sprintf(
                    '%s (%s) - %.2f TND/jour',
                    $materiel->getNom() ?? 'N/A',
                    $materiel->getTypeLabel(),
                    $materiel->getPrixLocation() ?? 0
                ),
                'label' => 'Matériel à louer',
                'placeholder' => 'Sélectionner un matériel disponible',
                'disabled' => $lockMateriel,
                'choice_attr' => static fn (Materiel $materiel): array => [
                    'data-price' => number_format((float) ($materiel->getPrixLocation() ?? 0.0), 2, '.', ''),
                    'data-type' => $materiel->getTypeLabel(),
                    'data-owner' => $materiel->getProprietaire() ? trim(($materiel->getProprietaire()->getPrenom() ?? '') . ' ' . ($materiel->getProprietaire()->getNom() ?? '')) : 'N/A',
                ],
                'attr' => [
                    'class' => 'js-rental-materiel',
                ],
            ])
            ->add('dateDebut', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType', [
                'widget' => 'single_text',
                'label' => 'Date de début',
                'attr' => [
                    'class' => 'js-rental-date-start',
                    'min' => (new \DateTime())->format('Y-m-d'),
                ],
            ])
            ->add('dateFin', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\DateType', [
                'widget' => 'single_text',
                'label' => 'Date de fin',
                'attr' => [
                    'class' => 'js-rental-date-end',
                ],
            ])
            ->add('montantTotal', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\HiddenType', [
                'required' => false,
                'attr' => [
                    'class' => 'js-rental-total-input',
                ],
            ]);
    }
}
