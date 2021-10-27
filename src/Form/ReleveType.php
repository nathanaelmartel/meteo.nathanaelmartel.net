<?php

namespace App\Form;

use App\Entity\Measure;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ReleveType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /*  $types = [
              'Panneau Solaire' => 'panneau-solaire',
              'Eau' => 'eau',
          ];*/
        $object = $builder->getData();

        $builder
            /*->add('type', ChoiceType::class, [
                'choices' => $types,
                'label' => 'Type',
                'expanded' => false,
                'multiple' => false,
                'mapped' => true,
            ])*/
            ->add('stated_at', DateType::class, [
                'label' => 'Jour de la relève',
                'widget' => 'single_text',
            ])
            ->add('releve', null, [
                'label' => ('' == $object->getUnit()) ? 'Valeur' : $object->getUnit(),
            ])
        ;

        if ('voiture' == $object->getType()) {
            $builder
                ->add('kilometre', null, [
                    'label' => 'Kilometrage',
                    'mapped' => false,
                ])
                ->add('essence', null, [
                    'label' => 'Essence',
                    'mapped' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Measure::class,
        ]);
    }
}
