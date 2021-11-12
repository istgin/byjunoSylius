<?php

declare(strict_types=1);

namespace BitBag\SyliusPayUPlugin\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class PayUGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'signature_key',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.signature_key',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.signature_key.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            );
    }
}
