<?php

namespace App\Form;

use App\Entity\PrayerRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PrayerRequestType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('requesterName', TextType::class, ['label' => 'Nome de quem pede'])
            ->add('request', TextareaType::class, ['label' => 'Pedido de oração'])
            ->add('confidential', CheckboxType::class, ['label' => 'Confidencial', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PrayerRequest::class]);
    }
}
