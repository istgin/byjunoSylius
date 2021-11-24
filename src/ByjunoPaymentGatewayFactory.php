<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class ByjunoPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => 'byjuno',
                'payum.factory_title' => 'Byjuno',
            ]
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'gateway_name' => 'byjuno',
                'client_id' => '',
                'user_id' => '',
                'password' => '',
                'tech_email' => '',
                'min_amount' => '',
                'max_amount' => '',
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['client_id', 'user_id', 'password', 'tech_email', 'min_amount', 'max_amount'];

            $config['payum.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    'gateway_name' => $config['gateway_name'],
                    'client_id' => $config['client_id'],
                    'user_id' => $config['user_id'],
                    'password' => $config['password'],
                    'tech_email' => $config['tech_email'],
                    'min_amount' => $config['min_amount'],
                    'max_amount' => $config['max_amount']
                ];
            };
        }
    }
}
