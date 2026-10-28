<?php

namespace App\Form;

use App\Entity\Event;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [];
        foreach (Event::TYPES as $k => $l) { $types[$l] = $k; }

        $builder
            ->add('type', ChoiceType::class, ['label' => 'Tipo', 'choices' => $types])
            ->add('name', TextType::class, ['label' => 'Nome do evento'])
            ->add('startsAt', DateTimeType::class, ['label' => 'Início', 'widget' => 'single_text'])
            ->add('endsAt', DateTimeType::class, ['label' => 'Término', 'widget' => 'single_text', 'required' => false])
            ->add('location', TextType::class, ['label' => 'Local', 'required' => false])
            ->add('fee', MoneyType::class, ['label' => 'Valor da inscrição', 'currency' => 'BRL', 'required' => false])
            ->add('capacity', IntegerType::class, ['label' => 'Vagas (capacidade)', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Event::class]);
    }
}
