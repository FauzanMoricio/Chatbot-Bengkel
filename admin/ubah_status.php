<?php
require_once '../config/Database.php';
require_once '../classes/Booking.php';

$db = (new Database())->connect();
$booking = new Booking($db);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $status = $_POST['status'] ?? '';

    if ($id && $status) {
        $booking->ubahStatus($id, $status);
    }
}

header("Location: bokings.php");
exit;


?>