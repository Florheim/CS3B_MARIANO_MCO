<?php
require_once 'config.php'; 
header('Content-Type: application/json'); 
$action = $_GET['action'] ?? ''; 

switch ($action) {
    case 'get_tasks':
        getTasks($conn);
        break;
    case 'add_task':
        addTask($conn);
        break;
    case 'update_task':
        updateTask($conn);
        break;
    case 'mark_done':
        markTaskDone($conn);
        break;
    case 'delete_task':
        deleteTask($conn);
        break;
    default:
        echo json_encode(['error' => 'Invalid API action.']);
        break;
}

$conn->close(); 

function getTasks($conn) {
    $sort_by = $_GET['sort_by'] ?? 'created_at';
    $sort_order = $_GET['sort_order'] ?? 'DESC';
    $filter_status = $_GET['filter_status'] ?? '';
    $search_query = $_GET['search_query'] ?? '';

    $allowed_sorts = ['created_at', 'due_date', 'status', 'title'];
    if (!in_array($sort_by, $allowed_sorts)) {
        $sort_by = 'created_at';
    }

    $allowed_orders = ['ASC', 'DESC'];
    if (!in_array(strtoupper($sort_order), $allowed_orders)) {
        $sort_order = 'DESC';
    }

    $sql = "SELECT id, title, description, due_date, status, created_at FROM tasks WHERE 1=1";
    $params = [];
    $types = "";

    if (!empty($filter_status) && in_array($filter_status, ['pending', 'in progress', 'completed'])) {
        $sql .= " AND status = ?";
        $params[] = $filter_status;
        $types .= "s";
    }

    if (!empty($search_query)) {
        $sql .= " AND (title LIKE ? OR description LIKE ?)";
        $params[] = "%" . $search_query . "%";
        $params[] = "%" . $search_query . "%";
        $types .= "ss";
    }

    $sql .= " ORDER BY $sort_by $sort_order";

    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $tasks = [];
    while ($row = $result->fetch_assoc()) {
        $tasks[] = $row;
    }

    echo json_encode(['success' => true, 'tasks' => $tasks]);
    $stmt->close();
}

function addTask($conn) {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    $status = trim($_POST['status'] ?? 'pending'); // Default status

    if (empty($title)) {
        echo json_encode(['error' => 'Title is required.']);
        return;
    }
    if (!empty($due_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $due_date)) {
        echo json_encode(['error' => 'Invalid due date format. Use YYYY-MM-DD.']);
        return;
    }
    if (!in_array($status, ['pending', 'in progress', 'completed'])) {
        $status = 'pending'; 
    }

    $sql = "INSERT INTO tasks (title, description, due_date, status) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("ssss", $title, $description, $due_date, $status);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Task added successfully!', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
}

function updateTask($conn) {
    $id = intval($_POST['id'] ?? 0);
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $due_date = trim($_POST['due_date'] ?? '');
    $status = trim($_POST['status'] ?? '');

    if ($id <= 0 || empty($title) || empty($status)) {
        echo json_encode(['error' => 'Invalid ID, title, or status.']);
        return;
    }
    if (!empty($due_date) && !preg_match("/^\d{4}-\d{2}-\d{2}$/", $due_date)) {
        echo json_encode(['error' => 'Invalid due date format. Use YYYY-MM-DD.']);
        return;
    }
    if (!in_array($status, ['pending', 'in progress', 'completed'])) {
        echo json_encode(['error' => 'Invalid status value.']);
        return;
    }

    $sql = "UPDATE tasks SET title = ?, description = ?, due_date = ?, status = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("ssssi", $title, $description, $due_date, $status, $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task updated successfully!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'No changes made or task not found.']);
        }
    } else {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
}

function markTaskDone($conn) {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid task ID.']);
        return;
    }

    $sql = "UPDATE tasks SET status = 'completed' WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task marked as completed!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Task not found or already completed.']);
        }
    } else {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
}

function deleteTask($conn) {
    $id = intval($_POST['id'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['error' => 'Invalid task ID.']);
        return;
    }

    $sql = "DELETE FROM tasks WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        echo json_encode(['error' => 'Prepare failed: ' . $conn->error]);
        return;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Task deleted successfully!']);
        } else {
            echo json_encode(['success' => true, 'message' => 'Task not found.']);
        }
    } else {
        echo json_encode(['error' => 'Execute failed: ' . $stmt->error]);
    }
    $stmt->close();
}
?>