<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Sweatshirt;

class SweatshirtType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nom'])
            ->add('price', NumberType::class, ['label' => 'Prix'])
            ->add('stockXS', NumberType::class, [
                'label' => 'Stock XS',
                'mapped' => false,
            ])
            ->add('stockS', NumberType::class, [
                'label' => 'Stock S',
                'mapped' => false,
            ])
            ->add('stockM', NumberType::class, [
                'label' => 'Stock M',
                'mapped' => false,
            ])
            ->add('stockL', NumberType::class, [
                'label' => 'Stock L',
                'mapped' => false,
            ])
            ->add('stockXL', NumberType::class, [
                'label' => 'Stock XL',
                'mapped' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Sweatshirt::class,
        ]);
    }
}
