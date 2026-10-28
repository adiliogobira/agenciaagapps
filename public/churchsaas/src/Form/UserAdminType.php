<?php

namespace App\Form;

use App\Entity\Member;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Cadastro/edição de usuários pelo Admin. A senha (plainPassword) não é mapeada;
 * o controller a criptografa e define quando preenchida.
 */
class UserAdminType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName', TextType::class, ['label' => 'Nome completo'])
            ->add('email', EmailType::class, ['label' => 'E-mail (login)'])
            ->add('roles', ChoiceType::class, [
                'label' => 'Perfis de acesso',
                'multiple' => true,
                'expanded' => true,
                'choices' => [
                    'Administrador' => 'ROLE_ADMIN',
                    'Pastor' => 'ROLE_PASTOR',
                    'Secretário' => 'ROLE_SECRETARIO',
                    'Tesoureiro' => 'ROLE_TESOUREIRO',
                    'Diácono' => 'ROLE_DIACONO',
                    'Líder de Ministério' => 'ROLE_LIDER_MINISTERIO',
                ],
            ])
            ->add('member', EntityType::class, [
                'label' => 'Vincular a um membro (opcional)',
                'class' => Member::class, 'choice_label' => 'fullName',
                'required' => false, 'placeholder' => '— nenhum —',
            ])
            ->add('active', CheckboxType::class, ['label' => 'Ativo', 'required' => false])
            ->add('plainPassword', PasswordType::class, [
                'label' => $options['is_new'] ? 'Senha' : 'Nova senha (deixe em branco para manter)',
                'mapped' => false,
                'required' => $options['is_new'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => User::class, 'is_new' => false]);
        $resolver->setAllowedTypes('is_new', 'bool');
    }
}
