<?php

namespace App\Http\Controllers;

use App\Helpers\ArkSigner\FileConverter;
use App\Http\Controllers\Controller;
use App\Models\ArkSigner\ViaDocument;
use App\Models\ArkSigner\ViaServiceRequest;
use Dflydev\DotAccessData\Data;
use Illuminate\Support\Env;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class İmzaController extends Controller
{
    public function index(){
        return view('front.signature');
//        $ana_kategoriler = DB::table('works_main_categories')->get();
//        $temp = $ana_kategoriler[6];
//        $ana_kategoriler[6] = $ana_kategoriler[7];
//        $ana_kategoriler[7] = $temp;
//        $eserler = Works::select('works.*','puani','madde_no','faaliyet_alani')
//            ->join('works_score', 'works_score.id', '=', 'works.work_sub_category_id')
//            ->where('is_active','=',1)
//            ->where('works.user_id', Auth::id())
//            ->orderBy('works.work_sub_category_id')
//            ->orderByDesc('get_score_from_work')
//            ->orderBy('works.work_detail')
//            ->get();
//        $eser_alt_kategoriler_dizisi = [];
//        $ana_kategori_puanlari = [];
//        foreach ($ana_kategoriler as $index => $ana_kategori){
//            $ana_kategori_puanlari[$index] = 0;
//        }
//        $tum_eserler_toplam_puan = Works::where('user_id',Auth::id())->sum('get_score_from_work');
//
//
//        foreach ($eserler as $eser) {
//            //eserlerin tamamına bakarak Hangi alt kategorilere ait olduğunu ayıklıyor (Örneğin C1, A3 gibi)
//            $work_score = WorkScore::find($eser->work_sub_category_id);
//            $eser_alt_kategori_madde_no = $work_score->madde_no;
//            $eser_alt_kategori_madde_no = explode('.', $eser_alt_kategori_madde_no)[0];
//            if (!in_array($eser_alt_kategori_madde_no, $eser_alt_kategoriler_dizisi)) {
//                array_push($eser_alt_kategoriler_dizisi, $eser_alt_kategori_madde_no);
//            }
//            foreach ($ana_kategoriler as $key => $ana_kategori) {
//                if ($eser_alt_kategori_madde_no[0] == $ana_kategori->kategori_adi_bas_harfi) {
//                    $ana_kategori_puanlari[$key] += $eser->get_score_from_work;
//                }
//            }
//        }
//
//
//        return view('front.arksigner.calculate-point-pdf')->with(['ana_kategoriler' => $ana_kategoriler,
//                'eser_alt_kategoriler_dizisi'=>$eser_alt_kategoriler_dizisi,
//                'eserler'=>$eserler,
//                'tum_eserler_toplam_puan'=>$tum_eserler_toplam_puan,
//                'ana_kategori_puanlari'=>$ana_kategori_puanlari
//            ])->render();




//        $pdf = new Html2Pdf();
//        $html = view('front.signature')->render();
//        $pdf->writeHTML($html);
//        $pdf->output('test.pdf',);
//        return view('front.signature');
    }


    public function imzala($type){
        $signature = Auth::user()->getApplication->getSignature;
        if (!$signature){
            return redirect()->back();
        }

        switch ($type){
            case "application_letter":
                $fileName = public_path($signature->basvuru_dilekcesi);
                $callbackUrl = "http://127.0.0.1:8000/auth/ark-signer-imzala-success-scoring";// imzalama işleminden sonra servisin sizin uygulamanıza döneceği url
                break;

            case "scoring_table":
                $fileName = public_path($signature->puanlandırma_tablosu);
                $callbackUrl = "http://127.0.0.1:8000/auth/ark-signer-imzala-success-scoring";// imzalama işleminden sonra servisin sizin uygulamanıza döneceği url
                break;
            default:
                return redirect()->back();
        }
        $viaUrl = "https://via-service.arksigner.com";// via tarafından tanımlanmış olan unique appId değeri
        $serviceUrl = "https://via.arksigner.com/";
        $appId = "9168726f-0445-4684-aecd-ec884c418017"; // viada tanımlı appId ile eşleşen şifre
        $pass = "2bBu7Z53eC";
        $cancelUrl = "http://127.0.0.1:8000/auth/ark-signer-imzala-failed";


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
        $document->setBase64FileData(FileConverter::getFileBase64($fileName));

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
        $document->setFileName($fileName);

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

            return view('arksigner-redirect',compact('serviceUrl', 'responseUrl', 'transactionUuid'));
        }
        catch (Exception $ex){
            //@todo Hata loglara yazılacak
            return redirect()->back();
        }

        curl_close($curl);

    }

    public function success_application_letter(){
        $signature = Auth::user()->getApplication->getSignature;
        $serviceUrl = "https://via.arksigner.com/";
        $success = $_GET["success"];
        $docUuid = $_GET["documentUUID"];
        $downloadUrl = $_GET["downloadUrl"];
        $downloadAction = $_GET["downloadAction"];
        $downloadParameter = $_GET["downloadParameter"];

        if (!$success){
            echo $_GET["errorMessage"];
            //@todo Loglara yazılacak
            return redirect()->route('signature-index');
        }
        else {
            // imzalama işlemi başarılı iken dosyanın indirilebilmesi sağlanmalıdır.
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
            $pdf_decoded = base64_decode ($signedFile);
            //Write data back to pdf file
            $pdf = fopen (public_path('files/'.Auth::user()->id.'/arksigner/'.$signature->basvuru_dilekcesi).'.signed.pdf','w');
            fwrite ($pdf,$pdf_decoded);
            //close output file
            fclose ($pdf);
            $signature->basvuru_dilekcesi_imza_durumu = 1;
            $signature->basvuru_dilekcesi_base64text = $signedFile;
            $signature->basvuru_dilekcesi_imzali = $pdf;
            $signature->save();
            return redirect()->route('signature-index');

        }
    }

    public function success_scoring_table(){
        $signature = Auth::user()->getApplication->getSignature;
        $serviceUrl = "https://via.arksigner.com/";
        $success = $_GET["success"];
        $docUuid = $_GET["documentUUID"];
        $downloadUrl = $_GET["downloadUrl"];
        $downloadAction = $_GET["downloadAction"];
        $downloadParameter = $_GET["downloadParameter"];

        if (!$success){
            echo $_GET["errorMessage"];
            //@todo Loglara yazılacak
            return redirect()->route('signature-index');
        }
        else {
            // imzalama işlemi başarılı iken dosyanın indirilebilmesi sağlanmalıdır.
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
            $pdf_decoded = base64_decode ($signedFile);
            //Write data back to pdf file
            $pdf = fopen (public_path('files/'.Auth::user()->id.'/arksigner/'.$signature->puanlandırma_tablosu).'.signed.pdf','w');
            fwrite ($pdf,$pdf_decoded);
            //close output file
            fclose ($pdf);
            $signature->puanlandırma_tablosu_imza_durumu = 1;
            $signature->puanlandırma_tablosu_base64text = $signedFile;
            $signature->puanlandırma_tablosu_imzali = $pdf;
            $signature->save();
            return redirect()->route('signature-index');

        }
    }

    public function failed(){
        return redirect()->route('signature-index');
    }
}

