<?php
require_once '../config/Database.php';
require_once '../classes/Booking.php';

// Cek apakah user sudah login
session_start();

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'login') {
    header("Location: login.php");
    exit();
}


$db = (new Database())->connect();
$booking = new Booking($db);

// Sorting dan search
$sort = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'asc' : 'desc';
$status_filter = $_GET['status'] ?? '';
$keyword = $_GET['search'] ?? '';

// Ambil data dengan filter
$data = $booking->ambilSemua($sort, $keyword);
if (!empty($status_filter)) {
    $data = array_filter($data, function($row) use ($status_filter) {
        return $row['status'] === $status_filter;
    });
}

$bookingBaru = $booking->jumlahBaru();
$statistik = $booking->getStatistik();


$booking->hapusBookingLama();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Booking Servis</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        ::-webkit-scrollbar-thumb {
            background: #c1c1c1;
            border-radius: 3px;
        }
        ::-webkit-scrollbar-thumb:hover {
            background: #a8a8a8;
        }
    </style>
</head>
<body class="bg-gray-100">

<div x-data="{ open: false }" class="flex min-h-screen bg-gray-100">
    
    <?php include 'sidebar.php' ?>

    <!-- Overlay for mobile sidebar -->
    <div 
        @click="open = false"
        :class="open ? 'opacity-50 pointer-events-auto' : 'opacity-0 pointer-events-none'"
        class="fixed inset-0 z-20 bg-black transition-opacity duration-300 ease-in-out md:hidden">
    </div>
    
    <!-- Main Content Area -->
    <div class="flex-1 overflow-y-auto bg-gray-50">
        <div class="p-4 pt-20 md:p-6 md:pt-6 max-w-7xl mx-auto w-full">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mb-2">Kelola Booking Servis</h1>
                        <p class="text-gray-600">Kelola dan pantau semua booking servis motor</p>
                    </div>
                    <div class="mt-4 sm:mt-0" x-show="bookingBaru > 0">
                        <a href="#booking-baru" class="inline-flex items-center px-4 py-2 bg-red-500 text-white text-sm font-medium rounded-lg hover:bg-red-600 animate-pulse transition-colors">
                            <span class="w-2 h-2 bg-white rounded-full mr-2 animate-ping"></span>
                            <span x-text="bookingBaru"></span> Booking Baru
                        </a>
                    </div>
                </div>
            </div>

            
            <!-- Filter dan Search -->
            <div class="bg-white rounded-lg shadow-sm border mb-6">
                <div class="p-4">
                    <!-- Status Filter Tabs -->
                    <div class="flex flex-wrap gap-2 mb-4">
                        <a href="?" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= empty($status_filter) ? 'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Semua
                        </a>
                        <a href="?status=Menunggu&search=<?= urlencode($keyword) ?>&sort=<?= $sort ?>" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= $status_filter === 'Menunggu' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Menunggu
                        </a>
                        <a href="?status=Silahkan Datang&search=<?= urlencode($keyword) ?>&sort=<?= $sort ?>" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= $status_filter === 'Silahkan Datang' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Silahkan Datang
                        </a>
                        <a href="?status=Diproses&search=<?= urlencode($keyword) ?>&sort=<?= $sort ?>" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= $status_filter === 'Diproses' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Diproses
                        </a>
                        <a href="?status=Dibatalkan&search=<?= urlencode($keyword) ?>&sort=<?= $sort ?>" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= $status_filter === 'Dibatalkan' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Dibatalkan
                        </a>
                        <a href="?status=Selesai&search=<?= urlencode($keyword) ?>&sort=<?= $sort ?>" class="px-3 py-2 text-xs sm:text-sm font-medium rounded-lg transition-colors <?= $status_filter === 'Selesai' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
                            Selesai
                        </a>
                    </div>
                    
                    <!-- Search and Sort -->
                    <div class="flex flex-col lg:flex-row gap-3 items-start lg:items-center">
                        <form method="get" class="flex flex-1 gap-2">
                            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
                            <input type="hidden" name="sort" value="<?= $sort ?>">
                            <div class="flex-1">
                                <input type="search" name="search" value="<?= htmlspecialchars($keyword) ?>" 
                                       placeholder="Cari nama, tanggal, nomor HP..." 
                                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700 transition-colors whitespace-nowrap">
                                Cari
                            </button>
                        </form>
                        
                        <a href="?sort=<?= $sort === 'asc' ? 'desc' : 'asc' ?>&search=<?= urlencode($keyword) ?>&status=<?= urlencode($status_filter) ?>"
                           class="bg-gray-100 text-gray-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-gray-200 transition-colors whitespace-nowrap">
                            <?= $sort === 'asc' ? 'Terbaru ↓' : 'Terlama ↑' ?>
                        </a>
                    </div>
                    
                    <?php if (!empty($keyword)): ?>
                    <div class="mt-3 text-sm text-gray-600">
                        Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
                        <a href="?status=<?= urlencode($status_filter) ?>&sort=<?= $sort ?>" class="ml-2 text-blue-600 hover:underline">Hapus filter</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistik Cards -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Booking Hari Ini</p>
                            <p class="text-xl sm:text-2xl font-bold text-gray-900"><?= $statistik['hari_ini'] ?></p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 text-sm">📅</span>
                        </div>
                    </div>
                </div>
    
                <div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Menunggu</p>
                            <p class="text-xl sm:text-2xl font-bold text-yellow-600"><?= $statistik['menunggu'] ?></p>
                        </div>
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-yellow-600 text-sm">⏳</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Dibatalkan</p>
                            <p class="text-xl sm:text-2xl font-bold text-blue-600"><?= $statistik['dibatalkan'] ?></p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 text-sm">⚙️</span>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow-sm border p-4 hover:shadow-md transition-shadow">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs sm:text-sm font-medium text-gray-600">Selesai Hari Ini</p>
                            <p class="text-xl sm:text-2xl font-bold text-green-600"><?= $statistik['selesai_hari_ini'] ?></p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 text-sm">✅</span>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Tabel Data -->
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <?php if (count($data) > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pelanggan</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Motor & Layanan</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jadwal</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-3 sm:px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $sudahAdaBaru = false;
                                foreach ($data as $i => $row):
                                    $baru = (strtotime($row['created_at']) >= strtotime('-10 minutes'));
                                    $barisId = ($baru && !$sudahAdaBaru) ? 'id="booking-baru"' : '';
                                    if ($baru) $sudahAdaBaru = true;
                                ?>
                                    <tr <?= $barisId ?> class="<?= $baru ? 'bg-yellow-50 border-l-4 border-yellow-400' : '' ?> hover:bg-gray-50 transition-colors">
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900 font-medium">
                                            <?= $i + 1 ?>
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900 flex items-center">
                                                        <?= htmlspecialchars($row['nama']) ?>
                                                        <?php if ($baru): ?>
                                                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                                Baru
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                    <div class="text-sm text-gray-500"><?= htmlspecialchars($row['no_hp']) ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-4">
                                            <div class="text-sm text-gray-900 font-medium"><?= htmlspecialchars($row['jenis_motor']) ?></div>
                                            <div class="text-sm text-gray-500"><?= htmlspecialchars($row['layanan']) ?></div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="font-medium"><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                                            <div class="text-gray-500 text-xs"><?= date('H:i', strtotime($row['tanggal'])) ?></div>
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap">
                                            <form method="post" action="ubah_status.php" class="inline">
                                                <input type="hidden" name="id" value="<?= $row['id'] ?>">
                                                <select name="status" class="text-xs sm:text-sm p-2 rounded-md border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500" onchange="this.form.submit()">
                                                    <option value="Menunggu" <?= $row['status'] == 'Menunggu' ? 'selected' : '' ?>>Menunggu</option>
                                                    <option value="Silahkan Datang" <?= $row['status'] == 'Silahkan Datang' ? 'selected' : '' ?>>Silahkan Datang</option>
                                                    <option value="Diproses" <?= $row['status'] == 'Diproses' ? 'selected' : '' ?>>Diproses</option>
                                                    <option value="Selesai" <?= $row['status'] == 'Selesai' ? 'selected' : '' ?>>Selesai</option>
                                                    <option value="Dibatalkan" <?= $row['status'] == 'Dibatalkan' ? 'selected' : '' ?>>Dibatalkan</option>
                                                </select>
                                            </form>
                                        </td>
                                        <td class="px-3 sm:px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div class="flex flex-col sm:flex-row gap-2">
                                                <!-- <button onclick="editBooking(<?= $row['id'] ?>)" 
                                                        class="text-blue-600 hover:text-blue-900 hover:underline text-sm transition-colors">
                                                    Edit
                                                </button> -->
                                                <a href="#" class="text-red-500 hover:text-red-700 hover:underline transition-colors duration-200"
                                                data-id="<?= (int)$row['id'] ?>" onclick="openDeleteModal(this)">
                                                Hapus
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-12">
                        <div class="w-16 h-16 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-4">
                            <span class="text-2xl text-gray-400">📋</span>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada booking</h3>
                        <p class="text-gray-500 text-sm">
                            <?php if (!empty($keyword)): ?>
                                Tidak ditemukan booking yang sesuai dengan pencarian "<?= htmlspecialchars($keyword) ?>"
                            <?php else: ?>
                                Belum ada booking yang masuk
                            <?php endif; ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Booking -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-md shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Edit Booking</h3>
                <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                    <span class="sr-only">Close</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <form id="editForm" class="space-y-4">
                <input type="hidden" id="edit_id" name="id">
                
                <div>
                    <label for="edit_nama" class="block text-sm font-medium text-gray-700">Nama Pelanggan</label>
                    <input type="text" id="edit_nama" name="nama" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div>
                    <label for="edit_no_hp" class="block text-sm font-medium text-gray-700">No. HP</label>
                    <input type="tel" id="edit_no_hp" name="no_hp" required pattern="[0-9]{10,15}"
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="08123456789">
                </div>
                
                <div>
                    <label for="edit_jenis_motor" class="block text-sm font-medium text-gray-700">Jenis Motor</label>
                    <input type="text" id="edit_jenis_motor" name="jenis_motor" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                           placeholder="Honda Beat, Yamaha Vario, dll">
                </div>
                
                <div>
                    <label for="edit_layanan" class="block text-sm font-medium text-gray-700">Layanan</label>
                    <select id="edit_layanan" name="layanan" required
                            class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                        <option value="">Pilih Layanan</option>
                        <option value="Servis Besar">Servis Besar</option>
                        <option value="Servis Kecil">Servis Kecil</option>
                        <option value="Servis Kelistrikan">Servis Kelistrikan</option>
                    </select>
                </div>
                
                <div>
                    <label for="edit_tanggal" class="block text-sm font-medium text-gray-700">Tanggal & Waktu</label>
                    <input type="datetime-local" id="edit_tanggal" name="tanggal" required
                           class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                </div>
                
                <div class="flex justify-end space-x-3 pt-4">
                    <button type="button" onclick="closeEditModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi -->
<div id="deleteModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black bg-opacity-40">
  <div class="bg-white rounded-lg shadow-lg w-full max-w-sm mx-auto p-6">
    <h2 class="text-xl font-bold text-red-600 mb-4">Konfirmasi Hapus</h2>
    <p class="text-gray-700 mb-6">Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dibatalkan.</p>
    <div class="flex justify-end gap-3">
      <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-300 hover:bg-gray-400 rounded text-gray-800">Batal</button>
      <a href="#" id="confirmDeleteLink" class="px-4 py-2 bg-red-600 hover:bg-red-700 rounded text-white">Hapus</a>
    </div>
  </div>
</div>

<!-- Loading Overlay -->
<div id="loadingOverlay" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 shadow-lg">
        <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Memproses...</span>
        </div>
    </div>
</div>

<script>
// Scroll otomatis ke booking baru
document.addEventListener('DOMContentLoaded', function() {
    const bookingBaru = document.getElementById('booking-baru');
    if (bookingBaru) {
        bookingBaru.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
});

// Auto refresh setiap 30 detik untuk cek booking baru
setInterval(function() {
    const bookingBaruCount = <?= $bookingBaru ?>;
    if (bookingBaruCount === 0) {
        location.reload();
    }
}, 30000);

// Fungsi untuk menampilkan modal edit
function editBooking(id) {
    showLoading();
    
    fetch(`edit_booking.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            hideLoading();
            
            if (data.success) {
                // Isi form dengan data booking
                document.getElementById('edit_id').value = data.data.id;
                document.getElementById('edit_nama').value = data.data.nama;
                document.getElementById('edit_no_hp').value = data.data.no_hp;
                document.getElementById('edit_jenis_motor').value = data.data.jenis_motor;
                document.getElementById('edit_layanan').value = data.data.layanan;
                
                // Format tanggal untuk datetime-local
                const tanggal = new Date(data.data.tanggal);
                const formattedDate = tanggal.getFullYear() + '-' + 
                    String(tanggal.getMonth() + 1).padStart(2, '0') + '-' + 
                    String(tanggal.getDate()).padStart(2, '0') + 'T' + 
                    String(tanggal.getHours()).padStart(2, '0') + ':' + 
                    String(tanggal.getMinutes()).padStart(2, '0');
                
                document.getElementById('edit_tanggal').value = formattedDate;
                
                // Tampilkan modal
                document.getElementById('editModal').classList.remove('hidden');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            alert('Terjadi kesalahan saat mengambil data booking');
        });
}

// Fungsi untuk menutup modal edit
function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
    document.getElementById('editForm').reset();
}

// Handle submit form edit
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    showLoading();
    
    const formData = new FormData(this);
    
    fetch('edit_booking.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        hideLoading();
        
        if (data.success) {
            alert('Booking berhasil diperbarui!');
            closeEditModal();
            location.reload(); // Refresh halaman untuk menampilkan perubahan
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        hideLoading();
        console.error('Error:', error);
        alert('Terjadi kesalahan saat menyimpan perubahan');
    });
});

// Fungsi untuk menampilkan loading
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

// Fungsi untuk menyembunyikan loading
function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

// Tutup modal jika klik di luar modal
window.addEventListener('click', function(event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeEditModal();
    }
});

// Tutup modal dengan tombol Escape
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeEditModal();
    }
});

//HapusBUTtON
function openDeleteModal(button) {
    const id = button.getAttribute('data-id');
    const deleteLink = document.getElementById('confirmDeleteLink');
    deleteLink.href = 'hapus_bokings.php?id=' + id;


    const modal = document.getElementById('deleteModal');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

function closeDeleteModal() {
    const modal = document.getElementById('deleteModal');
    modal.classList.remove('flex');
    modal.classList.add('hidden');
}
</script>

</body>
</html>