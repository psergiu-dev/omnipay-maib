<?php


namespace PsergiuDev\OmnipayMaib\Message;

use Omnipay\Common\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\AbstractRequest as BaseAbstractRequest;
use PsergiuDev\OmnipayMaib\Ecomm;

abstract class AbstractRequest extends BaseAbstractRequest
{
    /**
     * @var Ecomm
     */
    protected $maib;

    public function __construct(ClientInterface $httpClient, HttpRequest $httpRequest, Ecomm $maib)
    {
        $this->maib = $maib;
        parent::__construct($httpClient, $httpRequest);
    }

    private function configure(): void
    {
        $this->maib->setMerchantUrl($this->getMerchantUrl());
        $this->maib->setClientUrl($this->getClientUrl());
        $this->maib->setMerchantCertificate($this->getMerchantCertificate());
        $this->maib->setMerchantCertificatePassword($this->getMerchantCertificatePassword());
    }

    public function setMerchantUrl($value): AbstractRequest
    {
        return $this->setParameter('merchant_url', $value);
    }

    public function getMerchantUrl()
    {
        return $this->getParameter('merchant_url');
    }

    public function setClientUrl($value): AbstractRequest
    {
        return $this->setParameter('client_url', $value);
    }

    public function getClientUrl()
    {
        return $this->getParameter('client_url');
    }

    public function getMerchantCertificate()
    {
        return $this->getParameter('merchant_certificate');
    }

    public function setMerchantCertificate($value): AbstractRequest
    {
        return $this->setParameter('merchant_certificate', $value);
    }

    public function getMerchantCertificatePassword()
    {
        return $this->getParameter('merchant_certificate_password');
    }

    public function setMerchantCertificatePassword($value): AbstractRequest
    {
        return $this->setParameter('merchant_certificate_password', $value);
    }

    /**
     * Set the correct configuration sending
     *
     * @return ResponseInterface
     */
    public function send(): ResponseInterface
    {
        $this->configure();

        return parent::send();
    }

    protected function createResponse($data, $isSuccessful = null, $additionalResultCodes = []): Response
    {
        if (isset($data['TRANSACTION_ID']) && !isset($data[''])) {
            $data = array_merge($data, [
                'redirect_url' => $this->maib->getRedirectUrl($data['TRANSACTION_ID'])
            ]);
        }
        if (!is_null($isSuccessful)) {
            $data['isSuccessful'] = $isSuccessful;
        }
        if ($additionalResultCodes) {
            $data['additionalResultCodes'] = $additionalResultCodes;
        }

        return $this->response = new Response($this, $data);
    }
}