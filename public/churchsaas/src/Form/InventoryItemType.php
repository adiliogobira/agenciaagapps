<?php

namespace App\Form;

use App\Entity\InventoryItem;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InventoryItemType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $cats = [];
        foreach (InventoryItem::CATEGORIES as $k => $l) { $cats[$l] = $k; }

        $builder
            ->add('name', TextType::class, ['label' => 'Item'])
            ->add('category', ChoiceType::class, ['label' => 'Categoria', 'choices' => $cats])
            ->add('unit', TextType::class, ['label' => 'Unidade', 'required' => false,
                'attr' => ['placeholder' => 'un, kg, L, pacote, fardo']])
            ->add('minQuantity', NumberType::class, ['label' => 'Estoque mínimo (alerta)', 'required' => false, 'scale' => 2]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => InventoryItem::class]);
    }
}
