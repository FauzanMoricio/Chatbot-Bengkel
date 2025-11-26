<?php
require_once '../config/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Ambil data POST
    $ulasan_id = $_POST['ulasan_id'] ?? '';
    $nama = $_POST['nama'] ?? '';
    $balasan = $_POST['balasan'] ?? '';
    $is_admin = isset($_POST['is_admin']) ? 1 : 0;

    try {
        // Koneksi ke database
        $db = (new Database())->connect();

        // Gunakan prepared statement untuk keamanan
        $stmt = $db->prepare("INSERT INTO balasan (ulasan_id, nama, balasan, is_admin, dibuat_pada) 
                              VALUES (:ulasan_id, :nama, :balasan, :is_admin, NOW())");

        $stmt->bindParam(':ulasan_id', $ulasan_id);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':balasan', $balasan);
        $stmt->bindParam(':is_admin', $is_admin, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Balasan berhasil dikirim!";
        } else {
            echo "Gagal menyimpan balasan.";
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Terjadi kesalahan saat menyimpan balasan.";
    }
}
?>
