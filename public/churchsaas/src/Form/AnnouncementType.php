<?php

namespace App\Form;

use App\Entity\Announcement;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AnnouncementType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $channels = [];
        foreach (Announcement::CHANNELS as $k => $l) { $channels[$l] = $k; }
        $audiences = [];
        foreach (Announcement::AUDIENCES as $k => $l) { $audiences[$l] = $k; }

        $builder
            ->add('title', TextType::class, ['label' => 'Título'])
            ->add('body', TextareaType::class, ['label' => 'Mensagem', 'attr' => ['rows' => 4]])
            ->add('channel', ChoiceType::class, ['label' => 'Canal', 'choices' => $channels])
            ->add('audience', ChoiceType::class, ['label' => 'Público', 'choices' => $audiences]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Announcement::class]);
    }
}
