<?php

namespace App\Form;

use App\Entity\Campaign;
use App\Entity\FinancialCategory;
use App\Entity\Member;
use App\Entity\Transaction;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('direction', ChoiceType::class, ['label' => 'Tipo', 'choices' => ['Entrada' => 'entrada', 'Saída' => 'saida']])
            ->add('kind', ChoiceType::class, ['label' => 'Natureza', 'choices' => [
                'Dízimo' => 'dizimo', 'Oferta' => 'oferta', 'Campanha' => 'campanha', 'Despesa' => 'despesa', 'Outro' => 'outro',
            ]])
            ->add('amount', MoneyType::class, ['label' => 'Valor', 'currency' => 'BRL'])
            ->add('occurredAt', DateType::class, ['label' => 'Data', 'widget' => 'single_text', 'input' => 'datetime_immutable'])
            ->add('description', TextType::class, ['label' => 'Descrição', 'required' => false])
            ->add('category', EntityType::class, ['label' => 'Categoria', 'class' => FinancialCategory::class, 'required' => false, 'placeholder' => '—'])
            ->add('member', EntityType::class, ['label' => 'Contribuinte', 'class' => Member::class, 'required' => false, 'placeholder' => '—', 'choice_label' => 'fullName'])
            ->add('campaign', EntityType::class, ['label' => 'Campanha', 'class' => Campaign::class, 'required' => false, 'placeholder' => '—'])
            ->add('paymentMethod', ChoiceType::class, ['label' => 'Forma', 'required' => false, 'choices' => [
                'Dinheiro' => 'dinheiro', 'PIX' => 'pix', 'Cartão' => 'cartao', 'Transferência' => 'transferencia', 'Cheque' => 'cheque',
            ]]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Transaction::class]);
    }
}
