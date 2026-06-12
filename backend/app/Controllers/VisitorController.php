<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class VisitorController
{
    public function track()
    {
        $data = Response::input();
        $db = Database::connection();
        $auth = null;
        try { $auth = OwnerScope::userFromToken(); } catch (\Throwable $e) {}

        $sessionId = $_COOKIE['fml_sid'] ?? bin2hex(random_bytes(16));
        if (!isset($_COOKIE['fml_sid'])) {
            setcookie('fml_sid', $sessionId, time() + 86400 * 30, '/');
        }

        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '';
        if (str_contains($ip, ',')) $ip = trim(explode(',', $ip)[0]);
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        [$browser, $browserVer] = $this->parseBrowser($userAgent);
        [$os] = $this->parseOS($userAgent);
        $device = $this->parseDevice($userAgent);

        $country = $data['country'] ?? null;
        $city = $data['city'] ?? null;
        $region = $data['region'] ?? null;

        if (!$country && $ip && $ip !== '::1' && !str_starts_with($ip, '127.') && !str_starts_with($ip, '192.168.') && !str_starts_with($ip, '10.')) {
            $geo = $this->geolocate($ip);
            if ($geo) {
                $country = $geo['country'] ?? null;
                $city = $geo['city'] ?? null;
                $region = $geo['region'] ?? null;
            }
        }
        if (!$country) $country = 'Desconhecido';

        $userId = $auth ? $auth['user_id'] : null;
        $pageUrl = mb_substr((string)($data['page_url'] ?? ''), 0, 500);
        $referrer = mb_substr((string)($data['referrer'] ?? ''), 0, 500);

        $stmt = $db->prepare("INSERT INTO visitors (session_id, user_id, ip_address, country, city, region, browser, browser_version, os, device_type, referrer, page_url, user_agent) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('sisssssssssss',
            $sessionId, $userId, $ip, $country, $city, $region, $browser, $browserVer, $os, $device, $referrer, $pageUrl, $userAgent
        );
        $stmt->execute();
        $stmt->close();

        Response::success(['tracked' => true, 'session_id' => $sessionId]);
    }

    public function stats()
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth)) Response::error('Apenas admin', 403);
        $db = Database::connection();
        $days = (int)($_GET['days'] ?? 30);
        $days = max(1, min(365, $days));
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $total = (int)($db->query("SELECT COUNT(*) as n FROM visitors")->fetch_assoc()['n'] ?? 0);
        $unique = (int)($db->query("SELECT COUNT(DISTINCT session_id) as n FROM visitors")->fetch_assoc()['n'] ?? 0);
        $today = (int)($db->query("SELECT COUNT(*) as n FROM visitors WHERE DATE(visited_at) = CURDATE()")->fetch_assoc()['n'] ?? 0);
        $logged = (int)($db->query("SELECT COUNT(DISTINCT user_id) as n FROM visitors WHERE user_id IS NOT NULL")->fetch_assoc()['n'] ?? 0);

        $byDay = [];
        $r = $db->query("SELECT DATE(visited_at) as d, COUNT(*) as n FROM visitors WHERE visited_at >= '{$since}' GROUP BY DATE(visited_at) ORDER BY d ASC");
        if ($r) while ($row = $r->fetch_assoc()) $byDay[] = $row;

        $byCountry = [];
        $r = $db->query("SELECT country, COUNT(*) as n FROM visitors GROUP BY country ORDER BY n DESC LIMIT 10");
        if ($r) while ($row = $r->fetch_assoc()) $byCountry[] = $row;

        $byDevice = [];
        $r = $db->query("SELECT device_type, COUNT(*) as n FROM visitors GROUP BY device_type ORDER BY n DESC");
        if ($r) while ($row = $r->fetch_assoc()) $byDevice[] = $row;

        $byBrowser = [];
        $r = $db->query("SELECT browser, COUNT(*) as n FROM visitors GROUP BY browser ORDER BY n DESC LIMIT 8");
        if ($r) while ($row = $r->fetch_assoc()) $byBrowser[] = $row;

        $recent = [];
        $r = $db->query("SELECT v.id, v.ip_address, v.country, v.city, v.browser, v.os, v.device_type, v.page_url, v.visited_at, u.name as user_name
            FROM visitors v LEFT JOIN users u ON u.id = v.user_id
            ORDER BY v.visited_at DESC LIMIT 30");
        if ($r) while ($row = $r->fetch_assoc()) $recent[] = $row;

        Response::success([
            'total' => $total,
            'unique_sessions' => $unique,
            'today' => $today,
            'logged_users' => $logged,
            'by_day' => $byDay,
            'by_country' => $byCountry,
            'by_device' => $byDevice,
            'by_browser' => $byBrowser,
            'recent' => $recent,
        ]);
    }

    private function geolocate($ip)
    {
        $url = "http://ip-api.com/json/{$ip}?fields=status,country,regionName,city";
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 5,
        ]);
        $body = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code !== 200 || !$body) return null;
        $data = json_decode($body, true);
        if (($data['status'] ?? '') !== 'success') return null;
        return $data;
    }

    private function parseBrowser($ua)
    {
        $browser = 'Desconhecido'; $ver = '';
        if (preg_match('/Edg\/([\d.]+)/', $ua, $m)) { $browser = 'Edge'; $ver = $m[1]; }
        elseif (preg_match('/OPR\/([\d.]+)/', $ua, $m)) { $browser = 'Opera'; $ver = $m[1]; }
        elseif (preg_match('/Firefox\/([\d.]+)/', $ua, $m)) { $browser = 'Firefox'; $ver = $m[1]; }
        elseif (preg_match('/Chrome\/([\d.]+)/', $ua, $m)) { $browser = 'Chrome'; $ver = $m[1]; }
        elseif (preg_match('/Safari\/([\d.]+)/', $ua, $m)) { $browser = 'Safari'; $ver = $m[1]; }
        return [$browser, $ver];
    }

    private function parseOS($ua)
    {
        if (preg_match('/Windows NT ([\d.]+)/', $ua, $m)) return ['Windows ' . str_replace(['10.0','6.3','6.2','6.1'], ['10/11','8.1','8','7'], $m[1])];
        if (preg_match('/Mac OS X ([\d_]+)/', $ua, $m)) return ['macOS ' . str_replace('_', '.', $m[1])];
        if (preg_match('/Android ([\d.]+)/', $ua, $m)) return ['Android ' . $m[1]];
        if (preg_match('/iPhone OS ([\d_]+)/', $ua, $m)) return ['iOS ' . str_replace('_', '.', $m[1])];
        if (preg_match('/Linux/', $ua)) return ['Linux'];
        return ['Desconhecido'];
    }

    private function parseDevice($ua)
    {
        if (preg_match('/(tablet|ipad|playbook|silk)/i', $ua)) return 'tablet';
        if (preg_match('/(mobi|iphone|ipod|android.*mobile)/i', $ua)) return 'mobile';
        return 'desktop';
    }
}
