<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class EntregaController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $search = $_GET['q'] ?? null;
        $estado = $_GET['estado'] ?? null;
        $cliente_id = $_GET['cliente_id'] ?? null;
        $motorista_id = $_GET['motorista_id'] ?? null;
        $destino = $_GET['destino'] ?? null;
        $data_inicio = $_GET['data_inicio'] ?? null;
        $data_fim = $_GET['data_fim'] ?? null;

        $sql = "SELECT e.*, m.nome_completo as motorista_nome, c.matricula as camiao_matricula, c.codigo_interno as camiao_codigo,
                u.name as cliente_nome_user
                FROM entregas e
                LEFT JOIN motoristas m ON m.id = e.motorista_id
                LEFT JOIN camioes c ON c.id = e.camiao_id
                LEFT JOIN users u ON u.id = e.cliente_id
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!OwnerScope::isAdmin($auth) && ($auth['role'] ?? '') !== 'funcionario') {
            $sql .= " AND e.cliente_id = ?";
            $params[] = $auth['user_id'];
            $types .= 'i';
        } elseif (!empty($cliente_id)) {
            $sql .= " AND e.cliente_id = ?";
            $params[] = (int)$cliente_id;
            $types .= 'i';
        }

        if ($estado) { $sql .= " AND e.estado = ?"; $params[] = $estado; $types .= 's'; }
        if ($motorista_id) { $sql .= " AND e.motorista_id = ?"; $params[] = (int)$motorista_id; $types .= 'i'; }
        if ($destino) { $sql .= " AND e.destino LIKE ?"; $params[] = "%$destino%"; $types .= 's'; }
        if ($data_inicio) { $sql .= " AND e.created_at >= ?"; $params[] = $data_inicio; $types .= 's'; }
        if ($data_fim) { $sql .= " AND e.created_at <= ?"; $params[] = $data_fim . ' 23:59:59'; $types .= 's'; }
        if ($search) {
            $sql .= " AND (e.referencia_fmlider LIKE ? OR e.referencia_cliente LIKE ? OR e.numero_processo LIKE ? OR e.cliente_nome LIKE ? OR e.origem LIKE ? OR e.destino LIKE ? OR e.matricula LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like, $like, $like, $like, $like]);
            $types .= 'sssssss';
        }

        $sql .= " ORDER BY e.created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        foreach ($rows as &$row) {
            $stmt2 = $db->prepare("SELECT * FROM contentores WHERE entrega_id = ?");
            $stmt2->bind_param('i', $row['id']);
            $stmt2->execute();
            $row['contentores'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt2->close();
        }

        Response::success(['entregas' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare("SELECT e.*, m.nome_completo as motorista_nome, c.matricula as camiao_matricula, c.codigo_interno as camiao_codigo,
                u.name as cliente_nome_user
                FROM entregas e
                LEFT JOIN motoristas m ON m.id = e.motorista_id
                LEFT JOIN camioes c ON c.id = e.camiao_id
                LEFT JOIN users u ON u.id = e.cliente_id
                WHERE e.id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Entrega não encontrada', 404);

        $role = $auth['role'] ?? '';
        if (!OwnerScope::isAdmin($auth) && $role !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['cliente_id']);
        }

        $stmt2 = $db->prepare("SELECT * FROM contentores WHERE entrega_id = ?");
        $stmt2->bind_param('i', $id);
        $stmt2->execute();
        $row['contentores'] = $stmt2->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt2->close();

        $stmt3 = $db->prepare("SELECT * FROM historico_entregas WHERE entrega_id = ? ORDER BY created_at DESC");
        $stmt3->bind_param('i', $id);
        $stmt3->execute();
        $row['historico'] = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        Response::success(['entrega' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        $data = Response::input();

        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $referencia = OwnerScope::generateReference('ENT', 'entregas', 'referencia_fmlider');

        $db = Database::connection();
        $db->begin_transaction();

        try {
            $clienteId = !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null;
            $motoristaId = !empty($data['motorista_id']) ? (int)$data['motorista_id'] : null;
            $camiaoId = !empty($data['camiao_id']) ? (int)$data['camiao_id'] : null;
            $dataSaida = !empty($data['data_saida']) ? $data['data_saida'] : null;
            $dataPrevista = !empty($data['data_prevista']) ? $data['data_prevista'] : null;
            $dataEntrega = !empty($data['data_entrega']) ? $data['data_entrega'] : null;
            $userId = $auth['user_id'];

            $stmt = $db->prepare("INSERT INTO entregas (referencia_fmlider, referencia_cliente, numero_processo, tipologia, origem, destino, cliente_id, cliente_nome, motorista_id, camiao_id, matricula, estado, data_saida, data_prevista, data_entrega, observacoes, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $estado = $data['estado'] ?? 'pendente';
            $matricula = $data['matricula'] ?? null;

            $stmt->bind_param('ssssssiiiiisssssi',
                $referencia,
                $data['referencia_cliente'] ?? null,
                $data['numero_processo'] ?? null,
                $data['tipologia'] ?? null,
                $data['origem'] ?? null,
                $data['destino'] ?? null,
                $clienteId,
                $data['cliente_nome'] ?? null,
                $motoristaId,
                $camiaoId,
                $matricula,
                $estado,
                $dataSaida,
                $dataPrevista,
                $dataEntrega,
                $data['observacoes'] ?? null,
                $userId
            );

            if (!$stmt->execute()) {
                error_log('Database error (EntregaController store): ' . $stmt->error);
                $db->rollback();
                Response::error('Erro ao criar entrega', 500);
            }
            $entregaId = $stmt->insert_id;
            $stmt->close();

            if (!empty($data['contentores']) && is_array($data['contentores'])) {
                $stmt2 = $db->prepare("INSERT INTO contentores (entrega_id, numero, tipo, estado, data_entrega, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
                foreach ($data['contentores'] as $c) {
                    $cDataEntrega = !empty($c['data_entrega']) ? $c['data_entrega'] : null;
                    $stmt2->bind_param('isssss',
                        $entregaId,
                        $c['numero'] ?? null,
                        $c['tipo'] ?? null,
                        $c['estado'] ?? null,
                        $cDataEntrega,
                        $c['observacoes'] ?? null
                    );
                    if (!$stmt2->execute()) {
                        error_log('Database error (EntregaController store contentor): ' . $stmt2->error);
                        $db->rollback();
                        Response::error('Erro ao criar contentor', 500);
                    }
                }
                $stmt2->close();
            }

            $utilizadorNome = $auth['user_metadata']['name'] ?? $auth['name'] ?? 'Sistema';
            $stmt3 = $db->prepare("INSERT INTO historico_entregas (entrega_id, estado_anterior, estado_novo, utilizador_id, utilizador_nome, observacoes) VALUES (?, NULL, ?, ?, ?, ?)");
            $stmt3->bind_param('isis',
                $entregaId,
                $estado,
                $userId,
                $utilizadorNome,
                $data['observacoes'] ?? null
            );
            $stmt3->execute();
            $stmt3->close();

            $db->commit();
            Response::success(['entrega_id' => $entregaId, 'referencia_fmlider' => $referencia], 'Entrega criada');
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Exception (EntregaController store): ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT cliente_id FROM entregas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Entrega não encontrada', 404);

        $role = $auth['role'] ?? '';
        if (!OwnerScope::isAdmin($auth) && $role !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['cliente_id']);
        }

        $data = Response::input();
        $errors = $this->validate($data, true);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $db->begin_transaction();

        try {
            $clienteId = !empty($data['cliente_id']) ? (int)$data['cliente_id'] : null;
            $motoristaId = !empty($data['motorista_id']) ? (int)$data['motorista_id'] : null;
            $camiaoId = !empty($data['camiao_id']) ? (int)$data['camiao_id'] : null;
            $dataSaida = !empty($data['data_saida']) ? $data['data_saida'] : null;
            $dataPrevista = !empty($data['data_prevista']) ? $data['data_prevista'] : null;
            $dataEntrega = !empty($data['data_entrega']) ? $data['data_entrega'] : null;

            $stmt = $db->prepare("UPDATE entregas SET referencia_cliente=?, numero_processo=?, tipologia=?, origem=?, destino=?, cliente_id=?, cliente_nome=?, motorista_id=?, camiao_id=?, matricula=?, estado=?, data_saida=?, data_prevista=?, data_entrega=?, observacoes=? WHERE id=?");
            $stmt->bind_param('sssssiississssii',
                $data['referencia_cliente'] ?? null,
                $data['numero_processo'] ?? null,
                $data['tipologia'] ?? null,
                $data['origem'] ?? null,
                $data['destino'] ?? null,
                $clienteId,
                $data['cliente_nome'] ?? null,
                $motoristaId,
                $camiaoId,
                $data['matricula'] ?? null,
                $data['estado'] ?? 'pendente',
                $dataSaida,
                $dataPrevista,
                $dataEntrega,
                $data['observacoes'] ?? null,
                $id
            );
            if (!$stmt->execute()) {
                error_log('Database error (EntregaController update): ' . $stmt->error);
                $db->rollback();
                Response::error('Erro ao atualizar entrega', 500);
            }
            $stmt->close();

            if (isset($data['contentores']) && is_array($data['contentores'])) {
                $stmtDel = $db->prepare("DELETE FROM contentores WHERE entrega_id = ?");
                $stmtDel->bind_param('i', $id);
                $stmtDel->execute();
                $stmtDel->close();

                if (!empty($data['contentores'])) {
                    $stmt2 = $db->prepare("INSERT INTO contentores (entrega_id, numero, tipo, estado, data_entrega, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($data['contentores'] as $c) {
                        $cDataEntrega = !empty($c['data_entrega']) ? $c['data_entrega'] : null;
                        $stmt2->bind_param('isssss',
                            $id,
                            $c['numero'] ?? null,
                            $c['tipo'] ?? null,
                            $c['estado'] ?? null,
                            $cDataEntrega,
                            $c['observacoes'] ?? null
                        );
                        if (!$stmt2->execute()) {
                            error_log('Database error (EntregaController update contentor): ' . $stmt2->error);
                            $db->rollback();
                            Response::error('Erro ao atualizar contentor', 500);
                        }
                    }
                    $stmt2->close();
                }
            }

            $db->commit();
            Response::success([], 'Entrega atualizada');
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Exception (EntregaController update): ' . $e->getMessage());
            Response::error('Erro interno do servidor', 500);
        }
    }

    public function updateEstado($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $data = Response::input();

        $novoEstado = $data['estado'] ?? null;
        if (!$novoEstado) Response::error('Estado é obrigatório', 422);

        $validStates = ['pendente', 'em_preparacao', 'saiu_da_base', 'em_transporte', 'chegou_cliente', 'entregue', 'cancelado'];
        if (!in_array($novoEstado, $validStates)) {
            Response::error('Estado inválido', 422, ['valid_states' => $validStates]);
        }

        $stmt = $db->prepare('SELECT cliente_id, estado FROM entregas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Entrega não encontrada', 404);

        $role = $auth['role'] ?? '';
        if (!OwnerScope::isAdmin($auth) && $role !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['cliente_id']);
        }

        $estadoAnterior = $row['estado'];

        $stmt2 = $db->prepare('UPDATE entregas SET estado = ? WHERE id = ?');
        $stmt2->bind_param('si', $novoEstado, $id);
        if (!$stmt2->execute()) {
            error_log('Database error (EntregaController updateEstado): ' . $stmt2->error);
            Response::error('Erro ao atualizar estado', 500);
        }
        $stmt2->close();

        if ($novoEstado === 'entregue') {
            $stmtNow = $db->prepare('UPDATE entregas SET data_entrega = NOW() WHERE id = ? AND data_entrega IS NULL');
            $stmtNow->bind_param('i', $id);
            $stmtNow->execute();
            $stmtNow->close();
        }

        $utilizadorNome = $auth['user_metadata']['name'] ?? $auth['name'] ?? 'Sistema';
        $userId = $auth['user_id'];

        $stmt3 = $db->prepare("INSERT INTO historico_entregas (entrega_id, estado_anterior, estado_novo, utilizador_id, utilizador_nome, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt3->bind_param('isssis',
            $id,
            $estadoAnterior,
            $novoEstado,
            $userId,
            $utilizadorNome,
            $data['observacoes'] ?? null
        );
        if (!$stmt3->execute()) {
            error_log('Database error (EntregaController historico): ' . $stmt3->error);
        }
        $stmt3->close();

        if ($novoEstado !== 'cancelado' && $row['cliente_id']) {
            $stmt4 = $db->prepare('SELECT name FROM users WHERE id = ? LIMIT 1');
            $stmt4->bind_param('i', $row['cliente_id']);
            $stmt4->execute();
            $cliente = $stmt4->get_result()->fetch_assoc();
            $stmt4->close();
        }

        Response::success(['estado' => $novoEstado, 'estado_anterior' => $estadoAnterior], 'Estado atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT cliente_id FROM entregas WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Entrega não encontrada', 404);

        $role = $auth['role'] ?? '';
        if (!OwnerScope::isAdmin($auth) && $role !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['cliente_id']);
        }

        $stmt2 = $db->prepare('DELETE FROM entregas WHERE id = ?');
        $stmt2->bind_param('i', $id);
        if (!$stmt2->execute()) {
            error_log('Database error (EntregaController destroy): ' . $stmt2->error);
            Response::error('Erro ao remover entrega', 500);
        }
        $stmt2->close();

        Response::success([], 'Entrega removida');
    }

    public function stats()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();
        $role = $auth['role'] ?? '';
        $isAdmin = OwnerScope::isAdmin($auth);
        $isFuncionario = $role === 'funcionario';

        $where = '';
        $params = [];
        $types = '';

        if (!$isAdmin && !$isFuncionario) {
            $where = 'WHERE e.cliente_id = ?';
            $params[] = $auth['user_id'];
            $types = 'i';
        }

        $sql = "SELECT
            COUNT(*) as total,
            SUM(e.estado = 'pendente') as pendente,
            SUM(e.estado = 'em_preparacao') as em_preparacao,
            SUM(e.estado = 'saiu_da_base') as saiu_da_base,
            SUM(e.estado = 'em_transporte') as em_transporte,
            SUM(e.estado = 'chegou_cliente') as chegou_cliente,
            SUM(e.estado = 'entregue') as entregue,
            SUM(e.estado = 'cancelado') as cancelado
            FROM entregas e $where";
        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stats = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $sql2 = "SELECT COUNT(*) as total_contentores FROM contentores c
                 LEFT JOIN entregas e ON e.id = c.entrega_id
                 $where";
        $stmt2 = $db->prepare($sql2);
        if ($params) $stmt2->bind_param($types, ...$params);
        $stmt2->execute();
        $contentores = $stmt2->get_result()->fetch_assoc();
        $stmt2->close();
        $stats['total_contentores'] = $contentores['total_contentores'];

        $sql3 = "SELECT e.cliente_nome, COUNT(*) as total
                 FROM entregas e $where
                 GROUP BY e.cliente_id, e.cliente_nome
                 ORDER BY total DESC
                 LIMIT 10";
        $stmt3 = $db->prepare($sql3);
        if ($params) $stmt3->bind_param($types, ...$params);
        $stmt3->execute();
        $stats['top_clientes'] = $stmt3->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt3->close();

        $whereMotorista = $where ? "$where AND e.motorista_id IS NOT NULL" : 'WHERE e.motorista_id IS NOT NULL';
        $sql4 = "SELECT m.nome_completo, COUNT(*) as total
                 FROM entregas e
                 LEFT JOIN motoristas m ON m.id = e.motorista_id
                 $whereMotorista
                 GROUP BY e.motorista_id, m.nome_completo
                 ORDER BY total DESC
                 LIMIT 10";
        $stmt4 = $db->prepare($sql4);
        if ($params) $stmt4->bind_param($types, ...$params);
        $stmt4->execute();
        $stats['top_motoristas'] = $stmt4->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt4->close();

        $whereCamiao = $where ? "$where AND e.camiao_id IS NOT NULL" : 'WHERE e.camiao_id IS NOT NULL';
        $sql5 = "SELECT c.matricula, c.codigo_interno, COUNT(*) as total
                 FROM entregas e
                 LEFT JOIN camioes c ON c.id = e.camiao_id
                 $whereCamiao
                 GROUP BY e.camiao_id, c.matricula, c.codigo_interno
                 ORDER BY total DESC
                 LIMIT 10";
        $stmt5 = $db->prepare($sql5);
        if ($params) $stmt5->bind_param($types, ...$params);
        $stmt5->execute();
        $stats['top_camioes'] = $stmt5->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt5->close();

        Response::success(['stats' => $stats]);
    }

    public function importExcel()
    {
        $auth = OwnerScope::userFromToken();
        $role = $auth['role'] ?? '';
        if (!OwnerScope::isAdmin($auth) && $role !== 'funcionario') {
            Response::error('Sem permissão para importar entregas', 403);
        }

        $data = Response::input();
        $items = $data['entregas'] ?? $data;

        if (!is_array($items) || empty($items)) {
            Response::error('Nenhum dado para importar', 422);
        }

        $db = Database::connection();
        $db->begin_transaction();

        $imported = 0;
        $errors = [];

        try {
            foreach ($items as $idx => $item) {
                $referencia = OwnerScope::generateReference('ENT', 'entregas', 'referencia_fmlider');
                $clienteId = !empty($item['cliente_id']) ? (int)$item['cliente_id'] : null;
                $motoristaId = !empty($item['motorista_id']) ? (int)$item['motorista_id'] : null;
                $camiaoId = !empty($item['camiao_id']) ? (int)$item['camiao_id'] : null;
                $dataSaida = !empty($item['data_saida']) ? $item['data_saida'] : null;
                $dataPrevista = !empty($item['data_prevista']) ? $item['data_prevista'] : null;
                $dataEntrega = !empty($item['data_entrega']) ? $item['data_entrega'] : null;
                $userId = $auth['user_id'];

                $stmt = $db->prepare("INSERT INTO entregas (referencia_fmlider, referencia_cliente, numero_processo, tipologia, origem, destino, cliente_id, cliente_nome, motorista_id, camiao_id, matricula, estado, data_saida, data_prevista, data_entrega, observacoes, user_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $estado = $item['estado'] ?? 'pendente';

                $stmt->bind_param('ssssssiiiiisssssi',
                    $referencia,
                    $item['referencia_cliente'] ?? null,
                    $item['numero_processo'] ?? null,
                    $item['tipologia'] ?? null,
                    $item['origem'] ?? null,
                    $item['destino'] ?? null,
                    $clienteId,
                    $item['cliente_nome'] ?? null,
                    $motoristaId,
                    $camiaoId,
                    $item['matricula'] ?? null,
                    $estado,
                    $dataSaida,
                    $dataPrevista,
                    $dataEntrega,
                    $item['observacoes'] ?? null,
                    $userId
                );

                if (!$stmt->execute()) {
                    $errors[] = ['index' => $idx, 'error' => $stmt->error];
                    $stmt->close();
                    continue;
                }
                $entregaId = $stmt->insert_id;
                $stmt->close();

                if (!empty($item['contentores']) && is_array($item['contentores'])) {
                    $stmt2 = $db->prepare("INSERT INTO contentores (entrega_id, numero, tipo, estado, data_entrega, observacoes) VALUES (?, ?, ?, ?, ?, ?)");
                    foreach ($item['contentores'] as $c) {
                        $cDataEntrega = !empty($c['data_entrega']) ? $c['data_entrega'] : null;
                        $stmt2->bind_param('isssss',
                            $entregaId,
                            $c['numero'] ?? null,
                            $c['tipo'] ?? null,
                            $c['estado'] ?? null,
                            $cDataEntrega,
                            $c['observacoes'] ?? null
                        );
                        $stmt2->execute();
                    }
                    $stmt2->close();
                }

                $utilizadorNome = $auth['user_metadata']['name'] ?? $auth['name'] ?? 'Sistema';
                $stmt3 = $db->prepare("INSERT INTO historico_entregas (entrega_id, estado_anterior, estado_novo, utilizador_id, utilizador_nome, observacoes) VALUES (?, NULL, ?, ?, ?, ?)");
                $stmt3->bind_param('isis',
                    $entregaId,
                    $estado,
                    $userId,
                    $utilizadorNome,
                    $item['observacoes'] ?? 'Importação em lote'
                );
                $stmt3->execute();
                $stmt3->close();

                $imported++;
            }

            $db->commit();
            Response::success([
                'imported' => $imported,
                'errors' => $errors,
                'total' => count($items)
            ], 'Importação concluída');
        } catch (\Exception $e) {
            $db->rollback();
            error_log('Exception (EntregaController import): ' . $e->getMessage());
            Response::error('Erro na importação', 500);
        }
    }

    private function validate($data, $isUpdate = false)
    {
        $errors = [];
        if (!$isUpdate) {
            if (empty($data['origem'])) $errors['origem'] = 'Origem é obrigatória';
            if (empty($data['destino'])) $errors['destino'] = 'Destino é obrigatório';
        }
        return $errors;
    }
}
