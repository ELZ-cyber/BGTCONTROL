<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const DATA_FILE = __DIR__ . '/datos_grupos.json';
const DIRECCION = 'Dirección';

function default_data(): array {
    return [
        'groups' => [
            'Grupo A' => ['members' => [], 'description' => '', 'admin' => null, 'delegados' => []],
            'Grupo B' => ['members' => [], 'description' => '', 'admin' => null, 'delegados' => []],
            DIRECCION => ['members' => [], 'description' => '', 'admin' => null, 'delegados' => []],
        ],
        'weeks' => [], 'notes' => [], 'antecedents' => [], 'movements' => [],
    ];
}

function load_data(): array {
    if (!file_exists(DATA_FILE)) return default_data();
    $fp = fopen(DATA_FILE, 'r');
    if (!$fp) return default_data();
    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    $d = json_decode($content, true);
    if (!is_array($d)) return default_data();
    foreach (['groups' => [], 'weeks' => [], 'notes' => [], 'antecedents' => [], 'movements' => []] as $k => $v) {
        if (!isset($d[$k])) $d[$k] = $v;
    }
    if (!isset($d['groups'][DIRECCION])) {
        $d['groups'][DIRECCION] = ['members' => [], 'description' => '', 'admin' => null, 'delegados' => []];
    }
    foreach ($d['groups'] as &$gg) {
        $gg['members'] = $gg['members'] ?? [];
        $gg['description'] = $gg['description'] ?? '';
        $gg['admin'] = $gg['admin'] ?? null;
        $gg['delegados'] = $gg['delegados'] ?? [];
    }
    unset($gg);
    foreach ($d['weeks'] as &$wdata) {
        foreach ($wdata as &$wg) {
            $wg['participation'] = $wg['participation'] ?? [];
            $wg['incidents'] = $wg['incidents'] ?? [];
            foreach ($wg['participation'] as $mid => $v) {
                if (is_string($v)) {
                    $wg['participation'][$mid] = ['status' => $v, 'days' => [], 'mult' => 1];
                } elseif (is_array($v)) {
                    $wg['participation'][$mid] = [
                        'status' => $v['status'] ?? 'si',
                        'days' => $v['days'] ?? [],
                        'mult' => (int)($v['mult'] ?? 1),
                    ];
                }
            }
        }
    }
    unset($wg, $wdata);
    return $d;
}

function save_data(array $d): bool {
    $tmp = DATA_FILE . '.tmp';
    $fp = fopen($tmp, 'w');
    if (!$fp) return false;
    flock($fp, LOCK_EX);
    fwrite($fp, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);
    return rename($tmp, DATA_FILE);
}

function new_id(): string { return bin2hex(random_bytes(5)); }
function now_iso(): string { return date('Y-m-d H:i'); }

function find_member(array &$data, string $mid): array {
    foreach ($data['groups'] as $gname => &$gg) {
        foreach ($gg['members'] as $idx => &$m) {
            if ($m['id'] === $mid) {
                return ['group' => $gname, 'member' => &$m, 'index' => $idx];
            }
        }
    }
    return ['group' => null, 'member' => null, 'index' => -1];
}

function remove_member(array &$data, string $mid): ?array {
    foreach ($data['groups'] as &$gg) {
        foreach ($gg['members'] as $idx => $m) {
            if ($m['id'] === $mid) {
                unset($gg['members'][$idx]);
                $gg['members'] = array_values($gg['members']);
                return $m;
            }
        }
    }
    return null;
}

$input = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_GET['action'] ?? '';
$p = $input['payload'] ?? [];

try {
    $data = load_data();
    $result = ['ok' => true];

    switch ($action) {
        case 'load':
            break;

        case 'add_member':
            $g = $p['group'];
            if (!isset($data['groups'][$g])) throw new Exception('Grupo no existe');
            $data['groups'][$g]['members'][] = [
                'id' => new_id(),
                'name' => trim($p['name']),
                'role' => $p['role'],
                'created' => now_iso(),
                'castigado' => $g === DIRECCION,
            ];
            break;

        case 'edit_member':
            $old = remove_member($data, $p['id']);
            if ($old) {
                $old['name'] = trim($p['name']);
                $old['role'] = $p['role'];
                if ($p['group'] === DIRECCION) $old['castigado'] = true;
                $data['groups'][$p['group']]['members'][] = $old;
            }
            break;

        case 'delete_member':
            remove_member($data, $p['id']);
            break;

        case 'move_member':
            $mem = remove_member($data, $p['id']);
            if ($mem && $p['from'] !== $p['to']) {
                $mem['castigado'] = ($p['to'] === DIRECCION);
                $data['groups'][$p['to']]['members'][] = $mem;
                array_unshift($data['movements'], [
                    'id' => new_id(),
                    'member_id' => $p['id'],
                    'member_name' => $mem['name'],
                    'from' => $p['from'],
                    'to' => $p['to'],
                    'type' => $p['type'],
                    'reason' => $p['reason'],
                    'date' => now_iso(),
                ]);
                $data['antecedents'][] = [
                    'id' => new_id(),
                    'member_id' => $p['id'],
                    'tipo' => $p['type'] === 'castigo' ? 'Falta' : 'Acuerdo',
                    'text' => ($p['type'] === 'castigo' ? 'Castigo · trasladado a ' : 'Trasladado a ')
                              . $p['to'] . ': ' . $p['reason'],
                    'date' => now_iso(),
                ];
            }
            break;

        case 'add_group':
            $n = trim($p['name']);
            if ($n && $n !== DIRECCION && !isset($data['groups'][$n])) {
                $data['groups'][$n] = [
                    'members' => [],
                    'description' => $p['description'] ?? '',
                    'admin' => null,
                    'delegados' => [],
                ];
            }
            break;

        case 'edit_group':
            $oldName = $p['old_name'];
            $newName = trim($p['name']);
            if (!isset($data['groups'][$oldName])) break;
            if ($newName !== $oldName && !isset($data['groups'][$newName])) {
                $data['groups'][$newName] = $data['groups'][$oldName];
                unset($data['groups'][$oldName]);
                foreach ($data['weeks'] as &$wd) {
                    if (isset($wd[$oldName])) {
                        $wd[$newName] = $wd[$oldName];
                        unset($wd[$oldName]);
                    }
                }
                unset($wd);
                $oldName = $newName;
            }
            $data['groups'][$oldName]['description'] = $p['description'] ?? '';
            $data['groups'][$oldName]['admin'] = $p['admin'] ?: null;
            $data['groups'][$oldName]['delegados'] = $p['delegados'] ?? [];
            break;

        case 'delete_group':
            $g = $p['name'];
            if ($g !== DIRECCION && isset($data['groups'][$g])) {
                foreach ($data['groups'][$g]['members'] as $m) {
                    $m['castigado'] = true;
                    $data['groups'][DIRECCION]['members'][] = $m;
                }
                unset($data['groups'][$g]);
            }
            break;

        case 'mark':
            $wk = $p['week']; $g = $p['group'];
            if (!isset($data['weeks'][$wk])) $data['weeks'][$wk] = [];
            if (!isset($data['weeks'][$wk][$g])) {
                $data['weeks'][$wk][$g] = ['participation' => [], 'incidents' => []];
            }
            $part = &$data['weeks'][$wk][$g]['participation'];
            $estado = $p['estado'];
            $day = (int)$p['day'];
            $mult = (int)($p['mult'] ?? 1);
            foreach ($p['mids'] as $mid) {
                if ($estado === null) {
                    unset($part[$mid]);
                } elseif ($estado === 'no') {
                    $part[$mid] = ['status' => 'no', 'days' => [$day], 'mult' => 1];
                } else {
                    $old = $part[$mid] ?? null;
                    if (!is_array($old)) $old = ['status' => 'si', 'days' => [], 'mult' => 1];
                    $days = $old['days'] ?? [];
                    if (!in_array($day, $days)) $days[] = $day;
                    sort($days);
                    $oldMult = (int)($old['mult'] ?? 1);
                    $newMult = ($old['status'] ?? 'si') === 'si' ? max($oldMult, $mult) : $mult;
                    $part[$mid] = ['status' => 'si', 'days' => $days, 'mult' => $newMult];
                }
            }
            unset($part);
            break;

        case 'add_incident':
            $wk = $p['week']; $g = $p['group'];
            if (!isset($data['weeks'][$wk])) $data['weeks'][$wk] = [];
            if (!isset($data['weeks'][$wk][$g])) {
                $data['weeks'][$wk][$g] = ['participation' => [], 'incidents' => []];
            }
            $data['weeks'][$wk][$g]['incidents'][] = [
                'id' => new_id(),
                'member_id' => $p['member_id'],
                'text' => $p['text'],
                'date' => now_iso(),
            ];
            break;

        case 'add_note':
            array_unshift($data['notes'], [
                'id' => new_id(),
                'title' => $p['title'] ?: 'Sin título',
                'group' => $p['group'],
                'body' => $p['body'],
                'date' => now_iso(),
            ]);
            break;

        case 'update_note':
            foreach ($data['notes'] as &$n) {
                if ($n['id'] === $p['id']) {
                    $n['body'] = $p['body'];
                    $n['date'] = now_iso();
                    break;
                }
            }
            unset($n);
            break;

        case 'delete_note':
            $data['notes'] = array_values(array_filter($data['notes'], fn($n) => $n['id'] !== $p['id']));
            break;

        case 'add_antecedent':
            $data['antecedents'][] = [
                'id' => new_id(),
                'member_id' => $p['member_id'],
                'tipo' => $p['tipo'],
                'text' => $p['text'],
                'date' => now_iso(),
            ];
            break;

        case 'clear_movements':
            $data['movements'] = [];
            break;

        default:
            http_response_code(400);
            echo json_encode(['ok' => false, 'error' => 'Unknown action']);
            exit;
    }

    if ($action !== 'load') save_data($data);
    $result['data'] = $data;
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}