<?php
require_once 'auth_check.php'; // Ensures user is logged in

header('Content-Type: application/json');

// --- Role-Based Access Control (ACL) ---
$user_role = $_SESSION['role'] ?? 'guest';
$entity = $_GET['entity'] ?? null;

$acl = [
    'admin' => ['stocks', 'cashTransactions', 'caris', 'invoices', 'cheques', 'stockMovements', 'productDescriptions'],
    'muhasebe' => ['cashTransactions', 'caris', 'invoices', 'cheques'],
    'stok' => ['stocks', 'stockMovements', 'productDescriptions']
];

if (!$entity || !isset($acl[$user_role]) || !in_array($entity, $acl[$user_role])) {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Forbidden: You do not have permission to access this resource.']);
    exit;
}
// --- End of ACL Check ---


$method = $_SERVER['REQUEST_METHOD'];
$data_file = __DIR__ . '/../data/' . $entity . '.json';

if (!file_exists($data_file)) {
    if (file_put_contents($data_file, json_encode([])) === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create data file.']);
        exit;
    }
}

switch ($method) {
    case 'GET':
        handle_get($data_file);
        break;
    case 'POST':
        handle_post($data_file);
        break;
    case 'DELETE':
        handle_delete($data_file);
        break;
    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method Not Allowed']);
        break;
}

function handle_get($data_file) {
    $data = json_decode(file_get_contents($data_file), true);
    echo json_encode(['success' => true, 'data' => $data]);
}

function handle_post($data_file) {
    $all_data = json_decode(file_get_contents($data_file), true);
    $new_item = json_decode(file_get_contents('php://input'), true);

    if (isset($new_item['id']) && !empty($new_item['id'])) { // Update existing item
        $found = false;
        foreach ($all_data as $key => $item) {
            if ($item['id'] === $new_item['id']) {
                $all_data[$key] = array_merge($item, $new_item);
                $found = true;
                break;
            }
        }
        if (!$found) {
             http_response_code(404);
             echo json_encode(['success' => false, 'message' => 'Item to update not found.']);
             exit;
        }
        $item_to_return = $new_item;
    } else { // Create new item
        $new_item['id'] = uniqid(rand(), true);
        $all_data[] = $new_item;
        $item_to_return = $new_item;
    }

    file_put_contents($data_file, json_encode($all_data, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true, 'data' => $item_to_return]);
}

function handle_delete($data_file) {
    $all_data = json_decode(file_get_contents($data_file), true);
    $id_to_delete = $_GET['id'] ?? null;

    if (!$id_to_delete) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No ID specified for deletion.']);
        exit;
    }

    $initial_count = count($all_data);
    $all_data = array_filter($all_data, function($item) use ($id_to_delete) {
        return isset($item['id']) && $item['id'] !== $id_to_delete;
    });

    if (count($all_data) === $initial_count) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Item to delete not found.']);
        exit;
    }

    file_put_contents($data_file, json_encode(array_values($all_data), JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
}
?>
