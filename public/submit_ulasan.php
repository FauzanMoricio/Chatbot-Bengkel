<?php
require_once '../config/Database.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nama = $_POST['nama'] ?? '';
    $ulasan = $_POST['ulasan'] ?? '';

    try {
        // Koneksi ke database
        $db = (new Database())->connect();

        // Gunakan prepared statement untuk keamanan
        $stmt = $db->prepare("INSERT INTO ulasan (nama, ulasan, dibuat_pada) VALUES (:nama, :ulasan, NOW())");

        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':ulasan', $ulasan);

        if ($stmt->execute()) {
            echo "Ulasan berhasil dikirim!";
        } else {
            echo "Gagal menyimpan ulasan.";
        }

    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo "Terjadi kesalahan saat menyimpan ulasan.";
    }
}
?>
