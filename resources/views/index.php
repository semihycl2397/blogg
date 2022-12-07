<?php

print('<div style="text-align:center;"><h1><strong>ArkSigner PHP-VIA Entegrasyon Örneği </strong></h1><hr/></div><br />');
print ('Php dosyasının bulunduğu dizine test.pdf adında bir dosya kopyalayarak, imzalama işlemini test edebilirsiniz.<br />');

// imzalamak istediğiniz dosyanın yolu
$fileName = "test.pdf";

// servis url'si
$viaUrl = "https://viatest-service.arksigner.com";

$serviceUrl = "https://viatest.arksigner.com/";

// via tarafından tanımlanmış olan unique appId değeri
$appId = "a6ff9168-3dbb-4541-8166-f7d13a068b60";

// viada tanımlı appId ile eşleşen şifre
$pass = "12345";

// imzalama işleminden sonra servisin sizin uygulamanıza döneceği url
$callbackUrl = "http://localhost/arkphp_demo/signed.php";

// imzalama işlemi iptal edilirse  servisin sizin uygulamanıza döneceği url
$cancelUrl = "http://localhost/arkphp_demo/abortsign.php";

echo "<strong>Dosya Adı: </strong>", $fileName , '<br />';

echo "<form method='POST'><input type='submit' id='btnSign' name='btnSign' value='İMZALA' /></form>";

if(array_key_exists('btnSign',$_POST)){
    sign($fileName, $viaUrl, $serviceUrl, $appId, $pass, $callbackUrl, $cancelUrl);
}

// bu metod ile, viaservis aracılığıyla imzalama işlemi yapılacaktır.
function sign($fileName, $viaUrl, $serviceUrl, $appId, $pass, $callbackUrl, $cancelUrl){
    $service = new ViaServiceRequest();
    // servis requestine appId değeriniz eklenmelidir ki via uygulaması yetkilendirme yapabilsin.
    $service->setAppId($appId);

    // şifreniz ve appId değeri eşleşmez ise, via yetkilendirme hatası dönecektir.
    $service->setPass($pass);

    // via, imzalama işlemi yapılsın veya iptal edilsin gibi durumlarda uygulamanıza döneceği adresi bilmelidir.
    $service->setcallBackUrl($callbackUrl);
    $service->setcancelUrl($cancelUrl);

    // uygulamanızda ingilizce dil desteği varsa buraya "en-US" da geçebilirsiniz, bu sayede via türkçe-ingilizce görüntülenebilecektir.
    $service->setlang('tr-TR');

    // isteğin yapılacağı url, yani via adresi
    $service->setserviceUrl($viaUrl);

    // via servisi, imzalayacağı dokümanları bir liste halinde bekler, bir yada birden fazla doküman göndererek imzalama işlemi yapılabilir.
    $service->signDocuments = [];

    $document = new ViaDocument();
    // imzalanacak verinin base64 hali
    $document->setBase64FileData(getFileBase64($fileName));

    // atılacak imza türü (CAdES-PAdES)
    $document->setSign("CAdES");
    // imza atacak kişinin tc kimlik numarası
    $document->setidentityNo("11111111111");

    // atılacak imzanın geçerlilik süresi, (BES-EST-XL-LTV)
    $document->setSignType("BES");
    // imzanın seri - paralel mi atılacağı (Serial / Parallel)
    $document->setSignTur("Serial");

    // belge ile imzası birlikte mi ayrık mı?
    $document->setSignUsage("true");

    // imzalanacak dosyanın adı
    $document->setFileName("test.pdf");

    // null geçilebilir
    $document->setEntegrationType(null);

    // imza ayrı şekilde atılacaksa, content olarak gönderilecek verinin base64 hali gerekir.
    $document->setcontentBase64String(null);

    // null geçilebilir
    $document->setSessionValue(null);

    // belgeye "e-imzalıdır" ibaresi eklensin mi?
    $document->setisAddSignedInfoToNormalProcess(true);
    $key = sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
    // random guid
    $document->setcustomKey($key);

    // imzalanacak belge servis isteğine ekleniyor.
    $service->signDocuments[0] = $document;

    $curl = curl_init();

    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($service));

    // servis üzerinde imzalama task'ı oluşturulacağını belirtiyoruz.
    curl_setopt($curl, CURLOPT_URL, $viaUrl . "/CreateSigningTask");
    // isteğin json kabul ettiğini bildiriyoruz
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

    try{
        // oluşturulan request çalıştırılıyor ve sonuç alınıyor.
        $response = curl_exec($curl);
        $result = json_decode($response);

        $success = $result->{'success'};

        if	(!$success){
            echo $result->{'message'};
            return;
        }

        // bu istek sonucunda bize, bir transactionUuid, ve bir Url dönülerek via 'ya yönlendirme yapılıyor.
        // bu yönlendirme, 5070 sayısı e-imza kanunu gereğince belgeyi imzalamadan önce mutlaka görmeniz gerektiğinden ötürü,
        // belgenin önizlemesini vermek amacıyla yapılıyor.
        $responseUrl = $result->{'url'};

        // aynı zamanda dönen transactionUUID değeri, via tarafının hangi belge için imzalama işlemine gelindiğini
        // bilmesini sağlıyor. Unique olan bu değer ile, via kendi tarafında belge bilgilerini önizleme verebiliyor, imzalama işlemini yapabiliyor.
        $transactionUuid = $result->{'transactionUUID'};

        // ilk çalıştırılan isteğin sonucunda dosyayı imzalamak için yönlendirme yapılacak adrese form submit ediliyor.
        // buradan sonra, via tarafı bize, callbackUrl veya cancelUrl değerlerinde belirttiğimiz sayfalara dönüyor.
        // iptal için abortsign.php, imzalandıktan sonrası için signed.php
        echo "<html><body onload='document.forms[1].submit()'>" .
            "<form method='POST' action='" . $serviceUrl . '/' . $responseUrl .".aspx" . "'>" .
            "<input type='hidden' name='documentUUID' value='" . $transactionUuid . "'>" .
            "</form></body></html>";
    }
    catch (Exception $ex){
        echo 'Hata2: ', $ex->getMessage();
    }

    curl_close($curl);
}

function getFileBase64($fileName){
    try{
        //convert file to base64Data
        $b64Doc = chunk_split(base64_encode(file_get_contents($fileName)));
    }
    catch (Exception $ex){
        echo 'Hata => ' , $ex->getMessage();
        $b64Doc = "";
    }

    return $b64Doc;
}

class ViaServiceRequest
{
    public $appId ;
    public $pass ;
    public $callBackUrl ;
    public $cancelUrl ;
    public $serviceUrl ;
    public $connectorServiceAddress ;
    public $lang ;
    public $signDocuments;

    function setAppId($id){
        $this->appId = $id;
    }

    function getAppId() {
        return $this->appId;
    }

    function setPass($password){
        $this->pass = $password;
    }

    function getPass() {
        return $this->pass;
    }


    function setcallBackUrl($cbURL){
        $this->callBackUrl = $cbURL;
    }

    function getcallBackUrl() {
        return $this->callBackUrl;
    }

    function setcancelUrl($cancelURL){
        $this->cancelUrl= $cancelURL;
    }

    function getcancelUrl() {
        return $this->cancelUrl;
    }

    function setserviceUrl($svcURL){
        $this->serviceUrl = $svcURL;
    }

    function getserviceUrl() {
        return $this->serviceUrl;
    }

    function setconnectorServiceAddress($connSvcAddress){
        $this->connectorServiceAddress = $connSvcAddress;
    }

    function getconnectorServiceAddress() {
        return $this->connectorServiceAddress;
    }

    function setlang($lng){
        $this->lang = $lng;
    }

    function getlang() {
        return $this->lang;
    }

    function setsignDocuments($id){
        $this->appId = $id;
    }

    function getsignDocuments() {
        return $this->signDocuments;
    }
}
class ViaDocument
{
    public $base64FileData ;
    public $Sign ;
    public $SignType ;
    public $SignTur ;
    public $SignUsage ;
    public $FileName ;
    public $customKey ;
    public $identityNo ;
    public $EntegrationType ;
    public $Phone ;
    public $contentBase64String ;
    public $SessionValue ;
    public $SignLocation ;
    public $isAddSignedInfoToNormalProcess ;

    function setBase64FileData($base64Data) {
        $this->base64FileData = $base64Data;
    }

    function getBase64FileData (){
        return $this.base64FileData;
    }

    function setSign($sign) {
        $this->Sign = $sign;
    }

    function getSign (){
        return $this.Sign;
    }

    function setSignType($signType) {
        $this->SignType = $signType;
    }

    function getSignType (){
        return $this.SignType;
    }

    function setSignTur($signTur) {
        $this->SignTur = $signTur;
    }

    function getSignTur (){
        return $this.SignTur;
    }

    function setSignUsage($signUsage) {
        $this->SignUsage = $signUsage;
    }

    function getSignUsage (){
        return $this.SignUsage;
    }

    function setFileName($fileName) {
        $this->FileName = $fileName;
    }

    function getFileName (){
        return $this.FileName;
    }

    function setcustomKey($key) {
        $this->customKey = $key;
    }

    function getcustomKey (){
        return $this.customKey;
    }

    function setidentityNo($identity) {
        $this->identityNo = $identity;
    }

    function getidentityNo (){
        return $this.identityNo;
    }

    function setEntegrationType($entegrationType) {
        $this->EntegrationType = $entegrationType;
    }

    function getEntegrationType (){
        return $this.EntegrationType;
    }

    function setPhone($phone) {
        $this->Phone = $phone;
    }

    function getPhone (){
        return $this.Phone;
    }

    function setcontentBase64String($contentBase64) {
        $this->contentBase64String = $contentBase64;
    }

    function getcontentBase64String (){
        return $this.contentBase64String;
    }

    function setSessionValue($sessionValue) {
        $this->SessionValue = $sessionValue;
    }

    function getSessionValue (){
        return $this.SessionValue;
    }

    function setSignLocation($signLocation) {
        $this->SignLocation = $signLocation;
    }

    function getSignLocation (){
        return $this.SignLocation;
    }

    function setisAddSignedInfoToNormalProcess($addSignedInfo) {
        $this->isAddSignedInfoToNormalProcess = $addSignedInfo;
    }

    function getisAddSignedInfoToNormalProcess (){
        return $this.isAddSignedInfoToNormalProcess;
    }
}
?>
