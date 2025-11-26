<?php
require_once '../config/Database.php';

header('Content-Type: application/json');

try {
    $db = (new Database())->connect();

    $stmt = $db->prepare("SELECT * FROM ulasan ORDER BY dibuat_pada DESC");
    $stmt->execute();

    $ulasan = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($ulasan);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Gagal mengambil data ulasan"]);
    // Untuk debug (hapus di produksi): error_log($e->getMessage());
}
?>
