<?php
require_once '../config/Database.php';

header('Content-Type: application/json');

try {
    $db = (new Database())->connect();

    if (isset($_GET['ulasan_id'])) {
        $ulasan_id = $_GET['ulasan_id'];

        // Gunakan prepared statement untuk keamanan
        $queryCheckColumn = $db->query("SHOW COLUMNS FROM balasan LIKE 'is_admin'");
        $has_admin_col = $queryCheckColumn->rowCount() > 0;

        // Buat query sesuai dengan keberadaan kolom
        if ($has_admin_col) {
            $sql = "SELECT id, ulasan_id, nama, balasan, dibuat_pada, is_admin 
                    FROM balasan 
                    WHERE ulasan_id = :ulasan_id 
                    ORDER BY dibuat_pada ASC";
        } else {
            $sql = "SELECT id, ulasan_id, nama, balasan, dibuat_pada 
                    FROM balasan 
                    WHERE ulasan_id = :ulasan_id 
                    ORDER BY dibuat_pada ASC";
        }

        $stmt = $db->prepare($sql);
        $stmt->bindParam(':ulasan_id', $ulasan_id, PDO::PARAM_INT);
        $stmt->execute();

        $balasan = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (!$has_admin_col) {
                $row['is_admin'] = ($row['nama'] === 'Admin CNS') ? 1 : 0;
            }
            $balasan[] = $row;
        }

        echo json_encode($balasan);
    } else {
        echo json_encode([]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Gagal mengambil data balasan']);
}
?>
