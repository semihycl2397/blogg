<?php

namespace App\Models\ArkSigner;
use const base64FileData;
use const contentBase64String;
use const customKey;
use const EntegrationType;
use const FileName;
use const identityNo;
use const isAddSignedInfoToNormalProcess;
use const Phone;
use const SessionValue;
use const Sign;
use const SignLocation;
use const SignTur;
use const SignType;
use const SignUsage;

class ViaDocument
{
    public $base64FileData;
    public $Sign;
    public $SignType;
    public $SignTur;
    public $SignUsage;
    public $FileName;
    public $customKey;
    public $identityNo;
    public $EntegrationType;
    public $Phone;
    public $contentBase64String;
    public $SessionValue;
    public $SignLocation;
    public $isAddSignedInfoToNormalProcess;

    function setBase64FileData($base64Data)
    {
        $this->base64FileData = $base64Data;
    }

    function getBase64FileData()
    {
        return $this . base64FileData;
    }

    function setSign($sign)
    {
        $this->Sign = $sign;
    }

    function getSign()
    {
        return $this . Sign;
    }

    function setSignType($signType)
    {
        $this->SignType = $signType;
    }

    function getSignType()
    {
        return $this . SignType;
    }

    function setSignTur($signTur)
    {
        $this->SignTur = $signTur;
    }

    function getSignTur()
    {
        return $this . SignTur;
    }

    function setSignUsage($signUsage)
    {
        $this->SignUsage = $signUsage;
    }

    function getSignUsage()
    {
        return $this . SignUsage;
    }

    function setFileName($fileName)
    {
        $this->FileName = $fileName;
    }

    function getFileName()
    {
        return $this . FileName;
    }

    function setcustomKey($key)
    {
        $this->customKey = $key;
    }

    function getcustomKey()
    {
        return $this . customKey;
    }

    function setidentityNo($identity)
    {
        $this->identityNo = $identity;
    }

    function getidentityNo()
    {
        return $this . identityNo;
    }

    function setEntegrationType($entegrationType)
    {
        $this->EntegrationType = $entegrationType;
    }

    function getEntegrationType()
    {
        return $this . EntegrationType;
    }

    function setPhone($phone)
    {
        $this->Phone = $phone;
    }

    function getPhone()
    {
        return $this . Phone;
    }

    function setcontentBase64String($contentBase64)
    {
        $this->contentBase64String = $contentBase64;
    }

    function getcontentBase64String()
    {
        return $this . contentBase64String;
    }

    function setSessionValue($sessionValue)
    {
        $this->SessionValue = $sessionValue;
    }

    function getSessionValue()
    {
        return $this . SessionValue;
    }

    function setSignLocation($signLocation)
    {
        $this->SignLocation = $signLocation;
    }

    function getSignLocation()
    {
        return $this . SignLocation;
    }

    function setisAddSignedInfoToNormalProcess($addSignedInfo)
    {
        $this->isAddSignedInfoToNormalProcess = $addSignedInfo;
    }

    function getisAddSignedInfoToNormalProcess()
    {
        return $this . isAddSignedInfoToNormalProcess;
    }
}
