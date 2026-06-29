<?php

namespace App\Controllers;

use App\Config\Database;
use App\Helpers\OwnerScope;
use App\Helpers\Response;

class LicenciamentoController
{
    public function index()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $estado = $_GET['estado'] ?? null;
        $tipo = $_GET['tipo'] ?? null;
        $search = $_GET['q'] ?? null;
        $userId = $_GET['user_id'] ?? null;

        $sql = "SELECT l.*, u.name as client_name, f.name as func_name FROM licenciamentos l
                LEFT JOIN users u ON u.id = l.user_id
                LEFT JOIN users f ON f.id = l.funcionario_id
                WHERE 1=1";
        $params = [];
        $types = '';

        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            $sql .= " AND l.user_id = ?";
            $params[] = $auth['user_id'];
            $types .= 'i';
        } elseif (!OwnerScope::isAdmin($auth) && $auth['role'] === 'funcionario') {
        } elseif ($userId) {
            $sql .= " AND l.user_id = ?";
            $params[] = (int)$userId;
            $types .= 'i';
        }

        if ($estado) { $sql .= " AND l.estado = ?"; $params[] = $estado; $types .= 's'; }
        if ($tipo) { $sql .= " AND l.tipo_licenciamento = ?"; $params[] = $tipo; $types .= 's'; }
        if ($search) {
            $sql .= " AND (l.numero_processo LIKE ? OR l.referencia LIKE ? OR l.empresa LIKE ? OR l.nif_empresa LIKE ? OR l.descricao LIKE ?)";
            $like = "%$search%";
            $params = array_merge($params, [$like, $like, $like, $like, $like]);
            $types .= 'sssss';
        }

        $sql .= " ORDER BY l.created_at DESC LIMIT 500";

        $stmt = $db->prepare($sql);
        if ($params) $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        Response::success(['licenciamentos' => $rows]);
    }

    public function show($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare("SELECT l.*, u.name as client_name, f.name as func_name FROM licenciamentos l LEFT JOIN users u ON u.id = l.user_id LEFT JOIN users f ON f.id = l.funcionario_id WHERE l.id = ? LIMIT 1");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Licenciamento não encontrado', 404);

        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);
        }

        $histStmt = $db->prepare("SELECT h.*, u.name as user_name FROM licenciamento_historico h LEFT JOIN users u ON u.id = h.user_id WHERE h.licenciamento_id = ? ORDER BY h.created_at DESC");
        $histStmt->bind_param('i', $id);
        $histStmt->execute();
        $historico = $histStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $histStmt->close();

        $estadoStmt = $db->prepare("SELECT e.*, u.name as user_name FROM licenciamento_estados_historico e LEFT JOIN users u ON u.id = e.user_id WHERE e.licenciamento_id = ? ORDER BY e.created_at DESC");
        $estadoStmt->bind_param('i', $id);
        $estadoStmt->execute();
        $estados = $estadoStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $estadoStmt->close();

        $row['historico'] = $historico;
        $row['estados_historico'] = $estados;

        Response::success(['licenciamento' => $row]);
    }

    public function store()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $data = Response::input();
        $errors = $this->validate($data);
        if (!empty($errors)) Response::error('Verifique os campos', 422, $errors);

        $referencia = OwnerScope::generateReference('LIC', 'licenciamentos', 'referencia');

        $userId = $data['user_id'] ?? $auth['user_id'];
        $funcionarioId = $data['funcionario_id'] ?? null;
        $tipo = $data['tipo_licenciamento'] ?? 'Outro';
        $estado = $data['estado'] ?? 'rascunho';
        $dataSubmissao = !empty($data['data_submissao']) ? $data['data_submissao'] : null;
        $dataValidade = !empty($data['data_validade']) ? $data['data_validade'] : null;

        $stmt = $db->prepare("INSERT INTO licenciamentos (user_id, funcionario_id, numero_processo, referencia, tipo_licenciamento, descricao, empresa, nif_empresa, estado, data_submissao, data_validade, observacoes, fonte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $stmt->bind_param('iisssssssssss',
            $userId,
            $funcionarioId,
            $data['numero_processo'] ?? null,
            $referencia,
            $tipo,
            $data['descricao'] ?? null,
            $data['empresa'] ?? null,
            $data['nif_empresa'] ?? null,
            $estado,
            $dataSubmissao,
            $dataValidade,
            $data['observacoes'] ?? null,
            $data['fonte'] ?? 'manual'
        );

        if (!$stmt->execute()) {
            error_log('Database error (LicenciamentoController create): ' . $stmt->error);
            Response::error('Erro interno do servidor', 500);
        }
        $newId = $stmt->insert_id;
        $stmt->close();

        $this->logEstado($db, $newId, null, $estado, $auth['user_id'], 'Processo criado');

        Response::success(['licenciamento_id' => $newId, 'referencia' => $referencia], 'Licenciamento criado');
    }

    public function update($id)
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $stmt = $db->prepare('SELECT * FROM licenciamentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Licenciamento não encontrado', 404);

        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            OwnerScope::ensureOwnerOrAdmin($auth, $row['user_id']);
        }

        $data = Response::input();

        $fields = ['numero_processo', 'tipo_licenciamento', 'descricao', 'empresa', 'nif_empresa', 'observacoes', 'funcionario_id'];
        $setClauses = [];
        $params = [];
        $types = '';

        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $setClauses[] = "$field = ?";
                $params[] = $data[$field] ?? null;
                $types .= 's';
            }
        }

        $dateFields = ['data_submissao', 'data_aprovacao', 'data_indeferimento', 'data_validade', 'data_expiracao'];
        foreach ($dateFields as $field) {
            if (array_key_exists($field, $data)) {
                $setClauses[] = "$field = ?";
                $params[] = !empty($data[$field]) ? $data[$field] : null;
                $types .= 's';
            }
        }

        if (!empty($setClauses)) {
            $sql = "UPDATE licenciamentos SET " . implode(', ', $setClauses) . " WHERE id = ?";
            $params[] = $id;
            $types .= 'i';
            $stmt = $db->prepare($sql);
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $stmt->close();

            foreach ($fields as $field) {
                if (array_key_exists($field, $data) && $data[$field] !== $row[$field]) {
                    $this->logHistorico($db, $id, $auth['user_id'], $field, $row[$field], $data[$field]);
                }
            }
        }

        Response::success([], 'Licenciamento atualizado');
    }

    public function destroy($id)
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth)) Response::error('Apenas admin pode eliminar', 403);

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM licenciamentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Licenciamento não encontrado', 404);

        $stmt = $db->prepare('DELETE FROM licenciamentos WHERE id = ?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $stmt->close();

        Response::success([], 'Licenciamento removido');
    }

    public function stats()
    {
        $auth = OwnerScope::userFromToken();
        $db = Database::connection();

        $sql = "SELECT
            COUNT(*) as total,
            SUM(estado='rascunho') as rascunho,
            SUM(estado='pendente_cliente') as pendente_cliente,
            SUM(estado='submetido') as submetido,
            SUM(estado='em_analise') as em_analise,
            SUM(estado='aprovado') as aprovado,
            SUM(estado='indeferido') as indeferido,
            SUM(estado='expira_brevemente') as expira_brevemente,
            SUM(estado='expirado') as expirado
            FROM licenciamentos";

        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            $sql .= " WHERE user_id = ?";
            $stmt = $db->prepare($sql);
            $stmt->bind_param('i', $auth['user_id']);
        } else {
            $stmt = $db->prepare($sql);
        }

        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        Response::success(['stats' => $row]);
    }

    public function importExcel()
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            Response::error('Sem permissão', 403);
        }

        $db = Database::connection();
        $data = Response::input();

        if (empty($data['records']) || !is_array($data['records'])) {
            Response::error('Nenhum registo para importar', 422);
        }

        $imported = 0;
        $updated = 0;
        $created = 0;
        $errors = [];
        $errorCount = 0;

        foreach ($data['records'] as $index => $record) {
            try {
                $numeroProcesso = $record['numero_processo'] ?? $record['Numero do Processo'] ?? $record['numero'] ?? null;
                $empresa = $record['empresa'] ?? $record['Empresa'] ?? $record['nome_empresa'] ?? null;
                $nif = $record['nif_empresa'] ?? $record['NIF'] ?? $record['nif'] ?? null;
                $tipo = $record['tipo_licenciamento'] ?? $record['Tipo'] ?? $record['tipo'] ?? 'Outro';
                $estadoExcel = $record['estado'] ?? $record['Estado'] ?? $record['status'] ?? null;
                $dataSubmissao = $record['data_submissao'] ?? $record['Data de Submissão'] ?? $record['data_sub'] ?? null;
                $dataAprovacao = $record['data_aprovacao'] ?? $record['Data de Aprovação'] ?? null;
                $dataIndeferimento = $record['data_indeferimento'] ?? $record['Data de Indeferimento'] ?? null;
                $dataValidade = $record['data_validade'] ?? $record['Data de Validade'] ?? null;
                $descricao = $record['descricao'] ?? $record['Descricao'] ?? $record['descrição'] ?? null;
                $observacoes = $record['observacoes'] ?? $record['Observações'] ?? $record['notas'] ?? null;
                $userId = $record['user_id'] ?? $data['default_user_id'] ?? null;

                $estado = $this->determinarEstado($estadoExcel, $dataAprovacao, $dataIndeferimento, $dataSubmissao, $dataValidade);

                if ($numeroProcesso) {
                    $checkStmt = $db->prepare("SELECT id, estado FROM licenciamentos WHERE numero_processo = ? LIMIT 1");
                    $checkStmt->bind_param('s', $numeroProcesso);
                    $checkStmt->execute();
                    $existing = $checkStmt->get_result()->fetch_assoc();
                    $checkStmt->close();

                    if ($existing) {
                        $updateFields = [];
                        $updateParams = [];
                        $updateTypes = '';

                        if ($empresa) { $updateFields[] = "empresa = ?"; $updateParams[] = $empresa; $updateTypes .= 's'; }
                        if ($nif) { $updateFields[] = "nif_empresa = ?"; $updateParams[] = $nif; $updateTypes .= 's'; }
                        if ($tipo) { $updateFields[] = "tipo_licenciamento = ?"; $updateParams[] = $tipo; $updateTypes .= 's'; }
                        if ($descricao) { $updateFields[] = "descricao = ?"; $updateParams[] = $descricao; $updateTypes .= 's'; }
                        if ($observacoes) { $updateFields[] = "observacoes = ?"; $updateParams[] = $observacoes; $updateTypes .= 's'; }
                        if ($dataSubmissao) { $updateFields[] = "data_submissao = ?"; $updateParams[] = $dataSubmissao; $updateTypes .= 's'; }
                        if ($dataAprovacao) { $updateFields[] = "data_aprovacao = ?"; $updateParams[] = $dataAprovacao; $updateTypes .= 's'; }
                        if ($dataIndeferimento) { $updateFields[] = "data_indeferimento = ?"; $updateParams[] = $dataIndeferimento; $updateTypes .= 's'; }
                        if ($dataValidade) { $updateFields[] = "data_validade = ?"; $updateParams[] = $dataValidade; $updateTypes .= 's'; }
                        if ($estado && $estado !== $existing['estado']) {
                            $updateFields[] = "estado = ?";
                            $updateParams[] = $estado;
                            $updateTypes .= 's';
                            $this->logEstado($db, $existing['id'], $existing['estado'], $estado, $auth['user_id'], 'Atualização via Excel');
                        }

                        if (!empty($updateFields)) {
                            $updateParams[] = $existing['id'];
                            $updateTypes .= 'i';
                            $uStmt = $db->prepare("UPDATE licenciamentos SET " . implode(', ', $updateFields) . " WHERE id = ?");
                            $uStmt->bind_param($updateTypes, ...$updateParams);
                            $uStmt->execute();
                            $uStmt->close();
                            $updated++;
                        }
                        $imported++;
                    } else {
                        $referencia = OwnerScope::generateReference('LIC', 'licenciamentos', 'referencia');
                        $finalUserId = $userId ?: $auth['user_id'];

                        $iStmt = $db->prepare("INSERT INTO licenciamentos (user_id, numero_processo, referencia, tipo_licenciamento, descricao, empresa, nif_empresa, estado, data_submissao, data_aprovacao, data_indeferimento, data_validade, observacoes, fonte) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'excel')");
                        $iStmt->bind_param('issssssssssss',
                            $finalUserId,
                            $numeroProcesso,
                            $referencia,
                            $tipo,
                            $descricao,
                            $empresa,
                            $nif,
                            $estado,
                            $dataSubmissao ?: null,
                            $dataAprovacao ?: null,
                            $dataIndeferimento ?: null,
                            $dataValidade ?: null,
                            $observacoes
                        );

                        if ($iStmt->execute()) {
                            $newId = $iStmt->insert_id;
                            $this->logEstado($db, $newId, null, $estado, $auth['user_id'], 'Importado via Excel');
                            $created++;
                            $imported++;
                        } else {
                            $errorCount++;
                            $errors[] = ['linha' => $index + 1, 'erro' => $iStmt->error];
                        }
                        $iStmt->close();
                    }
                } else {
                    $errorCount++;
                    $errors[] = ['linha' => $index + 1, 'erro' => 'Número do processo em falta'];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = ['linha' => $index + 1, 'erro' => $e->getMessage()];
            }
        }

        Response::success([
            'importados' => $imported,
            'atualizados' => $updated,
            'novos' => $created,
            'erros' => $errorCount,
            'detalhes_erros' => $errors,
        ], 'Importação concluída');
    }

    public function updateEstado($id)
    {
        $auth = OwnerScope::userFromToken();
        if (!OwnerScope::isAdmin($auth) && $auth['role'] !== 'funcionario') {
            Response::error('Sem permissão', 403);
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id, estado FROM licenciamentos WHERE id = ? LIMIT 1');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) Response::error('Licenciamento não encontrado', 404);

        $data = Response::input();
        if (empty($data['estado'])) Response::error('Estado é obrigatório', 422);

        $novoEstado = $data['estado'];
        $observacao = $data['observacao'] ?? null;

        $updateSql = "UPDATE licenciamentos SET estado = ?";
        $updateParams = [$novoEstado];
        $updateTypes = 's';

        if ($novoEstado === 'aprovado' && empty($row['data_aprovacao'])) {
            $updateSql .= ", data_aprovacao = CURDATE()";
        }
        if ($novoEstado === 'indeferido' && empty($row['data_indeferimento'])) {
            $updateSql .= ", data_indeferimento = CURDATE()";
        }

        $updateSql .= " WHERE id = ?";
        $updateParams[] = $id;
        $updateTypes .= 'i';

        $uStmt = $db->prepare($updateSql);
        $uStmt->bind_param($updateTypes, ...$updateParams);
        $uStmt->execute();
        $uStmt->close();

        $this->logEstado($db, $id, $row['estado'], $novoEstado, $auth['user_id'], $observacao);

        if ($novoEstado === 'aprovado' || $novoEstado === 'indeferido') {
            $licStmt = $db->prepare("SELECT user_id FROM licenciamentos WHERE id = ?");
            $licStmt->bind_param('i', $id);
            $licStmt->execute();
            $lic = $licStmt->get_result()->fetch_assoc();
            $licStmt->close();

            if ($lic) {
                $notifStmt = $db->prepare("INSERT INTO notifications (user_id, type, title, body, link, icon, is_read) VALUES (?, 'licenciamento_estado', ?, ?, '/licenciamentos', 'bi-file-earmark-check', 0)");
                $title = 'Estado do licenciamento atualizado';
                $body = "O seu licenciamento foi " . ($novoEstado === 'aprovado' ? 'aprovado' : 'indeferido') . ".";
                $notifStmt->bind_param('iss', $lic['user_id'], $title, $body);
                $notifStmt->execute();
                $notifStmt->close();
            }
        }

        Response::success([], 'Estado atualizado');
    }

    private function determinarEstado($estadoExcel, $dataAprovacao, $dataIndeferimento, $dataSubmissao, $dataValidade)
    {
        if ($dataAprovacao) return 'aprovado';
        if ($dataIndeferimento) return 'indeferido';
        if ($dataValidade && strtotime($dataValidade) < time()) return 'expirado';
        if ($dataValidade && strtotime($dataValidade) < strtotime('+30 days')) return 'expira_brevemente';
        if ($dataSubmissao) return 'em_analise';
        if ($estadoExcel) {
            $map = [
                'aprovado' => 'aprovado',
                'aprovada' => 'aprovado',
                'indeferido' => 'indeferido',
                'indeferida' => 'indeferido',
                'em análise' => 'em_analise',
                'em analise' => 'em_analise',
                'submetido' => 'submetido',
                'pendente' => 'pendente_cliente',
                'expirado' => 'expirado',
            ];
            $lower = strtolower(trim($estadoExcel));
            return $map[$lower] ?? 'submetido';
        }
        return 'submetido';
    }

    private function logEstado($db, $licenciamentoId, $estadoAnterior, $estadoNovo, $userId, $observacao)
    {
        $stmt = $db->prepare("INSERT INTO licenciamento_estados_historico (licenciamento_id, user_id, estado_anterior, estado_novo, observacao) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iisss', $licenciamentoId, $userId, $estadoAnterior, $estadoNovo, $observacao);
        $stmt->execute();
        $stmt->close();
    }

    private function logHistorico($db, $licenciamentoId, $userId, $campo, $valorAntigo, $valorNovo)
    {
        $stmt = $db->prepare("INSERT INTO licenciamento_historico (licenciamento_id, user_id, campo, valor_antigo, valor_novo) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('issss', $licenciamentoId, $userId, $campo, $valorAntigo, $valorNovo);
        $stmt->execute();
        $stmt->close();
    }

    private function validate($data)
    {
        $errors = [];
        if (empty($data['tipo_licenciamento'])) $errors['tipo_licenciamento'] = 'Tipo é obrigatório';
        return $errors;
    }
}
