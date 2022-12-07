<?php

namespace App\Models\ArkSigner;
class ViaServiceRequest
{
    public $appId;
    public $pass;
    public $callBackUrl;
    public $cancelUrl;
    public $serviceUrl;
    public $connectorServiceAddress;
    public $lang;
    public $signDocuments;

    function setAppId($id)
    {
        $this->appId = $id;
    }

    function getAppId()
    {
        return $this->appId;
    }

    function setPass($password)
    {
        $this->pass = $password;
    }

    function getPass()
    {
        return $this->pass;
    }


    function setcallBackUrl($cbURL)
    {
        $this->callBackUrl = $cbURL;
    }

    function getcallBackUrl()
    {
        return $this->callBackUrl;
    }

    function setcancelUrl($cancelURL)
    {
        $this->cancelUrl = $cancelURL;
    }

    function getcancelUrl()
    {
        return $this->cancelUrl;
    }

    function setserviceUrl($svcURL)
    {
        $this->serviceUrl = $svcURL;
    }

    function getserviceUrl()
    {
        return $this->serviceUrl;
    }

    function setconnectorServiceAddress($connSvcAddress)
    {
        $this->connectorServiceAddress = $connSvcAddress;
    }

    function getconnectorServiceAddress()
    {
        return $this->connectorServiceAddress;
    }

    function setlang($lng)
    {
        $this->lang = $lng;
    }

    function getlang()
    {
        return $this->lang;
    }

    function setsignDocuments($id)
    {
        $this->appId = $id;
    }

    function getsignDocuments()
    {
        return $this->signDocuments;
    }
}
