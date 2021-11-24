<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Form\Type;

use BitBag\SyliusPayUPlugin\Bridge\OpenPayUBridgeInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

final class ByjunoGatewayConfigurationType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'mode',
                ChoiceType::class,
                [
                    'choices' => [
                        'byjuno.byjuno_plugin.test' => "test",
                        'byjuno.byjuno_plugin.live' => "live",
                    ],
                    'label' => 'byjuno.byjuno_plugin.mode',
                ]
            )
            ->add(
                'client_id',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.client_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.client_id.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'user_id',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.user_id',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.user_id.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'password',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.password',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.password.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'tech_email',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.tech_email',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.tech_email.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'min_amount',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.min_amount',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.min_amount.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            )
            ->add(
                'max_amount',
                TextType::class,
                [
                    'label' => 'byjuno.byjuno_plugin.max_amount',
                    'constraints' => [
                        new NotBlank(
                            [
                                'message' => 'byjuno.byjuno_plugin.gateway_configuration.max_amount.not_blank',
                                'groups' => ['sylius'],
                            ]
                        ),
                    ],
                ]
            );
    }
}
