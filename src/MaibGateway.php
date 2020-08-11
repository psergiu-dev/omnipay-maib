<?php

namespace PsergiuDev\OmnipayMaib;

use Omnipay\Common\AbstractGateway;
use Omnipay\Common\Http\ClientInterface;
use Omnipay\Common\Message\RequestInterface;
use PsergiuDev\OmnipayMaib\Exceptions\NotSupportedException;
use PsergiuDev\OmnipayMaib\Message\CloseDayRequest;
use PsergiuDev\OmnipayMaib\Message\CompletePurchaseRequest;
use PsergiuDev\OmnipayMaib\Message\RefundRequest;
use Symfony\Component\HttpFoundation\Request as HttpRequest;
use PsergiuDev\OmnipayMaib\Message\PurchaseRequest;

class MaibGateway extends AbstractGateway
{
    /**
     * @var Ecomm
     */
    protected $maib;

    public function __construct(ClientInterface $httpClient = null, HttpRequest $httpRequest = null, Ecomm $ecomm = null)
    {
        $this->maib = $ecomm ?: MaibConfiguration::ecomm();
        parent::__construct($httpClient, $httpRequest);
    }


    public function getName(): string
    {
        return 'Maib';
    }

    /**
     * Get the gateway parameters.
     *
     * @return array
     */
    public function getDefaultParameters(): array
    {
        return [
            'language' => 'ru',
        ];
    }

    protected function createRequest($class, array $parameters)
    {
        $obj = new $class($this->httpClient, $this->httpRequest, $this->maib);

        return $obj->initialize(array_replace($this->getParameters(), $parameters));
    }

    public function getMerchantCertificate()
    {
        return $this->getParameter('merchant_certificate');
    }

    public function setMerchantCertificate($value): MaibGateway
    {
        return $this->setParameter('merchant_certificate', $value);
    }

    public function getMerchantCertificatePassword()
    {
        return $this->getParameter('merchant_certificate_password');
    }

    public function setMerchantCertificatePassword($value): MaibGateway
    {
        return $this->setParameter('merchant_certificate_password', $value);
    }


    public function purchase(array $parameters = [])
    {
        return $this->createRequest(PurchaseRequest::class, $parameters);
    }


    public function completePurchase(array $parameters = [])
    {
        return $this->createRequest(CompletePurchaseRequest::class, $parameters);
    }

    public function refund(array $parameters = [])
    {
        return $this->createRequest(RefundRequest::class, $parameters);
    }

    public function closeDay(array $parameters = [])
    {
        return $this->createRequest(CloseDayRequest::class, $parameters);
    }

    /**
     * @param array $parameters
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function authorize(array $parameters = [])
    {
        throw new NotSupportedException('The authorize method is not supported by maib');
    }

    /**
     * @return bool
     */
    public function supportsAuthorize(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function completeAuthorize(array $options = [])
    {
        throw new NotSupportedException('The completeAuthorize method is not supported by maib');
    }

    /**
     * @return bool
     */
    public function supportsCompleteAuthorize(): bool
    {
        return false;
    }

    /**
     * @param array $parameters
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function capture(array $parameters = [])
    {
        throw new NotSupportedException('The capture method is not supported by maib');
    }

    /**
     * @return bool
     */
    public function supportsCapture(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function fetchTransaction(array $options = [])
    {
        throw new NotSupportedException('The fetchTransaction method is not supported by maib');
    }

    public function supportsFetchTransaction(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function void(array $options = [])
    {
        throw new NotSupportedException('The void method is not supported by maib');
    }

    public function supportsVoid(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function createCard(array $options = [])
    {
        throw new NotSupportedException('The createCard method is not supported by maib');
    }

    public function supportsCreateCard(): bool
    {
        return false;
    }


    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function updateCard(array $options = [])
    {
        throw new NotSupportedException('The updateCard method is not supported by maib');
    }

    public function supportsUpdateCard(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function deleteCard(array $options = [])
    {
        throw new NotSupportedException('The deleteCard method is not supported by maib');
    }

    public function supportsDeleteCard(): bool
    {
        return false;
    }

    /**
     * @param array $options
     *
     * @return RequestInterface|void
     * @throws NotSupportedException
     */
    public function acceptNotification(array $options = [])
    {
        throw new NotSupportedException('The acceptNotification method is not supported by maib');
    }

    public function supportsAcceptNotification(): bool
    {
        return false;
    }

    public function setMerchantUrl($value): MaibGateway
    {
        return $this->setParameter('merchant_url', $value);
    }

    public function getMerchantUrl()
    {
        return $this->getParameter('merchant_url');
    }

    public function setClientUrl($value): MaibGateway
    {
        return $this->setParameter('client_url', $value);
    }

    public function getClientUrl()
    {
        return $this->getParameter('client_url');
    }
}