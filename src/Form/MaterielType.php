<?php

namespace App\Form;

use App\Entity\Materiel;

final class MaterielType
{
    public static function build($builder): void
    {
        $typeChoices = array_flip(Materiel::TYPE_LABELS);

        $builder
            ->add('nom', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextType', [
                'label' => 'Nom',
                'attr' => [
                    'placeholder' => 'Ex: John Deere 5100M',
                ],
            ])
            ->add('type', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\ChoiceType', [
                'label' => 'Type d\'équipement',
                'choices' => $typeChoices,
                'placeholder' => 'Sélectionner un type',
                'attr' => [
                    'class' => 'js-materiel-type',
                ],
            ])
            ->add('description', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\TextareaType', [
                'label' => 'Description',
                'required' => false,
                'attr' => [
                    'placeholder' => 'Décrivez les caractéristiques, l\'état général, les accessoires inclus...',
                    'rows' => 4,
                ],
            ])
            ->add('prixLocation', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\MoneyType', [
                'label' => 'Prix location (par jour)',
                'currency' => 'TND',
                'divisor' => 1,
                'attr' => [
                    'placeholder' => '0.00',
                ],
            ])
            ->add('imageFile', 'Symfony\\Component\\Form\\Extension\\Core\\Type\\FileType', [
                'label' => 'Image du matériel',
                'mapped' => false,
                'required' => false,
                'attr' => [
                    'accept' => 'image/*',
                    'class' => 'js-image-upload',
                ],
            ]);
    }
}
