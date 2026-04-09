<?php

namespace App\Form;

use App\Entity\Commande;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CommandeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('idPanier', IntegerType::class)
            ->add('idUtilisateur', IntegerType::class)
            ->add('idLivreur', IntegerType::class, [
                'required' => false,
            ])
            ->add('dateCommande', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('etat', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'cherche_livraison' => 'cherche_livraison',
                    'confirmee' => 'confirmee',
                    'en_livraison' => 'en_livraison',
                    'livree' => 'livree',
                    'annulee' => 'annulee',
                ],
            ])
            ->add('dateLivraison', DateType::class, [
                'required' => false,
                'widget' => 'single_text',
            ])
            ->add('localisation', TextType::class, [
                'required' => false,
            ])
            ->add('telephoneClient', TextType::class, [
                'required' => false,
            ])
            ->add('dateConfirmationLivreur', DateTimeType::class, [
                'required' => false,
                'widget' => 'single_text',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commande::class,
        ]);
    }
}
