<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$username = sanitize_input($_POST['username']);
$email = sanitize_input($_POST['email']);
$full_name = sanitize_input($_POST['full_name']);
$password = $_POST['password'];
$is_student = isset($_POST['is_student']) ? 1 : 0;

// Validate inputs
if (empty($username) || empty($email) || empty($full_name) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Check if username or email already exists
$check_query = "SELECT id FROM users WHERE username = ? OR email = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ss", $username, $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
    exit;
}

// Hash password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert user
$insert_query = "INSERT INTO users (username, email, full_name, password, is_student) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_query);
$stmt->bind_param("ssssi", $username, $email, $full_name, $hashed_password, $is_student);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Registration successful']);
} else {
    echo json_encode(['success' => false, 'message' => 'Registration failed']);
}
?>
