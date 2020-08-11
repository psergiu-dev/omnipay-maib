<?php


namespace PsergiuDev\OmnipayMaib\Message;


use Omnipay\Common\Message\AbstractResponse;
use Omnipay\Common\Message\RedirectResponseInterface;

class Response extends AbstractResponse implements RedirectResponseInterface
{

    public function isSuccessful(): bool
    {
        return @$this->data['RESULT'] === 'OK';
    }

    public function getTransactionId()
    {
        return @$this->data['TRANSACTION_ID'];
    }

    public function getTransactionReference()
    {
        return @$this->data['TRANSACTION_ID'];
    }

    public function getRedirectUrl()
    {
        return @$this->data['redirect_url'];
    }

    public function isRedirect(): bool
    {
        return isset($this->data['redirect_url']);
    }
}