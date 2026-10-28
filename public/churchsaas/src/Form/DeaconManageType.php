<?php

namespace App\Form;

use App\Entity\Deacon;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeaconManageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('leader', CheckboxType::class, [
                'label' => 'Líder do diaconato (pode criar e gerir escalas)',
                'required' => false,
            ])
            ->add('active', CheckboxType::class, ['label' => 'Ativo', 'required' => false])
            ->add('ordinationDate', DateType::class, [
                'label' => 'Data da ordenação', 'widget' => 'single_text', 'required' => false,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Deacon::class]);
    }
}
