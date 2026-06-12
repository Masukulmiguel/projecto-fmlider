<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class ChatController
{
    public function conversations()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $isAdmin = OwnerScope::isAdmin($auth);

        if ($isAdmin) {
            $sql = "SELECT u.id, u.name, u.email, u.photo, u.role, u.position,
                    (SELECT message FROM chat_messages WHERE (sender_id = u.id AND receiver_id IS NULL) OR (sender_id IS NULL AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_message,
                    (SELECT created_at FROM chat_messages WHERE (sender_id = u.id AND receiver_id IS NULL) OR (sender_id IS NULL AND receiver_id = u.id) ORDER BY created_at DESC LIMIT 1) as last_at,
                    (SELECT COUNT(*) FROM chat_messages WHERE sender_id = u.id AND receiver_id IS NULL AND is_read = 0) as unread
                    FROM users u
                    WHERE u.role IN ('cliente', 'funcionario') AND u.status = 1
                    ORDER BY last_at DESC, u.name ASC";
        } else {
            $sql = "SELECT u.id, u.name, u.email, u.photo, u.role, NULL as position, NULL as last_message, NULL as last_at, 0 as unread
                    FROM users u WHERE u.role = 'admin' LIMIT 1";
        }

        $r = $db->query($sql);
        $rows = $r ? $r->fetch_all(MYSQLI_ASSOC) : [];
        Response::success(['conversations' => $rows]);
    }

    public function messages()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $isAdmin = OwnerScope::isAdmin($auth);
        $otherId = isset($_GET['user_id']) ? (int)$_GET['user_id'] : null;

        if ($isAdmin) {
            if (!$otherId) Response::error('user_id é obrigatório', 422);
            $stmt = $db->prepare("SELECT id, sender_id, receiver_id, message, is_read, created_at FROM chat_messages
                WHERE (sender_id = ? AND receiver_id IS NULL) OR (sender_id IS NULL AND receiver_id = ?)
                ORDER BY created_at ASC LIMIT 500");
            $stmt->bind_param('ii', $otherId, $otherId);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $mark = $db->prepare("UPDATE chat_messages SET is_read = 1 WHERE sender_id = ? AND receiver_id IS NULL AND is_read = 0");
            $mark->bind_param('i', $otherId);
            $mark->execute();
            $mark->close();
        } else {
            $stmt = $db->prepare("SELECT id, sender_id, receiver_id, message, is_read, created_at FROM chat_messages
                WHERE (sender_id = ? AND receiver_id IS NULL) OR (sender_id IS NULL AND receiver_id = ?)
                ORDER BY created_at ASC LIMIT 500");
            $stmt->bind_param('ii', $auth['user_id'], $auth['user_id']);
            $stmt->execute();
            $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();

            $mark = $db->prepare("UPDATE chat_messages SET is_read = 1 WHERE receiver_id = ? AND sender_id IS NULL AND is_read = 0");
            $mark->bind_param('i', $auth['user_id']);
            $mark->execute();
            $mark->close();
        }

        Response::success(['messages' => $rows]);
    }

    public function send()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $isAdmin = OwnerScope::isAdmin($auth);
        $data = Response::input();
        $message = trim($data['message'] ?? '');
        if ($message === '') Response::error('Mensagem vazia', 422);
        if (mb_strlen($message) > 2000) Response::error('Mensagem demasiado longa', 422);

        $senderRole = $auth['role'] ?? 'cliente';
        $senderName = $auth['name'] ?? ($senderRole === 'admin' ? 'Administrador' : 'Utilizador');

        if ($isAdmin) {
            $receiverId = (int)($data['receiver_id'] ?? 0);
            if (!$receiverId) Response::error('receiver_id é obrigatório', 422);
            $check = $db->prepare('SELECT id, name, role FROM users WHERE id = ? AND role IN ("cliente","funcionario")');
            $check->bind_param('i', $receiverId);
            $check->execute();
            $rcv = $check->get_result()->fetch_assoc();
            $check->close();
            if (!$rcv) Response::error('Destinatário não encontrado', 404);

            $stmt = $db->prepare('INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (NULL, ?, ?)');
            $stmt->bind_param('is', $receiverId, $message);
            $stmt->execute();
            $msgId = $stmt->insert_id;
            $stmt->close();

            $notif = $db->prepare("INSERT INTO notifications (user_id, type, title, body, link, icon) VALUES (?, 'message', ?, ?, ?, 'bi-chat-dots-fill')");
            $title = 'Nova mensagem do Administrador';
            $preview = mb_substr($message, 0, 100);
            $link = $rcv['role'] === 'funcionario' ? '/funcionario/mensagens' : '/dashboard';
            $notif->bind_param('isss', $receiverId, $title, $preview, $link);
            $notif->execute();
            $notif->close();
        } else {
            $stmt = $db->prepare('INSERT INTO chat_messages (sender_id, receiver_id, message) VALUES (?, NULL, ?)');
            $stmt->bind_param('is', $auth['user_id'], $message);
            $stmt->execute();
            $msgId = $stmt->insert_id;
            $stmt->close();

            $admins = $db->query("SELECT id FROM users WHERE role = 'admin'");
            if ($admins) {
                $notif = $db->prepare("INSERT INTO notifications (user_id, type, title, body, link, icon) VALUES (?, 'message', ?, ?, ?, 'bi-chat-dots-fill')");
                $prefix = $senderRole === 'funcionario' ? 'Funcionário' : 'Cliente';
                $title = "Nova mensagem de {$prefix} {$senderName}";
                $preview = mb_substr($message, 0, 100);
                $link = '/admin/mensagens';
                while ($a = $admins->fetch_assoc()) {
                    $notif->bind_param('isss', $a['id'], $title, $preview, $link);
                    $notif->execute();
                }
                $notif->close();
            }
        }

        Response::success(['message_id' => $msgId], 'Mensagem enviada');
    }
}
