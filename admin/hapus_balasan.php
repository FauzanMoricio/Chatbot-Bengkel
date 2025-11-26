<?php
require_once '../config/Database.php';

header('Content-Type: application/json');

// Validasi parameter id
if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = (int) $_GET["id"];

    try {
        // Inisialisasi koneksi database (PDO)
        $db = new Database();
        $conn = $db->connect();

        // Siapkan statement untuk menghapus data secara aman
        $stmt = $conn->prepare("DELETE FROM balasan WHERE id = :id");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);

        // Eksekusi dan tangani hasil
        if ($stmt->execute()) {
            echo json_encode(["Berhasil" => "Balasan berhasil dihapus!"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Gagal menghapus balasan."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["error" => "Kesalahan server: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["error" => "Permintaan tidak valid!"]);
}
?>
