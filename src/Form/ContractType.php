<?php

namespace App\Form;

use App\Entity\Contract;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ContractType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('vehicle_uid')
            ->add('customer_uid')
            ->add('sign_datetime', null, [
                'widget' => 'single_text',
            ])
            ->add('locbegin_datetime', null, [
                'widget' => 'single_text',
            ])
            ->add('locend_datetime', null, [
                'widget' => 'single_text',
            ])
            ->add('returning_datetime', null, [
                'widget' => 'single_text',
            ])
            ->add('price')
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Contract::class,
        ]);
    }
}
