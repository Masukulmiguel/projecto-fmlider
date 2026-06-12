<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class NotificationController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $onlyUnread = !empty($_GET['unread']);

        $sql = "SELECT id, type, title, body, link, icon, is_read, created_at FROM notifications WHERE user_id = ?";
        if ($onlyUnread) $sql .= " AND is_read = 0";
        $sql .= " ORDER BY created_at DESC LIMIT 50";

        $stmt = $db->prepare($sql);
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        Response::success(['notifications' => $rows]);
    }

    public function unreadCount()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) as n FROM notifications WHERE user_id = ? AND is_read = 0');
        $stmt->bind_param('i', $auth['user_id']);
        $stmt->execute();
        $n = $stmt->get_result()->fetch_assoc()['n'] ?? 0;
        $stmt->close();
        Response::success(['count' => (int)$n]);
    }

    public function markRead($id = null)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $data = Response::input();
        $ids = $data['ids'] ?? null;
        $all = $data['all'] ?? false;

        if ($all) {
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0');
            $stmt->bind_param('i', $auth['user_id']);
        } elseif (is_array($ids) && !empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $types = str_repeat('i', count($ids) + 1);
            $params = $ids;
            array_unshift($params, $auth['user_id']);
            $stmt = $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = ? AND id IN ($placeholders)");
            $stmt->bind_param($types, ...$params);
        } elseif ($id) {
            $stmt = $db->prepare('UPDATE notifications SET is_read = 1 WHERE user_id = ? AND id = ?');
            $stmt->bind_param('ii', $auth['user_id'], $id);
        } else {
            Response::error('Nada para marcar', 422);
        }
        $stmt->execute();
        $stmt->close();
        Response::success([], 'Marcado como lido');
    }
}
