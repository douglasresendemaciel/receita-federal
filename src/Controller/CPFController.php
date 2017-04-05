<?php

namespace DouglasResende\ReceitaFederal\Controller;

use App\Http\Controllers\Controller;
use DouglasResende\ReceitaFederal\Request\CPFRequest;
use DouglasResende\ReceitaFederal\Traits\ProcessTrait;
use Illuminate\Support\Facades\File;

class CPFController extends Controller
{
    use ProcessTrait;

    private $file;

    public function __construct()
    {
        @session_start();
        $this->file = storage_path('app/receita-federal/' . session_id() . '_cpf');
    }

    public function index(CPFRequest $request)
    {
        $data = $request->all();

        $birthday =  new \DateTime($data['birthday']);

        if (!File::exists($this->file)) {
            return false;
        } else {
            $file = fopen($this->file, 'r');
            $content = null;
            while (!feof($file)) {
                $content .= fread($file, 1024);
            }
            fclose($file);

            $contentExploded = explode(chr(9), $content);

            $sessionName = trim($contentExploded[count($contentExploded) - 2]);
            $sessionId = trim($contentExploded[count($contentExploded) - 1]);

            $cookie = $sessionName . '=' . $sessionId . ';flag=1';
        }

        $post = array
        (
            'txtTexto_captcha_serpro_gov_br' => $data['captcha'],
            'tempTxtCPF' => $data['cpf'],
            'tempTxtNascimento' => date_format($birthday, 'd/m/Y'),
            'temptxtToken_captcha_serpro_gov_br' => '',
            'temptxtTexto_captcha_serpro_gov_br' => $data['captcha']
        );

        $post = http_build_query($post, NULL, '&');

        $ch = curl_init('https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaSituacao/ConsultaPublicaExibir.asp');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_SSLVERSION, 3);
        curl_setopt($ch, CURLOPT_REFERER, 'https://www.receita.fazenda.gov.br/Aplicacoes/SSL/ATCTA/CPF/ConsultaSituacao/ConsultaPublica.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);
        $html = utf8_encode(curl_exec($ch));
        curl_close($ch);

        $data = $this->parseHtmlCPF($html);

        if ($request->ajax())
            return response()->json($data);
        else
            return response($data);
    }
}