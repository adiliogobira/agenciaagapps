<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\SchoolClass;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SchoolClassType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, ['label' => 'Nome da turma'])
            ->add('ageGroup', TextType::class, ['label' => 'Faixa etária', 'required' => false,
                'attr' => ['placeholder' => 'ex.: Adultos, Juvenis, 7–10 anos']])
            ->add('teacher', EntityType::class, [
                'label' => 'Professor(a)', 'class' => Member::class, 'choice_label' => 'fullName',
                'required' => false, 'placeholder' => '— selecione —',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => SchoolClass::class]);
    }
}
