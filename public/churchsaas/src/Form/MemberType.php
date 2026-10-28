<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\Ministry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MemberType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, ['label' => 'Nome completo'])
            ->add('cpf', TextType::class, ['label' => 'CPF', 'required' => false])
            ->add('rg', TextType::class, ['label' => 'RG', 'required' => false])
            ->add('birthDate', DateType::class, ['label' => 'Nascimento', 'widget' => 'single_text', 'required' => false, 'input' => 'datetime_immutable'])
            ->add('gender', ChoiceType::class, ['label' => 'Sexo', 'required' => false, 'choices' => ['Masculino' => 'M', 'Feminino' => 'F']])
            ->add('maritalStatus', ChoiceType::class, ['label' => 'Estado civil', 'required' => false, 'choices' => [
                'Solteiro(a)' => 'solteiro', 'Casado(a)' => 'casado', 'Viúvo(a)' => 'viuvo',
                'Divorciado(a)' => 'divorciado', 'União estável' => 'uniao_estavel',
            ]])
            ->add('phone', TextType::class, ['label' => 'Telefone / WhatsApp', 'required' => false])
            ->add('email', EmailType::class, ['label' => 'E-mail', 'required' => false])
            ->add('address', TextareaType::class, ['label' => 'Endereço', 'required' => false])
            ->add('baptismDate', DateType::class, ['label' => 'Data de batismo', 'widget' => 'single_text', 'required' => false, 'input' => 'datetime_immutable'])
            ->add('membershipDate', DateType::class, ['label' => 'Membro desde', 'widget' => 'single_text', 'required' => false, 'input' => 'datetime_immutable'])
            ->add('entryType', ChoiceType::class, ['label' => 'Forma de ingresso', 'required' => false, 'choices' => [
                'Batismo' => 'batismo', 'Transferência' => 'transferencia', 'Aclamação' => 'aclamacao', 'Conversão' => 'conversao',
            ]])
            ->add('churchRole', ChoiceType::class, ['label' => 'Função na igreja', 'choices' => [
                'Membro' => 'membro', 'Obreiro' => 'obreiro', 'Diácono' => 'diacono',
                'Presbítero' => 'presbitero', 'Evangelista' => 'evangelista', 'Pastor' => 'pastor',
            ]])
            ->add('ministries', EntityType::class, [
                'label' => 'Ministérios',
                'class' => Ministry::class,
                'multiple' => true,
                'expanded' => false,
                'required' => false,
                'attr' => ['class' => 'form-select', 'size' => 5,
                    'title' => 'Segure Ctrl (ou ⌘) para selecionar mais de um'],
            ])
            ->add('status', ChoiceType::class, ['label' => 'Situação', 'choices' => [
                'Ativo' => 'ativo', 'Inativo' => 'inativo', 'Em disciplina' => 'disciplina', 'Transferido' => 'transferido',
            ]])
            ->add('photo', FileType::class, [
                'label' => 'Foto',
                'mapped' => false,
                'required' => false,
                'constraints' => [new File(maxSize: '4M', mimeTypes: ['image/jpeg', 'image/png', 'image/webp'], mimeTypesMessage: 'Envie uma imagem JPG, PNG ou WEBP.')],
            ])
            ->add('notes', TextareaType::class, ['label' => 'Observações', 'required' => false]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Member::class]);
    }
}
