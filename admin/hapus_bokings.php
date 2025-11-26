<?php
require_once '../config/Database.php';
require_once '../classes/Booking.php';

if (isset($_GET['id'])) {
  $id = (int) $_GET['id'];

  $db = (new Database())->connect();
  $booking = new Booking($db);

  $booking->hapus($id);
}

// Redirect kembali ke halaman bookings
header('Location: bokings.php');
exit;
