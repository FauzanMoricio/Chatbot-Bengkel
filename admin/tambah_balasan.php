<?php
require_once '../config/Database.php';
session_start();

// Validasi input
if (empty($_POST["ulasan_id"]) || empty($_POST["nama"]) || empty($_POST["balasan"])) {
    http_response_code(400);
    echo "Data tidak lengkap";
    exit();
}

$ulasan_id = $_POST["ulasan_id"];
$nama = $_POST["nama"];
$balasan = $_POST["balasan"];
$is_admin = isset($_POST["is_admin"]) ? 1 : 0;

try {
    $db = new Database();
    $conn = $db->connect(); // Koneksi menggunakan PDO

    // Cek apakah ulasan dengan ID tersebut ada
    $stmtCheck = $conn->prepare("SELECT id FROM ulasan WHERE id = :id");
    $stmtCheck->bindParam(':id', $ulasan_id, PDO::PARAM_INT);
    $stmtCheck->execute();

    if ($stmtCheck->rowCount() === 0) {
        http_response_code(404);
        echo "Ulasan tidak ditemukan";
        exit();
    }

    // Insert balasan
    $stmtInsert = $conn->prepare("
        INSERT INTO balasan (ulasan_id, nama, balasan, dibuat_pada, is_admin)
        VALUES (:ulasan_id, :nama, :balasan, NOW(), :is_admin)
    ");
    $stmtInsert->bindParam(':ulasan_id', $ulasan_id, PDO::PARAM_INT);
    $stmtInsert->bindParam(':nama', $nama, PDO::PARAM_STR);
    $stmtInsert->bindParam(':balasan', $balasan, PDO::PARAM_STR);
    $stmtInsert->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);

    if ($stmtInsert->execute()) {
        echo "Balasan berhasil ditambahkan";
    } else {
        http_response_code(500);
        echo "Gagal menambahkan balasan";
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo "Kesalahan server: " . $e->getMessage();
}
?>
