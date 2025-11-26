<?php
require_once '../config/Database.php';

header('Content-Type: application/json');

if (isset($_GET["id"]) && is_numeric($_GET["id"])) {
    $id = (int) $_GET["id"];

    try {
        $db = new Database();
        $conn = $db->connect(); // Koneksi PDO

        $stmt = $conn->prepare("DELETE FROM ulasan WHERE id = :id");
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(["Berhasil" => "Ulasan berhasil dihapus!"]);
        } else {
            http_response_code(500);
            echo json_encode(["error" => "Gagal menghapus ulasan."]);
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
