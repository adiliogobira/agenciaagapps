<?php

namespace App\Form;

use App\Entity\ServiceSlot;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Formulário para o líder/pastor montar uma vaga de serviço na escala.
 */
class ServiceSlotType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $choices = [];
        foreach (ServiceSlot::ACTIVITIES as $key => $label) {
            $choices[$label] = $key;
        }

        $builder
            ->add('activity', ChoiceType::class, [
                'label' => 'Atividade',
                'choices' => $choices,
            ])
            ->add('notes', TextType::class, [
                'label' => 'Detalhe (opcional)',
                'required' => false,
                'attr' => ['placeholder' => 'ex.: porta lateral, 1º turno, berçário'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => ServiceSlot::class]);
    }
}
