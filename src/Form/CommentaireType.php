<?php

namespace App\Form;

use App\Entity\Livre;
use App\Entity\Commentaire;
use App\Entity\Utilisateur;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class CommentaireType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('message', TextareaType::class, [
                'label' => 'Votre commentaire',
                'attr' => ['rows' => 5],
            ]);
        //     ->add('date_commentaire', null, [
        //         'widget' => 'single_text',
        //     ])
        //     ->add('modification_commentaire')
        //     ->add('utilisateur', EntityType::class, [
        //         'class' => Utilisateur::class,
        //         'choice_label' => 'id',
        //     ])
        //     ->add('livre', EntityType::class, [
        //         'class' => Livre::class,
        //         'choice_label' => 'id',
        //     ])
        // ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Commentaire::class,
        ]);
    }
}
