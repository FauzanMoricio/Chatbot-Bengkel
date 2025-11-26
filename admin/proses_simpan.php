<?php
require_once '../config/Database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keywords = $_POST['keywords'] ?? '';
    $json = $_POST['response_json'] ?? '';

    if (!$keywords || !$json) {
        die("Keyword dan respon JSON wajib diisi.");
    }

    try {
        $db = (new Database())->connect();
        $stmt = $db->prepare("INSERT INTO responses (keyword, response_json) VALUES (:keyword, :response_json)");
        $stmt->execute([
            ':keyword' => $keywords,
            ':response_json' => $json
        ]);

        header('Location: index.php?success=1');
        exit;
    } catch (PDOException $e) {
        die("Gagal menyimpan data: " . $e->getMessage());
    }
}
