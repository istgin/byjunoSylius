<?php
/**
 * Created by Byjuno.
 * User: i.sutugins
 * Date: 14.4.9
 * Time: 16:42
 */
namespace Ij\SyliusByjunoPlugin\Api;

use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Ij\SyliusByjunoPlugin\Api\Communicator\ByjunoRequest;
use Ij\SyliusByjunoPlugin\Entity\ByjunoLog;
use Ij\SyliusByjunoPlugin\Repository\ByjunoLogRepository;
use Sylius\Component\Core\Model\AddressInterface;
use Sylius\Component\Core\Model\PaymentInterface as SyliusPaymentInterface;
use Sylius\Component\Customer\Model\CustomerInterface;
use Symfony\Component\HttpFoundation\Response;

class DataHelper {

    public static function getClientIp()
    {
        $ipaddress = '';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (!empty($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (!empty($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }
     //   $addrMethod = $this->_scopeConfig->getValue('byjunocheckoutsettings/advanced/ip_detect_string', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
     //   if (!empty($addrMethod) && !empty($_SERVER[$addrMethod])) {
     //       $ipaddress = $_SERVER[$addrMethod];
     //   }
        return $ipaddress;
    }

    public static function mapMethod($type)
    {
        if ($type == 'INVOICE') {
            return "INVOICE";
        } else if ($type == 'INSTALLMENT') {
            return "INSTALLMENT";
        }
        return "INVOICE";
    }

    public static function mapRepayment($type)
    {
        if ($type == 'installment_3installment_enable') {
            return "10";
        } else if ($type == 'installment_10installment_enable') {
            return "5";
        } else if ($type == 'installment_12installment_enable') {
            return "8";
        } else if ($type == 'installment_24installment_enable') {
            return "9";
        } else if ($type == 'installment_4x12installment_enable') {
            return "1";
        } else if ($type == 'installment_4x10installment_enable') {
            return "2";
        } else if ($type == 'invoice_single_enable') {
            return "3";
        } else if ($type == 'invoice_partial_enable') {
            return "4";
        }
        return "0";
    }

    public static function byjunoIsStatusOk($status, $config)
    {
        try {
            if ($config === "")
            {
                return false;
            }
            $stateArray = explode(",", $config);
            if (in_array($status, $stateArray)) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param array $config
     * @param SyliusPaymentInterface $payment
     * @param $pref_lang
     * @param string $riskOwner
     * @param string $orderId
     * @param string $invoiceDelivery
     * @param string $transactionNumber
     * @param string $orderClosed
     * @return ByjunoRequest
     * @throws \Exception
     */
    public static function CreateSyliusShopRequestOrderQuote(Array $config, SyliusPaymentInterface $payment, $pref_lang, $riskOwner = "",
                                                             $orderId = "", $invoiceDelivery = "",
                                                             $transactionNumber = "", $isCDP = "",
                                                             $orderClosed = "NO")
    {
        //var_dump($config);
        /** @var $customer CustomerInterface */
        $customer = $payment->getOrder()->getCustomer();
        /** @var $billingAddress AddressInterface */
        $billingAddress = $payment->getOrder()->getBillingAddress();
        /** @var $shippingAddress AddressInterface */
        $shippingAddress = $payment->getOrder()->getShippingAddress();
        $request = new ByjunoRequest();
        $request->setClientId($config["client_id"]);
        $request->setUserID($config["user_id"]);
        $request->setPassword($config["password"]);
        $request->setVersion("1.00");
        try {
           $request->setRequestEmail($config["tech_email"]);
        } catch (\Exception $e) {

        }
        /** @var $dateTimeDob DateTimeInterface */
        $dateTimeDob = $customer->getBirthday();
        if (!empty($b)) {
            try {
                $request->setDateOfBirth($dateTimeDob->format('Y-m-d'));
            } catch (\Exception $e) {

            }
        }

        if (!empty($dob_custom)) {
            try {
                $dobObject = new \DateTime($dob_custom);
                if ($dobObject != null) {
                    $request->setDateOfBirth($dobObject->format('Y-m-d'));
                }
            } catch (\Exception $e) {

            }
        }
        $b2b = false;
        $company = $billingAddress->getCompany();
        if (!empty($company)) {
            $b2b = true;
        }

      //  $gender_male_possible_prefix_array = $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/gender_male_possible_prefix',
      //      \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
      //  $gender_female_possible_prefix_array = $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/gender_female_possible_prefix',
      //      \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $gender_male_possible_prefix = array();//explode(";", strtolower($gender_male_possible_prefix_array));
        $gender_female_possible_prefix = array();//explode(";", strtolower($gender_female_possible_prefix_array));

        $gender = $customer->getGender();
        $request->setGender('0');
        if (!empty($gender)) {
            if (in_array(strtolower($gender), $gender_male_possible_prefix)) {
                $request->setGender('1');
            } else if (in_array(strtolower($gender), $gender_female_possible_prefix)) {
                $request->setGender('2');
            }
        }

        // Custom gender
        /*
        if (!empty($gender_custom)) {
            if (in_array(strtolower($gender_custom), $gender_male_possible_prefix)) {
                $request->setGender('1');
            } else if (in_array(strtolower($gender_custom), $gender_female_possible_prefix)) {
                $request->setGender('2');
            }
        }
        */

        $billingStreet = $billingAddress->getStreet();
        $requestId = uniqid((String)$billingAddress->getId() . "_");
        $request->setRequestId($requestId);
        $reference = $customer->getId();
        $request->setCustomerReference($reference);

        $request->setFirstName((String)$billingAddress->getFirstname());
        $request->setLastName((String)$billingAddress->getLastname());
        $request->setFirstLine(trim((String)$billingStreet));
        $request->setCountryCode(strtoupper($billingAddress->getCountryCode()));
        $request->setPostCode((String)$billingAddress->getPostcode());
        $request->setTown((String)$billingAddress->getCity());

        if (!empty($pref_lang)) {
            $request->setLanguage($pref_lang);
        }

        if ($billingAddress->getCompany()) {
            $request->setCompanyName1($billingAddress->getCompany());
        }

        $request->setTelephonePrivate((String)trim($billingAddress->getPhoneNumber(), '-'));
        $request->setEmail((String)$customer->getEmail());

        $extraInfo["Name"] = 'ORDERCLOSED';
        $extraInfo["Value"] = $orderClosed;
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERAMOUNT';
        $extraInfo["Value"] = number_format($payment->getAmount() / 100, 2, '.', '');
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'ORDERCURRENCY';
        $extraInfo["Value"] = $payment->getCurrencyCode();
        $request->setExtraInfo($extraInfo);

        $extraInfo["Name"] = 'IP';
        $extraInfo["Value"] = DataHelper::getClientIp();
        $request->setExtraInfo($extraInfo);

        if (!empty($b2b_uid)) {
            $extraInfo["Name"] = 'REGISTERNUMBER';
            $extraInfo["Value"] = $b2b_uid;
            $request->setExtraInfo($extraInfo);
        }

        if (!empty($_SESSION["BYJUNO_TMX"]) && $config["tmx_enabled"] == "yes") {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = $_SESSION["BYJUNO_TMX"];
            $request->setExtraInfo($extraInfo);
        }
        /*
        $sedId = $this->_checkoutSession->getTmxSession();
        if ($this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/tmxenabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1' && !empty($sedId)) {
            $extraInfo["Name"] = 'DEVICE_FINGERPRINT_ID';
            $extraInfo["Value"] = $sedId;
            $request->setExtraInfo($extraInfo);
        }
        if ($paymentmethod->getAdditionalInformation('payment_send') == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        */
        if (!empty($shippingAddress)) {
            $shippingStreet = $shippingAddress->getStreet();

            $extraInfo["Name"] = 'DELIVERY_FIRSTLINE';
            $extraInfo["Value"] = trim((String)$shippingStreet);
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_HOUSENUMBER';
            $extraInfo["Value"] = '';
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_COUNTRYCODE';
            $extraInfo["Value"] = strtoupper($shippingAddress->getCountryCode());
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_POSTCODE';
            $extraInfo["Value"] = $shippingAddress->getPostcode();
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'DELIVERY_TOWN';
            $extraInfo["Value"] = $shippingAddress->getCity();
            $request->setExtraInfo($extraInfo);

            if ($shippingAddress->getCompany() != '' /*&& $this->_scopeConfig->getValue('byjunocheckoutsettings/byjuno_setup/businesstobusiness', \Magento\Store\Model\ScopeInterface::SCOPE_STORE) == '1'*/) {

                $extraInfo["Name"] = 'DELIVERY_COMPANYNAME';
                $extraInfo["Value"] = $shippingAddress->getCompany();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = '';
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $shippingAddress->getCompany();
                $request->setExtraInfo($extraInfo);

            } else {

                $extraInfo["Name"] = 'DELIVERY_FIRSTNAME';
                $extraInfo["Value"] = $shippingAddress->getFirstname();
                $request->setExtraInfo($extraInfo);

                $extraInfo["Name"] = 'DELIVERY_LASTNAME';
                $extraInfo["Value"] = $shippingAddress->getLastname();
                $request->setExtraInfo($extraInfo);
            }
        }

        $extraInfo["Name"] = 'PP_TRANSACTION_NUMBER';
        $extraInfo["Value"] = $requestId;
        $request->setExtraInfo($extraInfo);

        if ($b2b) {
            $extraInfo["Name"] = 'PAYMENTMETHOD';
            $extraInfo["Value"] = DataHelper::mapMethod($config["payment_method_b2b"]);
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'REPAYMENTTYPE';
            $extraInfo["Value"] = $config["repayment_type_b2b"];
            $request->setExtraInfo($extraInfo);
        } else {
            $extraInfo["Name"] = 'PAYMENTMETHOD';
            $extraInfo["Value"] = DataHelper::mapMethod($config["payment_method_b2c"]);
            $request->setExtraInfo($extraInfo);

            $extraInfo["Name"] = 'REPAYMENTTYPE';
            $extraInfo["Value"] = $config["repayment_type_b2c"];
            $request->setExtraInfo($extraInfo);
        }

        if (!empty($orderId)) {
            $extraInfo["Name"] = 'ORDERID';
            $extraInfo["Value"] = $orderId;
            $request->setExtraInfo($extraInfo);
        }

        if ($riskOwner != "") {
            $extraInfo["Name"] = 'RISKOWNER';
            $extraInfo["Value"] = $riskOwner;
            $request->setExtraInfo($extraInfo);
        }

        if ($invoiceDelivery == 'postal') {
            $extraInfo["Name"] = 'PAPER_INVOICE';
            $extraInfo["Value"] = 'YES';
            $request->setExtraInfo($extraInfo);
        }

        if ($transactionNumber != "") {
            $extraInfo["Name"] = 'TRANSACTIONNUMBER';
            $extraInfo["Value"] = $transactionNumber;
            $request->setExtraInfo($extraInfo);
        }
        if ($isCDP != "") {
            $extraInfo["Name"] = 'MESSAGETYPESPEC';
            $extraInfo["Value"] = 'CREDITCHECK';
            $request->setExtraInfo($extraInfo);
        }

        $extraInfo["Name"] = 'CONNECTIVTY_MODULE';
        $extraInfo["Value"] = 'Byjuno Sylius Module 1.0.0';
        $request->setExtraInfo($extraInfo);

        return $request;
    }

    public static function saveLog(EntityManagerInterface $em, ByjunoRequest $request, $xml_request, $xml_response, $status, $type)
    {
        /* @var $repo ByjunoLogRepository */
        $repo = $em->getRepository(ByjunoLog::class);
        $log = new ByjunoLog();
        $log->setRequestId($request->getRequestId());
        $log->setRequestType($type);
        $log->setFirstname($request->getFirstName());
        $log->setLastname($request->getLastName());
        $log->setIP($_SERVER['REMOTE_ADDR']);
        $log->setByjunoStatus((($status != "") ? $status . '' : 'Error'));
        $log->setXmlRequest($xml_request);
        $log->setXmlResponse($xml_response);
        $repo->add($log);
    }

}
