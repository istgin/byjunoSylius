<?php

declare(strict_types=1);

namespace Ij\SyliusByjunoPlugin\Controller;

use App\Entity\Payment\PaymentMethod;
use Sylius\Bundle\CoreBundle\Doctrine\ORM\PaymentMethodRepository;
use Symfony\Component\HttpFoundation\Response;

final class Byjunotmx
{

    private PaymentMethodRepository $paymentMethodRepository;

    private string $firstPaymentMethodFactoryName;

    public function __construct(
        PaymentMethodRepository $paymentMethodRepository,
        string $firstPaymentMethodFactoryName)
    {
        $this->paymentMethodRepository = $paymentMethodRepository;
        $this->firstPaymentMethodFactoryName = $firstPaymentMethodFactoryName;
    }

    public function tmxAction(): Response
    {
        $exists = false;
        $tmxConfig = "";
        foreach($this->paymentMethodRepository->findAll() as $payment) {
           /* @var $payment PaymentMethod */
           if ($payment->getGatewayConfig()->getFactoryName() == "byjuno") {
                if ($payment->getGatewayConfig()->getConfig()["tmx_enabled"] == "yes") {
                    $exists = true;
                    $tmxConfig = $payment->getGatewayConfig()->getConfig()["tmx_key"];
                }
                break;
            }
        }
        if (!$exists) {
            return new Response("");
        }
        if (!empty($_SESSION["BYJUNO_TMX"])) {
            return new Response("");
        }
        $_SESSION["BYJUNO_TMX"] = uniqid("byjunotmx_");
        return new Response("
        <script type=\"text/javascript\" src=\"https://h.online-metrix.net/fp/tags.js?org_id=".$tmxConfig."&session_id=".$_SESSION["BYJUNO_TMX"]."&pageid=checkout\"></script>
        <noscript>
            <iframe style=\"width: 100px; height: 100px; border: 0; position: absolute; top: -5000px;\" src=\"https://h.online-metrix.net/tags?org_id=".$tmxConfig."&session_id=".$_SESSION["BYJUNO_TMX"]."&pageid=checkout\"></iframe>
        </noscript>");
    }

}
