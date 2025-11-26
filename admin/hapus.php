<?php
require_once '../config/Database.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('ID tidak valid');
}

$id = $_GET['id'];
$db = (new Database())->connect();

try {
    $stmt = $db->prepare("DELETE FROM responses WHERE id = ?");
    $stmt->execute([$id]);

    // Redirect dengan notifikasi
    header('Location: index.php?deleted=1');
    exit;

} catch (PDOException $e) {
    // Redirect dengan pesan error
    header('Location: index.php?error=' . urlencode($e->getMessage()));
    exit;
}
