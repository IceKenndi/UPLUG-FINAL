<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require __DIR__ . "/../config/dbconfig.php";

$currentUser = $_SESSION['user_id'] ?? null;
if (!$currentUser) {
  header('Content-Type: application/json');
  echo json_encode([]);
  exit;
}

$toasts = [];

$stmt = $conn->prepare("SELECT message, type, link FROM global_toasts WHERE exclude_user != ?");
$stmt->bind_param("s", $currentUser);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
  $toasts[] = $row;
}

// Filter out already seen toasts
$seenGlobalToasts = $_SESSION['seen_global_toasts'] ?? [];
$filteredToasts = [];

foreach ($toasts as $toast) {
  if (!in_array($toast['message'], $seenGlobalToasts)) {
    $filteredToasts[] = $toast;
    $seenGlobalToasts[] = $toast['message'];
  }
}

$_SESSION['seen_global_toasts'] = $seenGlobalToasts;

header('Content-Type: application/json');
echo json_encode($filteredToasts);
