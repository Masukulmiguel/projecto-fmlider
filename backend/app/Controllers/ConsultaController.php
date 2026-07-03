<?php

namespace App\Controllers;

use App\Helpers\Response;

class ConsultaController
{
    private const EDGAR_API = 'http://consulta.edgarsingui.ao/consultar';
    private const BUSCADOR_API = 'https://buscador.ao/search/document';
    private const TIMEOUT = 10;

    private static $cache = [];

    public function lookupBi($bi)
    {
        $bi = strtoupper(trim($bi ?? ''));

        if ($bi === '' || !preg_match('/^\d{9}[A-Z]{2}\d{3}$/', $bi)) {
            Response::error('BI inválido. Deve conter 14 caracteres (ex: 006151112LA041).', 422);
        }

        $cacheKey = 'bi_' . $bi;
        if (isset(self::$cache[$cacheKey]) && self::$cache[$cacheKey]['expires'] > time()) {
            Response::success(self::$cache[$cacheKey]['data']);
        }

        $result = $this->queryEdgarApi($bi);

        if ($result === null) {
            $result = $this->queryBuscadorApi($bi);
        }

        if ($result === null) {
            Response::error('BI não encontrado ou API indisponível.', 404);
        }

        self::$cache[$cacheKey] = [
            'data' => $result,
            'expires' => time() + 3600,
        ];

        Response::success($result);
    }

    public function lookupBiByQuery()
    {
        $bi = strtoupper(trim($_GET['bi'] ?? ''));

        if ($bi === '' || !preg_match('/^\d{9}[A-Z]{2}\d{3}$/', $bi)) {
            Response::error('BI inválido. Deve conter 14 caracteres (ex: 006151112LA041).', 422);
        }

        $cacheKey = 'bi_' . $bi;
        if (isset(self::$cache[$cacheKey]) && self::$cache[$cacheKey]['expires'] > time()) {
            Response::success(self::$cache[$cacheKey]['data']);
        }

        $result = $this->queryEdgarApi($bi);

        if ($result === null) {
            $result = $this->queryBuscadorApi($bi);
        }

        if ($result === null) {
            Response::error('BI não encontrado ou API indisponível.', 404);
        }

        self::$cache[$cacheKey] = [
            'data' => $result,
            'expires' => time() + 3600,
        ];

        Response::success($result);
    }

    private function queryEdgarApi($bi)
    {
        try {
            $ch = curl_init(self::EDGAR_API . '/' . urlencode($bi));
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'FMLider-App/1.0',
                CURLOPT_ENCODING => '',
            ]);

            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $code !== 200) {
                error_log('ConsultaController: Edgar API failed HTTP ' . $code);
                return null;
            }

            $data = json_decode($response, true);
            if ($data === null || isset($data['error']) && $data['error'] === true) {
                return null;
            }

            return [
                'nome' => $data['name'] ?? null,
                'bi' => $bi,
                'fonte' => 'Edgar Singui API',
            ];
        } catch (\Exception $e) {
            error_log('ConsultaController: Edgar API exception: ' . $e->getMessage());
            return null;
        }
    }

    private function queryBuscadorApi($bi)
    {
        try {
            $url = self::BUSCADOR_API . '?' . http_build_query(['type' => 'BI', 'number' => $bi]);
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => self::TIMEOUT,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'FMLider-App/1.0',
                CURLOPT_ENCODING => '',
            ]);

            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($response === false || $code !== 200) {
                error_log('ConsultaController: Buscador API failed HTTP ' . $code);
                return null;
            }

            $data = json_decode($response, true);
            if ($data === null || !isset($data['data'])) {
                return null;
            }

            return [
                'nome' => $data['data']['name'] ?? null,
                'bi' => $data['data']['bi'] ?? $bi,
                'fonte' => 'Buscador.ao',
            ];
        } catch (\Exception $e) {
            error_log('ConsultaController: Buscador API exception: ' . $e->getMessage());
            return null;
        }
    }
}
