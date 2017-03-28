<?php

namespace DouglasResende\ReceitaFederal\Controller;

use App\Http\Controllers\Controller;
use DouglasResende\ReceitaFederal\Traits\ProcessTrait;
use Illuminate\Support\Facades\File;

class CaptchaController extends Controller
{
    use ProcessTrait;

    private $file;
    private $directory;

    /**
     * CaptchaController constructor.
     */
    public function __construct()
    {
        @session_start();
        $this->directory = storage_path('app/receita-federal/');
        $this->file = $this->directory . session_id();
    }

    public function index($document = 'cpf')
    {
        //trans('ReceiteFederal::cpf.line');
        switch ($document) {
            case 'cpf':

                $this->file = $this->file . '_cpf';
                $url = 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaSituacao/captcha/gerarCaptcha.asp';
                break;
            case 'cnpj':

                $this->file = $this->file . '_cnpj';
                $url = 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/captcha/gerarCaptcha.asp';
                break;

            default:
                throw new \Exception('invalid document type');
        }

        if (!File::exists($this->directory))
            File::makeDirectory($this->directory);

        if (!File::exists($this->file)) {
            $file = fopen($this->file, 'w+');
            fclose($file);
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $imgsource = curl_exec($ch);
        curl_close($ch);

        if (!empty($imgsource)) {
            $img = imagecreatefromstring($imgsource);
            header('Content-type: image/jpg');
            imagejpeg($img);
        }

        // --------------- aqui abaixo hack para consulta de cnpj.-----------
        if ($document == 'cnpj') {

            $file = fopen($this->file, 'r');
            $content = null;
            while (!feof($file)) {
                $content .= fread($file, 1024);
            }
            fclose($file);

            $contentExp = explode(chr(9), $content);

            $sessionName = trim($contentExp[count($contentExp) - 2]);
            $sessionId = trim($contentExp[count($contentExp) - 1]);

            $cookie = $sessionName . '=' . $sessionId;

            $ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);    // dados do arquivo de cookie
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);    // dados do arquivo de cookie
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $html = curl_exec($ch);
            curl_close($ch);
        }
    }
}