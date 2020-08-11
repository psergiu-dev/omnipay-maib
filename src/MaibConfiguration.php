<?php


namespace PsergiuDev\OmnipayMaib;


use Money\Currencies\ISOCurrencies;
use Money\Currency;

class MaibConfiguration
{
    public static $global = [];

    public static function ecomm(): Ecomm
    {
        $res = new Ecomm();

        if (isset(self::$global['merchant_url'])) {
            $res->setMerchantUrl(self::$global['merchant_url']);
        }

        if (isset(self::$global['client_url'])) {
            $res->setClientUrl(self::$global['client_url']);
        }

        if (isset(self::$global['merchant_certificate'])) {
            $res->setMerchantCertificate(self::$global['merchant_certificate']);
        }

        if (isset(self::$global['merchant_certificate_password'])) {
            $res->setMerchantCertificatePassword(self::$global['merchant_certificate_password']);
        }

        if (isset(self::$global['currency'])) {
            $currencies = new ISOCurrencies();
            $currencyCode = $currencies->numericCodeFor(new Currency(self::$global['currency']));
            $res->setCurrencyCode($currencyCode);
        }

        return $res;
    }


    public static function merchantCertificate($value = null)
    {
        if (empty($value)) {
            return self::$global['merchant_certificate'];
        }
        self::$global['merchant_certificate'] = $value;
    }

    public static function merchantCertificatePassword($value = null)
    {
        if (empty($value)) {
            return self::$global['merchant_certificate_password'];
        }
        self::$global['merchant_certificate_password'] = $value;
    }

    public static function currency($value = null)
    {
        if (empty($value)) {
            return self::$global['currency'];
        }
        self::$global['currency'] = $value;
    }

    public static function merchantUrl($value = null)
    {
        if (empty($value)) {
            return self::$global['merchant_url'];
        }
        self::$global['merchant_url'] = $value;
    }

    public static function clientUrl($value = null)
    {
        if (empty($value)) {
            return self::$global['client_url'];
        }
        self::$global['client_url'] = $value;
    }
}