<?php

namespace DouglasResende\ReceitaFederal\Traits;

/**
 * Class ProcessTrait
 * @package DouglasResende\ReceitaFederal\Traits
 */
trait ProcessTrait
{
    protected function getContent($start, $end, $total)
    {
        $content = str_replace($start, '', str_replace(strstr(strstr($total, $start), $end), '', strstr($total, $start)));
        return $content;
    }

    public function parseHtmlCPF($html)
    {
        $fields = [
            'No do CPF:',
            'Nome da Pessoa Física:',
            'Data de Nascimento:',
            'Situação Cadastral:',
            'Data da Inscrição:'
        ];

        $especialChars = [
            chr(9),
            chr(10),
            chr(13),
            '&nbsp;',
            '  '
        ];

        $html = str_replace('<br /><br />', '<br />', str_replace($especialChars, '', strip_tags($html, '<b><br>')));

        $html3 = $html;
        for ($i = 0; $i < count($fields); $i++) {
            $html2 = strstr($html, $fields[$i]);
            $result[] = trim($this->getContent($fields[$i], '<br />', $html2));
            $html = $html2;
        }

        if (!$result[0]) {
            if (strstr($html3, 'CPF incorreto')) {
                $result['message'] = trans('receita::cpf.invalid-document');
                $result['status'] = false;
            } else if (strstr($html3, 'não existe em nossa base de dados')) {
                $result['message'] = trans('receita::cpf.dont-exist');
                $result['status'] = false;
            } else if (strstr($html3, 'Os caracteres da imagem não foram preenchidos corretamente')) {
                $result['message'] = trans('receita::captcha.wrong-captcha');
                $result['status'] = false;
            } else {
                $result['message'] = trans('receita::cpf.invalid-information');
                $result['status'] = false;
            }
        } else {
            $result['message'] = trans('receita::cpf.success');
            $result['status'] = true;
        }
        return $result;
    }

    public function parseHtmlCNPJ($html)
    {
        $fields = [
            'NÚMERO DE INSCRIÇÃO',
            'DATA DE ABERTURA',
            'NOME EMPRESARIAL',
            'TÍTULO DO ESTABELECIMENTO (NOME DE FANTASIA)',
            'CÓDIGO E DESCRIÇÃO DA ATIVIDADE ECONÔMICA PRINCIPAL',
            'CÓDIGO E DESCRIÇÃO DAS ATIVIDADES ECONÔMICAS SECUNDÁRIAS',
            'CÓDIGO E DESCRIÇÃO DA NATUREZA JURÍDICA',
            'LOGRADOURO',
            'NÚMERO',
            'COMPLEMENTO',
            'CEP',
            'BAIRRO/DISTRITO',
            'MUNICÍPIO',
            'UF',
            'ENDEREÇO ELETRÔNICO',
            'TELEFONE',
            'ENTE FEDERATIVO RESPONSÁVEL (EFR)',
            'SITUAÇÃO CADASTRAL',
            'DATA DA SITUAÇÃO CADASTRAL',
            'MOTIVO DE SITUAÇÃO CADASTRAL',
            'SITUAÇÃO ESPECIAL',
            'DATA DA SITUAÇÃO ESPECIAL'
        ];
        $especialChars = [
            chr(9),
            chr(10),
            chr(13),
            '&nbsp;',
            '</b>',
            '  ',
            '<b>MATRIZ<br>',
            '<b>FILIAL<br>'
        ];
        $html = str_replace('<br><b>', '<b>', str_replace($especialChars, '', strip_tags($html, '<b><br>')));
        $html3 = $html;
        for ($i = 0; $i < count($fields); $i++) {
            $html2 = strstr($html, $fields[$i]);
            $result[] = trim($this->getContent($fields[$i] . '<b>', '<br>', $html2));
            $html = $html2;
        }
        if (strstr($result[5], '<b>')) {
            $secondaryCNAE = explode('<b>', $result[5]);
            $result[5] = $secondaryCNAE;
            unset($secondaryCNAE);
        } else
            $result[5] = [$result[5]];

        if (!$result[0]) {
            if (strstr($html3, 'O número do CNPJ não é válido')) {
                $result['message'] = trans('receita::cnpj.invalid-document');
                $result['status'] = false;
            } else {
                $result['message'] = trans('receita::captcha.wrong-captcha');
                $result['status'] = false;
            }
        } else {
            $result['message'] = trans('receita::cnpj.success');
            $result['status'] = true;
        }

        return $result;
    }

    public function parseHtmlCNPJPartners($html)
    {
        $fields = [
            'NOME EMPRESARIAL:',
            'CAPITAL SOCIAL:',
            'O Quadro de Sócios e Administradores(QSA) constante da base de dados do Cadastro Nacional da Pessoa Jurídica (CNPJ) é o seguinte:'
        ];
        $especialChars = [
            chr(9),
            chr(10),
            chr(13),
            '&nbsp;',
            '</b>',
            '  ',
        ];
        $html = str_replace('<br><b>', '<b>', str_replace($especialChars, '', strip_tags($html, '<b><br>')));
        $html3 = $html;
        $result = [];
        for ($i = 0; $i < count($fields); $i++) {
            $html2 = strstr($html, $fields[$i]);

            if ($i == 2) {
                $html2 = str_replace($fields[$i], '', $html2);
                $partners = explode('<b>', $html2);;
                $part = [];
                foreach ($partners as $key => $partner) {
                    $name = trim($this->getContent('Nome/Nome Empresarial:', '<b>', $partner));
                    $type = trim($this->getContent('Qualificação:', '<b>', $partner));
                    if (!empty($name))
                        $part[$key][] = $name;

                    if (!empty($type))
                        $part[$key - 1][] = $type;
                }
                $result[] = array_values($part);
            } else {
                $result[] = trim($this->getContent($fields[$i], '<b>', $html2));
            }
            $html = $html2;
        }

        if (!$result[0]) {
            $result = [];
            if (strstr($html3, 'atender a sua solicitação')) {
                $result['message'] = trans('receita::cnpj.no-partners');
                $result['status'] = false;
            } else {
                $result['message'] = trans('receita::captcha.wrong-captcha');
                $result['status'] = false;
            }
        } else {
            $result['message'] = trans('receita::cnpj.success');
            $result['status'] = true;
        }

        return $result;
    }
}