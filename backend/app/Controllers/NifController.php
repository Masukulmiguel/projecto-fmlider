<?php

namespace App\Controllers;

use App\Helpers\Response;

class NifController
{
    private const PORTAL_URL = 'https://portaldocontribuinte.minfin.gov.ao';
    private const LOOKUP_PATH = '/consultar-nif-do-contribuinte';
    private const AJAX_PATH = '/consultar-headNifId-do-contribuinte';
    private const TIMEOUT = 15;

    private static $cache = [];

    public function lookup($nif)
    {
        $nif = trim($nif ?? '');

        if ($nif === '' || !preg_match('/^\d{10}$/', $nif)) {
            Response::error('NIF inválido. Deve conter exactamente 10 dígitos.', 422);
        }

        return $this->doLookup($nif);
    }

    public function lookupByQuery()
    {
        $nif = trim($_GET['nif'] ?? '');

        if ($nif === '' || !preg_match('/^\d{10}$/', $nif)) {
            Response::error('NIF inválido. Deve conter exactamente 10 dígitos.', 422);
        }

        return $this->doLookup($nif);
    }

    private function doLookup($nif)
    {

        $cacheKey = 'nif_' . $nif;
        if (isset(self::$cache[$cacheKey]) && self::$cache[$cacheKey]['expires'] > time()) {
            Response::success(self::$cache[$cacheKey]['data']);
        }

        $result = $this->scrapeNif($nif);

        if ($result === null) {
            Response::error('NIF não encontrado no portal da AGT.', 404);
        }

        self::$cache[$cacheKey] = [
            'data' => $result,
            'expires' => time() + 3600,
        ];

        Response::success($result);
    }

    private function scrapeNif($nif)
    {
        $cookieFile = tempnam(sys_get_temp_dir(), 'agt_cookie_');

        $viewState = $this->getViewState($cookieFile);
        if ($viewState === null) {
            return null;
        }

        $result = $this->postNifLookup($nif, $viewState, $cookieFile);

        @unlink($cookieFile);

        return $result;
    }

    private function getViewState($cookieFile)
    {
        $ch = curl_init(self::PORTAL_URL . self::LOOKUP_PATH);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            CURLOPT_ENCODING => '',
        ]);

        $html = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($html === false) {
            error_log('NIF Lookup: cURL GET failed: ' . $err);
            return null;
        }
        if ($code !== 200) {
            error_log('NIF Lookup: GET returned HTTP ' . $code);
            return null;
        }

        if (preg_match('/name="javax\.faces\.ViewState"[^>]*value="([^"]+)"/', $html, $m)) {
            return $m[1];
        }

        if (preg_match("/name='javax\.faces\.ViewState'[^>]*value='([^']+)'/", $html, $m)) {
            return $m[1];
        }

        error_log('NIF Lookup: ViewState not found. Page length: ' . strlen($html));
        return null;
    }

    private function postNifLookup($nif, $viewState, $cookieFile)
    {
        $postData = http_build_query([
            'javax.faces.partial.ajax' => 'true',
            'javax.faces.source' => 'j_id_2x:j_id_34',
            'javax.faces.partial.execute' => '@all',
            'javax.faces.partial.render' => 'showpanelNIF',
            'j_id_2x_SUBMIT' => '1',
            'j_id_2x:txtNIFNumber' => $nif,
            'j_id_2x:j_id_34' => 'j_id_2x:j_id_34',
            'javax.faces.ViewState' => $viewState,
        ]);

        $ch = curl_init(self::PORTAL_URL . self::AJAX_PATH);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postData,
            CURLOPT_COOKIEFILE => $cookieFile,
            CURLOPT_COOKIEJAR => $cookieFile,
            CURLOPT_TIMEOUT => self::TIMEOUT,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
            CURLOPT_ENCODING => '',
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
                'Faces-Request: partial/ajax',
                'X-Requested-With: XMLHttpRequest',
            ],
        ]);

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($response === false) {
            error_log('NIF Lookup: cURL POST failed: ' . $err);
            return null;
        }
        if ($code !== 200) {
            error_log('NIF Lookup: POST returned HTTP ' . $code . ' body: ' . substr($response ?? '', 0, 500));
            return null;
        }

        return $this->parseResponse($response);
    }

    private function parseResponse($xmlResponse)
    {
        $startMarker = '<update id="showpanelNIF"><![CDATA[';
        $startPos = strpos($xmlResponse, $startMarker);
        if ($startPos === false) {
            error_log('NIF Lookup: showpanelNIF CDATA start not found. Response length: ' . strlen($xmlResponse));
            error_log('NIF Lookup: Response preview: ' . substr($xmlResponse, 0, 500));
            return null;
        }
        $htmlStart = $startPos + strlen($startMarker);

        $endMarker = ']]></update>';
        $endPos = strpos($xmlResponse, $endMarker, $htmlStart);
        if ($endPos === false) {
            error_log('NIF Lookup: CDATA end marker not found');
            return null;
        }

        $html = substr($xmlResponse, $htmlStart, $endPos - $htmlStart);

        if (strpos($html, 'panel-default-header') === false) {
            if (preg_match('/ui-growl-message[^>]*>.*?<span[^>]*>([^<]+)<\/span>/s', $xmlResponse, $gMsg)) {
                error_log('NIF Lookup: Portal growl: ' . trim($gMsg[1]));
            }
            error_log('NIF Lookup: Results panel not found - NIF does not exist in AGT portal');
            return null;
        }

        $nif = $this->extractField($html, 'NIF:');
        $nome = $this->extractField($html, 'Nome:');
        $tipo = $this->extractField($html, 'Tipo:');
        $estado = $this->extractField($html, 'Estado:');
        $inadimplente = $this->extractField($html, 'Inadimplente:');
        $regimeIva = $this->extractField($html, 'Regime de IVA:');

        if ($nome === null || $nome === '') {
            error_log('NIF Lookup: Nome field not found. HTML length: ' . strlen($html));
            return null;
        }

        $residencia = null;
        if (preg_match('/Residente Fiscal<\/label>.*?<label[^>]*>([^<]+)<\/label>/s', $html, $m)) {
            $residencia = trim($m[1]);
        }

        return [
            'nif' => $nif,
            'nome' => $nome,
            'tipo' => $tipo,
            'estado' => $estado,
            'inadimplente' => $inadimplente,
            'regime_iva' => $regimeIva,
            'residencia_fiscal' => $residencia,
        ];
    }

    private function extractField($html, $label)
    {
        $pattern = preg_quote($label, '/') . '\s*<\/label>\s*<div[^>]*>\s*<label[^>]*>([^<]+)<\/label>';
        if (preg_match('/' . $pattern . '/s', $html, $m)) {
            return trim($m[1]);
        }
        return null;
    }
}
