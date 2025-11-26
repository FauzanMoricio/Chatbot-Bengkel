<?php
require_once '../config/Database.php';
require_once '../classes/Booking.php';

$db = (new Database())->connect();
$booking = new Booking($db);

header('Content-Type: application/json');

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Proses update booking
        $id = $_POST['id'] ?? '';
        $nama = trim($_POST['nama'] ?? '');
        $no_hp = trim($_POST['no_hp'] ?? '');
        $jenis_motor = trim($_POST['jenis_motor'] ?? '');
        $layanan = trim($_POST['layanan'] ?? '');
        $tanggal = $_POST['tanggal'] ?? '';

        // Validasi input
        if (empty($id) || empty($nama) || empty($no_hp) || empty($jenis_motor) || empty($layanan) || empty($tanggal)) {
            throw new Exception('Semua field harus diisi');
        }

        // Validasi format tanggal
        $tanggal_obj = DateTime::createFromFormat('Y-m-d\TH:i', $tanggal);
        if (!$tanggal_obj) {
            throw new Exception('Format tanggal tidak valid');
        }

        // Validasi nomor HP (harus berupa angka dan minimal 10 digit)
        if (!preg_match('/^[0-9]{10,15}$/', $no_hp)) {
            throw new Exception('Nomor HP harus berupa angka 10-15 digit');
        }

        // Update booking
        $result = $booking->updateBooking($id, $nama, $no_hp, $jenis_motor, $layanan, $tanggal);

        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => 'Booking berhasil diperbarui'
            ]);
        } else {
            throw new Exception('Gagal memperbarui booking');
        }

    } elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
        // Ambil data booking untuk edit
        $id = $_GET['id'];
        $data = $booking->ambilById($id);
        
        if ($data) {
            echo json_encode([
                'success' => true,
                'data' => $data
            ]);
        } else {
            throw new Exception('Booking tidak ditemukan');
        }
    } else {
        throw new Exception('Method tidak diizinkan');
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>