<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\PastoralAppointment;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PastoralAppointmentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $types = [];
        foreach (PastoralAppointment::TYPES as $k => $l) { $types[$l] = $k; }

        $builder
            ->add('type', ChoiceType::class, ['label' => 'Tipo', 'choices' => $types])
            ->add('scheduledAt', DateTimeType::class, ['label' => 'Data e hora', 'widget' => 'single_text'])
            ->add('member', EntityType::class, [
                'label' => 'Membro', 'class' => Member::class, 'choice_label' => 'fullName',
                'required' => false, 'placeholder' => '— selecione —',
            ])
            ->add('pastor', EntityType::class, [
                'label' => 'Responsável', 'class' => User::class, 'choice_label' => 'fullName',
                'required' => false, 'placeholder' => '— selecione —',
                'query_builder' => function ($repo) use ($options) {
                    $qb = $repo->createQueryBuilder('u')->orderBy('u.fullName', 'ASC');
                    if ($options['church'] !== null) {
                        $qb->andWhere('u.church = :c')->setParameter('c', $options['church']);
                    }
                    return $qb;
                },
            ])
            ->add('subject', TextType::class, ['label' => 'Assunto', 'required' => false])
            ->add('confidentialNotes', TextareaType::class, ['label' => 'Anotações (confidencial)', 'required' => false])
            ->add('status', ChoiceType::class, [
                'label' => 'Situação',
                'choices' => ['Agendado' => 'agendado', 'Realizado' => 'realizado', 'Cancelado' => 'cancelado'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => PastoralAppointment::class, 'church' => null]);
    }
}
