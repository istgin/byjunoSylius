<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin;

use Ij\SyliusByjunoPlugin\Action\Api\ObtainTokenForCreditCardAction;
use Ij\SyliusByjunoPlugin\Action\CaptureAction;
use Ij\SyliusByjunoPlugin\Action\ConvertPaymentAction;
use Ij\SyliusByjunoPlugin\Action\StatusAction;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\GatewayFactory;
use Payum\Stripe\Action\Api\ObtainTokenAction;

final class ByjunoPaymentGatewayFactory extends GatewayFactory
{
    protected function populateConfig(ArrayObject $config): void
    {
        $config->defaults(
            [
                'payum.factory_name' => 'byjuno',
                'payum.factory_title' => 'Byjuno',
                'payum.template.obtain_token' => '@IjByjuno/payment.html.twig',

                #'payum.action.capture' => new CaptureAction(),
                #'payum.action.convert_payment' => new ConvertPaymentAction(),
                #'payum.action.status' => new StatusAction(),
                #'payum.action.obtain_token' => function (ArrayObject $config) {
                #    return new ObtainTokenForCreditCardAction($config['payum.template.obtain_token']);
               # },
                /*,
                'payum.action.capture' => new CaptureAction(),
                'payum.action.authorize' => new AuthorizeAction(),
                'payum.action.refund' => new RefundAction(),
                'payum.action.cancel' => new CancelAction(),
                'payum.action.notify' => new NotifyAction(),
                'payum.action.status' => new StatusAction(),
                'payum.action.convert_payment' => new ConvertPaymentAction(),
                */
            ]
        );

        if (false === (bool) $config['payum.api']) {
            $config['payum.default_options'] = [
                'gateway_name' => 'byjuno',
                'mode' => 'test',
                'client_id' => '',
                'user_id' => '',
                'password' => '',
                'tech_email' => '',
                'timeout' => '30',
                'min_amount' => '1',
                'max_amount' => '10000',
                'b2c_allow' => 'yes',
                'payment_method_b2c' => 'INVOICE',
                'repayment_type_b2c' => '',
                'b2b_allow' => 'no',
                'payment_method_b2b' => 'INVOICE',
                'repayment_type_b2b' => '',
                'accept_s2_ij' => '2',
                'accept_s2_client' => '',
                'accept_s3' => '2',
                'accept_cdp' => '2',
                'cdp_enabled' => 'yes',
                'tmx_enabled' => 'yes',
                'tmx_key' => 'lq866c5i',
            ];
            $config->defaults($config['payum.default_options']);

            $config['payum.required_options'] = ['client_id', 'user_id', 'password', 'tech_email', 'min_amount', 'max_amount'];

            $config['payum.api'] = static function (ArrayObject $config): array {
                $config->validateNotEmpty($config['payum.required_options']);

                return [
                    'gateway_name' => $config['gateway_name'],
                    'client_id' => $config['client_id'],
                    'mode' => $config['mode'],
                    'user_id' => $config['user_id'],
                    'password' => $config['password'],
                    'tech_email' => $config['tech_email'],
                    'timeout' => $config['timeout'],
                    'min_amount' => $config['min_amount'],
                    'max_amount' => $config['max_amount'],
                    'b2c_allow' => $config['b2c_allow'],
                    'payment_method_b2c' => $config['payment_method_b2c'],
                    'repayment_type_b2c' => $config['repayment_type_b2c'],
                    'b2b_allow' => $config['b2b_allow'],
                    'payment_method_b2b' => $config['payment_method_b2b'],
                    'repayment_type_b2b' => $config['repayment_type_b2b'],
                    'accept_s2_ij' => $config['accept_s2_ij'],
                    'accept_s2_client' => $config['accept_s2_client'],
                    'accept_s3' => $config['accept_s3'],
                    'accept_cdp' => $config['accept_cdp'],
                    'cdp_enabled' => $config['cdp_enabled'],
                    'tmx_enabled' => $config['tmx_enabled'],
                    'tmx_key' => $config['tmx_key']
                ];
            };
        }

        $config['payum.paths'] = array_replace([
            'IjByjuno' => __DIR__.'/Resources/views',
        ], $config['payum.paths'] ?: []);
    }
}
