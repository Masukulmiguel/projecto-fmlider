<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class DashboardController
{
    public function stats()
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth)) Response::error('Apenas admin', 403);

        $db = Database::connection();
        $days = (int)($_GET['days'] ?? 30);
        $days = max(1, min(365, $days));
        $since = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $totalClients = (int)($db->query("SELECT COUNT(*) as n FROM users WHERE role = 'cliente'")->fetch_assoc()['n'] ?? 0);
        $activeClients = (int)($db->query("SELECT COUNT(*) as n FROM users WHERE role = 'cliente' AND approval_status = 'approved'")->fetch_assoc()['n'] ?? 0);
        $pendingClients = (int)($db->query("SELECT COUNT(*) as n FROM users WHERE role = 'cliente' AND approval_status = 'pending'")->fetch_assoc()['n'] ?? 0);
        $newClientsThisMonth = (int)($db->query("SELECT COUNT(*) as n FROM users WHERE role = 'cliente' AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)")->fetch_assoc()['n'] ?? 0);

        $totalVisitors = (int)($db->query("SELECT COUNT(*) as n FROM visitors")->fetch_assoc()['n'] ?? 0);
        $uniqueSessions = (int)($db->query("SELECT COUNT(DISTINCT session_id) as n FROM visitors")->fetch_assoc()['n'] ?? 0);
        $visitorsToday = (int)($db->query("SELECT COUNT(*) as n FROM visitors WHERE DATE(visited_at) = CURDATE()")->fetch_assoc()['n'] ?? 0);

        $totalMessages = (int)($db->query("SELECT COUNT(*) as n FROM chat_messages")->fetch_assoc()['n'] ?? 0);
        $messagesToday = (int)($db->query("SELECT COUNT(*) as n FROM chat_messages WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['n'] ?? 0);
        $unreadMessages = (int)($db->query("SELECT COUNT(*) as n FROM chat_messages WHERE receiver_id IS NULL AND is_read = 0")->fetch_assoc()['n'] ?? 0);

        $totalEmbarques = (int)($db->query("SELECT COUNT(*) as n FROM embarques")->fetch_assoc()['n'] ?? 0);
        $totalCotacoes = (int)($db->query("SELECT COUNT(*) as n FROM cotacoes")->fetch_assoc()['n'] ?? 0);
        $totalDocumentos = (int)($db->query("SELECT COUNT(*) as n FROM documentos")->fetch_assoc()['n'] ?? 0);

        $clientsByDay = [];
        $r = $db->query("SELECT DATE(created_at) as d, COUNT(*) as n FROM users WHERE role = 'cliente' AND created_at >= '{$since}' GROUP BY DATE(created_at) ORDER BY d ASC");
        if ($r) while ($row = $r->fetch_assoc()) $clientsByDay[] = $row;

        $visitorsByDay = [];
        $r = $db->query("SELECT DATE(visited_at) as d, COUNT(*) as n FROM visitors WHERE visited_at >= '{$since}' GROUP BY DATE(visited_at) ORDER BY d ASC");
        if ($r) while ($row = $r->fetch_assoc()) $visitorsByDay[] = $row;

        $messagesByDay = [];
        $r = $db->query("SELECT DATE(created_at) as d, COUNT(*) as n FROM chat_messages WHERE created_at >= '{$since}' GROUP BY DATE(created_at) ORDER BY d ASC");
        if ($r) while ($row = $r->fetch_assoc()) $messagesByDay[] = $row;

        $visitorsByCountry = [];
        $r = $db->query("SELECT country, COUNT(*) as n FROM visitors GROUP BY country ORDER BY n DESC LIMIT 10");
        if ($r) while ($row = $r->fetch_assoc()) $visitorsByCountry[] = $row;

        $recentClients = [];
        $r = $db->query("SELECT u.id, u.name, u.email, u.approval_status, u.created_at, c.company_name FROM users u LEFT JOIN companies c ON c.user_id = u.id WHERE u.role = 'cliente' ORDER BY u.created_at DESC LIMIT 5");
        if ($r) while ($row = $r->fetch_assoc()) $recentClients[] = $row;

        $recentMessages = [];
        $r = $db->query("SELECT m.id, m.message, m.created_at, m.is_read, u.id as sender_id, u.name as sender_name FROM chat_messages m
            LEFT JOIN users u ON u.id = m.sender_id
            WHERE m.receiver_id IS NULL ORDER BY m.created_at DESC LIMIT 5");
        if ($r) while ($row = $r->fetch_assoc()) $recentMessages[] = $row;

        Response::success([
            'clients' => [
                'total' => $totalClients,
                'active' => $activeClients,
                'pending' => $pendingClients,
                'new_this_month' => $newClientsThisMonth,
            ],
            'visitors' => [
                'total' => $totalVisitors,
                'unique_sessions' => $uniqueSessions,
                'today' => $visitorsToday,
            ],
            'messages' => [
                'total' => $totalMessages,
                'today' => $messagesToday,
                'unread' => $unreadMessages,
            ],
            'embarques' => $totalEmbarques,
            'cotacoes' => $totalCotacoes,
            'documentos' => $totalDocumentos,
            'charts' => [
                'clients_by_day' => $clientsByDay,
                'visitors_by_day' => $visitorsByDay,
                'messages_by_day' => $messagesByDay,
                'visitors_by_country' => $visitorsByCountry,
            ],
            'recent' => [
                'clients' => $recentClients,
                'messages' => $recentMessages,
            ],
        ]);
    }
}
