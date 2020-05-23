<?php

namespace App\Form;

use App\Entity\Measure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ReleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $types = [
            /*'Ilek (hc)' => 'electricite-ilek-heures-creuses',
            'Ilek (hp)' => 'electricite-ilek-heures-pleines',
            'Ilek (gaz)' => 'gaz-ilek',*/
            'Panneau Solaire' => 'panneau-solaire',
        ];

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $types,
                'label' => 'Type',
                'expanded' => false,
                'multiple' => false,
                'mapped' => true,
            ])
            ->add('releve', null, ['label' => 'Valeur'])
            ->add('stated_at', DateType::class, ['label' => 'Jour de la relève', 'widget' => 'single_text'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
