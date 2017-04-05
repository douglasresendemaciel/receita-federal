<?php

namespace DouglasResende\ReceitaFederal\Controller;

use App\Http\Controllers\Controller;
use DouglasResende\ReceitaFederal\Request\CNPJRequest;
use DouglasResende\ReceitaFederal\Traits\ProcessTrait;
use Illuminate\Support\Facades\File;

class CNPJController extends Controller
{
    use ProcessTrait;

    private $file;

    public function __construct()
    {
        @session_start();
        $this->file = storage_path('app/receita-federal/' . session_id() . '_cnpj');
    }

    public function index(CNPJRequest $request)
    {
        $data = $request->all();

        if (!File::exists($this->file)) {
            return false;
        } else {
            // pega os dados de sessão gerados na visualização do captcha dentro do cookie
            $file = fopen($this->file, 'r');
            $conteudo = null;
            while (!feof($file)) {
                $conteudo .= fread($file, 1024);
            }
            fclose($file);

            $explodir = explode(chr(9), $conteudo);

            $sessionName = trim($explodir[count($explodir) - 2]);
            $sessionId = trim($explodir[count($explodir) - 1]);

            // se não tem falg	1 no cookie então acrescenta
            if (!strstr($conteudo, 'flag	1')) {
                // linha que deve ser inserida no cookie antes da consulta cnpj
                // observações argumentos separados por tab (chr(9)) e new line no final e inicio da linha (chr(10))
                // substitui dois chr(10) padrão do cookie para separar cabecario do conteudo , adicionando o conteudo $linha , que tb inicia com dois chr(10)
                $linha = chr(10) . chr(10) . 'www.receita.fazenda.gov.br	FALSE	/pessoajuridica/cnpj/cnpjreva/	FALSE	0	flag	1' . chr(10);
                // novo cookie com o flag=1 dentro dele , antes da linha de sessionname e sessionid
                $novo_cookie = str_replace(chr(10) . chr(10), $linha, $conteudo);

                // apaga o cookie antigo
                unlink($this->file);

                // cria o novo cookie , com a linha flag=1 inserida
                $file = fopen($this->file, 'w');
                fwrite($file, $novo_cookie);
                fclose($file);
            }

            // constroe o parâmetro de sessão que será passado no próximo curl
            $cookie = $sessionName . '=' . $sessionId . ';flag=1';
        }

        // dados que serão submetidos a consulta por post
        $post = array
        (
            'submit1' => 'Consultar',
            'origem' => 'comprovante',
            'cnpj' => $data['cnpj'],
            'txtTexto_captcha_serpro_gov_br' => $data['captcha'],
            'search_type' => 'cnpj'

        );

        $post = http_build_query($post, NULL, '&');

        $ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/valida.asp');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);        // aqui estão os campos de formulário
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);    // dados do arquivo de cookie
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);    // dados do arquivo de cookie
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');
        curl_setopt($ch, CURLOPT_COOKIE, $cookie);        // dados de sessão e flag=1
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 3);
        curl_setopt($ch, CURLOPT_REFERER, 'http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_Solicitacao2.asp');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_0);
        $html = utf8_encode(curl_exec($ch));
        curl_close($ch);

        $data = $this->parseHtmlCNPJ($html);

        if ($data['status'] == true) {

            $ch = curl_init('http://www.receita.fazenda.gov.br/pessoajuridica/cnpj/cnpjreva/Cnpjreva_qsa.asp');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, []);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0');

            curl_setopt($ch, CURLOPT_COOKIEFILE, $this->file);    // dados do arquivo de cookie
            curl_setopt($ch, CURLOPT_COOKIEJAR, $this->file);    // dados do arquivo de cookie
            curl_setopt($ch, CURLOPT_COOKIE, $cookie);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);

            $html = utf8_encode(curl_exec($ch));
            curl_close($ch);

            $data['capital'] = $this->parseHtmlCNPJPartners($html);
        }

        if ($request->ajax())
            return response()->json($data);
        else
            return response($data);
    }
}