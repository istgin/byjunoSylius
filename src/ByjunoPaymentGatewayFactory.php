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
