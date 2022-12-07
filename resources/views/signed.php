<?php
// via tarafı bize imza sonrasında dönerken, parametre olarak
// success, documentUUID, downloadUrl, downloadAction ve downloadParameter değerlerini döner,
// bunları okuyarak, download isteği oluşturulmalıdır.
$serviceUrl = "https://viatest.arksigner.com/";
$success = $_GET["success"];
$docUuid = $_GET["documentUUID"];
$downloadUrl = $_GET["downloadUrl"];
$downloadAction = $_GET["downloadAction"];
$downloadParameter = $_GET["downloadParameter"];

if (!$success){
    echo $_GET["errorMessage"];
}
else {
    // imzalama işlemi başarılı iken dosyanın indirilebilmesi sağlanmalıdır.
    echo 'Belgeniz imzalandı...';
    echo "<form method='POST'><input type='submit' id='btnDownload' name='btnDownload' value='İmzalı Belgeyi İndir'/></form>";

    if(array_key_exists('btnDownload',$_POST)){
        download($docUuid, $downloadUrl, $serviceUrl, $downloadAction, $downloadParameter);
    }
}


function download($docUuid, $downloadUrl, $serviceUrl, $downloadAction, $downloadParameter){
    // via uygulamasının imza sonrası bize döndüğü değerler ile download yapabilmek için url adresi oluşturuluyor
    $url = $serviceUrl . "/" . $downloadUrl . "?action=" . $downloadAction;
    // parametre olacak eklenecek veri oluşturuluyor
    $parameter = "documentUUID=" . $docUuid;

    $download = curl_init();
    curl_setopt($download, CURLOPT_URL, $url);
    curl_setopt($download, CURLOPT_POSTFIELDS, $parameter);
    curl_setopt($download, CURLOPT_BINARYTRANSFER, true);
    curl_setopt($download, CURLOPT_RETURNTRANSFER, true);

    // istek oluşturuluyor. Bu istek sonucunda bize, json olarak belgenin adı, base64 verisi gibi bilgiler dönüyor, bu bilgiler
    // kullanılarak imza sonrası yapılmak istenen işlemler yapılabilir.
    $response = curl_exec($download);
    $signedFile = json_decode($response)[0]->{"base64FileData"};

    echo 'İmzalı Belgenin Base64 Hali : </br>' . $signedFile;
}
?>
