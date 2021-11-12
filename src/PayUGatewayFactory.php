<?php

/*
 * This file was created by developers working at BitBag
 * Do you need more information about us and what we do? Visit our https://bitbag.io website!
 * We are hiring developers from all over the world. Join us and start your new, exciting adventure and become part of us: https://bitbag.io/career
*/

declare(strict_types=1);

namespace BitBag\SyliusPayUPlugin;

use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;

final class PayUGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => 'payu',
                'payum.factory_title' => 'PayU',
            ]
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'signature_key' => '',
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['signature_key'];

            $config['payum.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    'signature_key' => $config['signature_key'],
                ];
            };
        }
    }
}
