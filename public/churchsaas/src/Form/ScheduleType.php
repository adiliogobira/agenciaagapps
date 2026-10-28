<?php

namespace App\Form;

use App\Entity\Schedule;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ScheduleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('type', ChoiceType::class, ['label' => 'Tipo de escala', 'choices' => array_flip(Schedule::TYPES)])
            ->add('title', TextType::class, ['label' => 'Título / descrição'])
            ->add('scheduledAt', DateTimeType::class, ['label' => 'Data e hora', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('location', TextType::class, ['label' => 'Local', 'required' => false])
            ->add('notes', TextareaType::class, ['label' => 'Observações', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Schedule::class]);
    }
}
