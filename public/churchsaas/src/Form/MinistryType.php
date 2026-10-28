<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Ministry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MinistryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nome do ministério'])
            ->add('description', TextareaType::class, ['label' => 'Descrição', 'required' => false, 'attr' => ['rows' => 3]])
            ->add('leader', EntityType::class, [
                'label' => 'Líder responsável',
                'class' => Member::class,
                'choice_label' => 'fullName',
                'required' => false,
                'placeholder' => '— selecione —',
            ])
            ->add('active', CheckboxType::class, ['label' => 'Ativo', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Ministry::class]);
    }
}
