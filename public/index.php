<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CNS Motor</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <!-- Add Ionicons for icons -->
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
    <style>
        html {
            scroll-behavior: smooth;
        }
        .review-item {
            border-left: 3px solid #0d6efd;
        }
        .reply-form {
            display: none;
        }
        .review-replies {
            margin-left: 2rem;
            border-left: 2px solid #e5e7eb;
            padding-left: 1rem;
        }
        df-messenger {
            --df-messenger-bot-message: #d1d5dc;
            --df-messenger-button-titlebar-color: #130f40;
            --df-messenger-font-color: black;
            --df-messenger-send-icon: black;
            --df-messenger-user-message: #c7ecee;
            z-index: 999;
        }
    </style>
</head>
<body class="bg-gray-50 scr">
  
<!-- CNSBot -->
 <section id="chatbot">
  <div>
      <script src="https://www.gstatic.com/dialogflow-console/fast/messenger/bootstrap.js?v=1"></script>
      <df-messenger
          intent="WELCOME"
          chat-title="CNSbot"
          agent-id="6265f5e1-ad94-4dc0-8e84-d45f14350ae5"
          language-code="id"
          chat-icon="../assets/img/logocns.png">
      </df-messenger>
  </div>
</section>
<!-- End CNSBot -->

<!-- NAVBAR -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white shadow-md py-1 px-4" x-data="{ navOpen: false, activeMenu: 'home' }">
  <div class="container mx-auto">
    <div class="flex justify-between items-center">
      <div class="flex items-center">
        <a href="#home"><img src="../assets/img/cnslogo.png" width="60" height="50" class="mr-2 rounded-md"></a>
        <span class="text-2xl font-bold text-blue-400">Motor</span>
      </div>
      <button @click="navOpen = !navOpen" class="lg:hidden bg-blue-600 hover:bg-blue-700 transition-colors rounded-md p-2 focus:outline-none">
        <img src="../assets/img/toggle.svg" alt="toggle" width="30" height="30">
      </button>
      <div class="hidden lg:block">
        <ul class="flex gap-8 text-xl font-bold ">
          <li @click="activeMenu = 'home'" :class="activeMenu === 'home' ? 'text-blue-800 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
            <a href="#home" class="hover:text-blue-600">Home</a>
          </li>
          <li @click="activeMenu = 'jadwal'" :class="activeMenu === 'jadwal' ? 'text-blue-800 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
            <a href="#jadwal" class="hover:text-blue-600">Jadwal</a>
          </li>
          <li @click="activeMenu = 'ulasan'" :class="activeMenu === 'ulasan' ? 'text-blue-800 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
            <a href="#ulasan" class="hover:text-blue-600">Ulasan</a>
          </li>
        </ul>
      </div>
    </div>
  </div>

  <div x-show="navOpen" class="fixed right-0 left-0 bg-white shadow-lg border-gray-200 p-4 lg:hidden"
      x-transition:enter="transition ease-out duration-300"
      x-transition:enter-start="opacity-0 translate-y-full"
      x-transition:enter-end="opacity-100 translate-y-0"
      x-transition:leave="transition ease-in duration-300"
      x-transition:leave-start="opacity-100 translate-y-0"
      x-transition:leave-end="opacity-0 translate-y-full">
      <ul class="flex justify-between px-6" x-data="{ activeMenu: 'home' }">
      <li @click="activeMenu = 'home'" :class="activeMenu === 'home' ? 'text-blue-500 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
        <a href="#home" class="flex flex-col items-center gap-1 focus:outline-none">
          <ion-icon name="home" class="text-2xl" :class="activeMenu === 'home' ? 'text-blue-800' : 'text-gray-500 hover:text-blue-600'"></ion-icon>
          <span :class="activeMenu === 'home' ? 'text-blue-500 font-bold' : 'text-gray-600'">Home</span>
        </a>
      </li>
      <li @click="activeMenu = 'jadwal'" :class="activeMenu === 'jadwal' ? 'text-blue-500 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
        <a href="#jadwal" class="flex flex-col items-center gap-1 focus:outline-none">
          <ion-icon name="information-circle" class="text-2xl" :class="activeMenu === 'jadwal' ? 'text-blue-800' : 'text-gray-500 hover:text-blue-600'"></ion-icon>
          <span :class="activeMenu === 'jadwal' ? 'text-blue-500 font-bold' : 'text-gray-600'">Jadwal</span>
        </a>
      </li>
      <li @click="activeMenu = 'ulasan'" :class="activeMenu === 'ulasan' ? 'text-blue-500 font-bold' : 'text-gray-600 font-normal'" class="transition-colors">
        <a href="#ulasan" class="flex flex-col items-center gap-1 focus:outline-none">
          <ion-icon name="help-circle" class="text-2xl" :class="activeMenu === 'ulasan' ? 'text-blue-800' : 'text-gray-500 hover:text-blue-600'"></ion-icon>
          <span :class="activeMenu === 'ulasan' ? 'text-blue-500 font-bold' : 'text-gray-600'">Ulasan</span>
        </a>
      </li>
</ul>

  </div>
</nav>
<!-- NAVBAR -->

<!-- Home Section -->
<section id="home" class="relative overflow-hidden">
  <div class="min-h-screen w-full bg-cover bg-center bg-no-repeat" style="background-image: url('../assets/img/cns.jpg');">
    <div class="bg-black/50 min-h-screen w-full flex items-center px-4 sm:px-6 overflow-auto">
      <div class="container mx-auto py-16 md:py-24">
        <div class="text-center mb-12">
          <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4 mt-4">Panduan CNSbot</h2>
          <p class="text-white max-w-2xl mx-auto text-base sm:text-lg">
            Panduan pertanyaan umum tentang cara memulai dan menggunakan Chatbot kami melalui CNSbot.
          </p>
        </div>

        <div class="max-w-3xl mx-auto space-y-5">
          <!-- FAQ Item -->
          <details class="group bg-white/90 rounded-xl shadow-md transition-all duration-300">
            <summary class="flex items-center justify-between cursor-pointer p-6 text-gray-800 font-semibold text-base sm:text-lg rounded-xl hover:bg-gray-100 transition-all duration-300">
              <span>Apa itu CNSbot dan apa fungsinya?</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="px-6 pb-6 text-gray-700 text-sm sm:text-base">
              CNSbot adalah chatbot yang dirancang untuk membantu pengguna dalam memberikan informasi dan menjawab pertanyaan terkait bengkel CNS.
            </div>
          </details>

          <!-- FAQ Item -->
          <details class="group bg-white/90 rounded-xl shadow-md transition-all duration-300" open>
            <summary class="flex items-center justify-between cursor-pointer p-6 text-gray-800 font-semibold text-base sm:text-lg rounded-xl hover:bg-gray-100 transition-all duration-300">
              <span>Bagaimana cara memakai CNSbot?</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="px-6 pb-6 text-gray-700 text-sm sm:text-base">
              Tinggal klik logo CNS di bagian kanan bawah, lalu mulai percakapan dengan CNSbot.
            </div>
          </details>

          <!-- FAQ Item -->
          <details class="group bg-white/90 rounded-xl shadow-md transition-all duration-300">
            <summary class="flex items-center justify-between cursor-pointer p-6 text-gray-800 font-semibold text-base sm:text-lg rounded-xl hover:bg-gray-100 transition-all duration-300">
              <span>Apa saja percakapan yang ada pada CNSbot?</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="px-6 pb-6 text-gray-700 text-sm sm:text-base">
              Kami menyediakan percakapan seputar perawatan motor matic dan layanan bengkel.
            </div>
          </details>

          <!-- FAQ Item -->
          <details class="group bg-white/90 rounded-xl shadow-md transition-all duration-300">
            <summary class="flex items-center justify-between cursor-pointer p-6 text-gray-800 font-semibold text-base sm:text-lg rounded-xl hover:bg-gray-100 transition-all duration-300">
              <span>Perawatan dasar apa saja yang ada pada CNSbot?</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="px-6 pb-6 text-gray-700 text-sm sm:text-base">
              Pemeriksaan dan pengecekan komponen penting motor matic. Detailnya bisa ditanyakan ke CNSbot.
            </div>
          </details>
          <!-- FAQ Item -->
          <details class="group bg-white/90 rounded-xl shadow-md transition-all duration-300">
            <summary class="flex items-center justify-between cursor-pointer p-6 text-gray-800 font-semibold text-base sm:text-lg rounded-xl hover:bg-gray-100 transition-all duration-300">
              <span>bengekl CNS terdapat layanan apa saja?</span>
              <svg class="w-5 h-5 transition-transform duration-300 group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
              </svg>
            </summary>
            <div class="px-6 pb-6 text-gray-700 text-sm sm:text-base">
              Bengel CNS terdapat tiga layanan servis besar,kecil dan kelistrikan serta booking servis. Untuk lebih lanjut silahkan lihat pada menu CNSbot
            </div>
          </details>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- HOme -->

<!-- jadwal-->
<section id="jadwal" class="py-16 md:py-24 bg-gray-100">
    <?php
require_once '../config/Database.php';
require_once '../classes/Booking.php';

$db = (new Database())->connect();
$booking = new Booking($db);

// Ambil parameter GET
$sort = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'asc' : 'desc';
$status_filter = $_GET['status'] ?? '';
$keyword = $_GET['search'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

// Ambil semua data booking sesuai keyword dan sort
$allData = $booking->ambilSemua($sort, $keyword);

// Filter status jika ada
if (!empty($status_filter)) {
    $allData = array_filter($allData, function($row) use ($status_filter) {
        return $row['status'] === $status_filter;
    });
}

// Hitung total dan potong data sesuai halaman
$total_data = count($allData);
$total_pages = ceil($total_data / $limit);
$data = array_slice($allData, $offset, $limit);

$bookingBaru = $booking->jumlahBaru();

// Untuk query string yang tetap di pagination
function buildQuery($params, $exclude = []) {
    return http_build_query(array_diff_key($params, array_flip($exclude)));
}
$queryParams = $_GET;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Jadwal Booking Servis</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-5xl mx-auto bg-white shadow-md rounded-lg p-6">
  <h2 class="text-2xl md:text-4xl font-bold text-gray-900 mb-4 text-center px-2">Jadwal Booking Servis</h2>

  <!-- Filter dan Search -->
  <div class="bg-white rounded-lg shadow-sm border mb-6">
    <div class="p-4">
      <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:flex gap-2 w-full">
          <a href="?#jadwal" class="w-full text-center px-3 py-2 text-sm font-medium rounded-lg <?= empty($status_filter) ?  'bg-gray-900 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Semua
          </a>
          <a href="?<?= buildQuery(array_merge($queryParams, ['status' => 'Menunggu']), ['page']) ?>#jadwal" class="w-full text-center px-3 py-2 text-sm font-medium rounded-lg <?= $status_filter === 'Menunggu' ? 'bg-yellow-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Menunggu
          </a>
          <a href="?<?= buildQuery(array_merge($queryParams, ['status' => 'Silahkan Datang']), ['page']) ?>#jadwal" class="w-full text-center px-1 py-1 text-sm font-medium rounded-lg <?= $status_filter === 'Silahkan Datang' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Silahkan Datang
          </a>
          <a href="?<?= buildQuery(array_merge($queryParams, ['status' => 'Diproses']), ['page']) ?>#jadwal" class="w-full text-center px-3 py-2 text-sm font-medium rounded-lg <?= $status_filter === 'Diproses' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Diproses
          </a>
          <a href="?<?= buildQuery(array_merge($queryParams, ['status' => 'Selesai']), ['page']) ?>#jadwal" class="w-full text-center px-3 py-2 text-sm font-medium rounded-lg <?= $status_filter === 'Selesai' ? 'bg-green-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>">
            Selesai
          </a>
        </div>

        <div class="flex gap-2">
          <form method="get" class="flex gap-2">
            <input type="hidden" name="status" value="<?= htmlspecialchars($status_filter) ?>">
            <input type="hidden" name="sort" value="<?= $sort ?>">
            <input type="search" name="search" value="<?= htmlspecialchars($keyword) ?>" 
                   placeholder="Cari nama, tanggal (1 juni, juli), nomor HP..." 
                   class="border border-gray-300 rounded-lg px-3 py-2 text-sm w-64 focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
              Cari
            </button>
          </form>
        </div>
      </div>

      <?php if (empty($keyword)): ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 m-2">
            <h3 class="text-md font-medium text-blue-800 mb-2">Keterangan Status yang Diterima :</h3>
            <ul class="text-sm text-blue-700 space-y-1">
                <li>• Menunggu : Konsumen menunggu pihak bengkel untuk konfirmasi status selanjutnya</li>
                <li>• Silahkan Datang : jadwal Kosong dan konsumen bisa datang langsung ke bengkel sesuai jadwal</li>
                <li>• Diproses : Servis kendaraan sedang dilakukan</li>
                <li>• Selesai : Servis yang dilakukan telah selesai bisa diambil kembali kendaraan anda</li>
                <li>• Dibatalkan : Jadwal Penuh atau Konsumen tidak jadi melakukan Servis  </li>
            </ul>
            <h3 class="text-sm font-bold text-blue-800 my-2">Harap melihat dulu jadwal kosong dan Layanan yang tersedia Serta Estimasi Waktunya, Untuk Mempermudah proses booking </h3>
              <ul class="text-sm text-blue-700 space-y-1">
                <li>• Servis Besar : Estimasi Waktu lebih dari 2 - 3 ++ jam tergantung kerusakan</li>
                <li>• Servis Kecil : Estimasi Waktu 1 - 2 ++ jam tergantung kerusakan</li>
                <li>• Servis Kelistrikan : Estimasi Waktu 1 - 2 ++ jam tergantung kerusakan</li>
            </ul>
            <Strong>Layanan hanya dibagi tiga jenis : Besar, Ringan, Kelistrikan</Strong>
        </div>
        <?php endif; ?>

      <?php if (!empty($keyword)): ?>
        <div class="mt-3 text-sm text-gray-600">
          Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($keyword) ?>"</strong>
          <a href="?<?= buildQuery(array_merge($queryParams, ['search' => '']), ['page']) ?>" class="ml-2 text-blue-600 hover:underline">Hapus filter</a>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if (count($data) > 0): ?>
    <div class="w-full overflow-x-auto">
  <table class="min-w-[600px] sm:min-w-full border text-sm text-left">
    <thead class="bg-gray-100 text-gray-700">
      <tr>
        <th class="px-4 py-2 whitespace-nowrap">Nama</th>
        <th class="px-4 py-2 whitespace-nowrap">Motor</th>
        <th class="px-4 py-2 whitespace-nowrap">Layanan</th>
        <th class="px-4 py-2 whitespace-nowrap">Tanggal</th>
        <th class="px-4 py-2 whitespace-nowrap">Status</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($data as $row): ?>
        <tr class="border-t hover:bg-gray-50 transition">
          <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($row['nama']) ?></td>
          <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($row['jenis_motor']) ?></td>
          <td class="px-4 py-2 whitespace-nowrap"><?= htmlspecialchars($row['layanan']) ?></td>
          <td class="px-4 py-2 whitespace-nowrap"><?= date('d M Y H:i', strtotime($row['tanggal'])) ?></td>
          <td class="px-4 py-2 whitespace-nowrap">
            <span class="px-2 py-1 rounded-full text-xs font-medium
              <?= $row['status'] === 'Menunggu' ? 'bg-yellow-100 text-yellow-700' : '' ?>
              <?= $row['status'] === 'Silahkan Datang' ? 'bg-green-200 text-yellow-800' : '' ?>
              <?= $row['status'] === 'Diproses' ? 'bg-blue-100 text-blue-700' : '' ?>
              <?= $row['status'] === 'Selesai' ? 'bg-green-100 text-green-700' : '' ?>
              <?= $row['status'] === 'Dibatalkan' ? 'bg-red-100 text-red-700' : '' ?>">
              <?= $row['status'] ?>
            </span>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


    <!-- Pagination -->
    <div class="mt-6 flex justify-center space-x-2 text-sm">
      <?php if ($page > 1): ?>
        <a href="?<?= buildQuery(array_merge($queryParams, ['page' => $page - 1])) ?>" class="px-3 py-1 rounded border bg-white hover:bg-gray-100">« Prev</a>
      <?php endif; ?>

      <?php for ($i = 1; $i <= $total_pages; $i++): ?>
        <a href="?<?= buildQuery(array_merge($queryParams, ['page' => $i])) ?>"
           class="px-3 py-1 rounded border <?= $i == $page ? 'bg-blue-600 text-white' : 'bg-white hover:bg-gray-100' ?>">
          <?= $i ?>
        </a>
      <?php endfor; ?>

      <?php if ($page < $total_pages): ?>
        <a href="?<?= buildQuery(array_merge($queryParams, ['page' => $page + 1])) ?>" class="px-3 py-1 rounded border bg-white hover:bg-gray-100">Next »</a>
      <?php endif; ?>
    </div>

  <?php else: ?>
    <p class="text-gray-600">Belum ada jadwal booking yang tersedia.</p>
  <?php endif; ?>
</div>

</body>
</html>

</section>
<!-- jadwal -->


<!-- Ulasan -->
<section id="ulasan" class="py-16 md:py-24 bg-gray-50">
    <div class="container mx-auto px-4 md:px-8">
        <div class="text-center mb-12">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">Ulasan Pelanggan</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Berikan ulasan atau saran agar kami dapat berkembang dengan percakapan yang sesuai dengan kebutuhan anda.
            </p>
        </div>

        <div class="flex flex-col lg:flex-row lg:space-x-8 max-w-6xl mx-auto">
            <!-- Formulir Ulasan -->
            <div class="w-full lg:w-2/5 mb-8 lg:mb-0 bg-">
              <div class="bg-white p-6 rounded-lg shadow-lg border border-gray-200">
                  <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">Berikan Ulasan Anda</h3>
                  
                  <form id="ulasan-form" class="space-y-4">
                      <div>
                          <label for="nama" class="block text-gray-700 font-medium mb-2">Nama</label>
                          <input type="text" id="nama" name="nama" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm" placeholder="Masukkan nama Anda" required>
                      </div>

                      <div>
                          <label for="ulasan" class="block text-gray-700 font-medium mb-2">Ulasan Anda</label>
                          <textarea id="ulasan" name="ulasan" rows="6" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm resize-none" placeholder="Berikan ulasan atau saran Anda terkait bengkel CNS" required></textarea>
                      </div>

                      <div class="flex justify-center">
                          <button type="submit" class="px-6 py-3 bg-blue-600 text-white text-lg rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition duration-300 shadow-md">
                              Kirim Ulasan
                          </button>
                      </div>
                  </form>
              </div>
          </div>

            <!-- Daftar Ulasan -->
            <div class="w-full lg:w-3/5">
                <div class="reviews-container space-y-6"></div>
            </div>
            
        </div>
    </div>
</section>

<!-- Font Awesome untuk badge verifikasi -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">

<script>
document.getElementById("ulasan-form").addEventListener("submit", function(event) {
    event.preventDefault();
    
    let formData = new FormData(this);

    fetch("submit_ulasan.php", {
        method: "POST",
        body: formData
    }).then(response => response.text())
      .then(data => {
          alert(data);
          loadUlasan();
          // Reset form setelah submit berhasil
          this.reset();
      });
});

function loadUlasan() {
    fetch("get_ulasan.php")
        .then(response => response.json())
        .then(data => {
            let reviewsContainer = document.querySelector(".reviews-container");
            reviewsContainer.innerHTML = "";

            data.forEach(ulasan => {
                reviewsContainer.innerHTML += `
                    <div class="review-item bg-white p-6 rounded-lg shadow-md border border-gray-200">
                        <div class="flex items-center mb-3">
                          <div class="w-10 h-10 bg-teal-200 flex items-center justify-center rounded-full text-white mr-3">
                            <svg class="w8 h-8 text-black" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                              <path stroke-linecap="round" stroke-linejoin="round" d="M5.121 17.804A9.938 9.938 0 0112 15c2.21 0 4.247.716 5.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                          </div>
                            <div>
                                <h4 class="text-lg font-bold text-gray-900">${ulasan.nama}</h4>
                                <span class="text-xs text-gray-500">${new Date(ulasan.dibuat_pada).toLocaleDateString()}</span>
                            </div>
                        </div>

                        <p class="text-gray-700 text-sm mb-4">${ulasan.ulasan}</p>

                        <button onclick="toggleReplyForm(${ulasan.id})" class="text-blue-600 text-sm font-medium flex items-center hover:underline">
                            💬 Balas
                        </button>

                        <div id="reply-form-${ulasan.id}" class="hidden bg-gray-50 p-4 rounded-lg shadow-sm mt-4 border border-gray-300">
                            <h4 class="text-lg font-bold text-gray-900 mb-3">Balas Ulasan</h4>
                            <input type="text" id="reply-nama-${ulasan.id}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm mb-2" placeholder="Nama">
                            <textarea id="reply-text-${ulasan.id}" rows="4" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm resize-none mb-3" placeholder="Balasan"></textarea>
                            <div class="flex justify-end space-x-2">
                                <button onclick="toggleReplyForm(${ulasan.id})" class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-100 transition duration-300">Batal</button>
                                <button onclick="submitReply(${ulasan.id})" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-md transition duration-300">Kirim</button>
                            </div>
                        </div>

                        <div class="review-replies mt-4" id="balasan-${ulasan.id}"></div>
                    </div>
                `;
                loadBalasan(ulasan.id);
            });
        })
        .catch(error => {
            console.error("Error loading reviews:", error);
        });
}

function loadBalasan(ulasanId) {
    fetch(`get_balasan.php?ulasan_id=${ulasanId}`)
        .then(response => response.json())
        .then(data => {
            let balasanContainer = document.getElementById(`balasan-${ulasanId}`);
            balasanContainer.innerHTML = "";

            data.forEach(balasan => {
                // Cek apakah balasan dari admin (berdasarkan nama atau flag is_admin)
                const isAdmin = balasan.is_admin == 1 || balasan.nama === "Admin CNS";
                
                balasanContainer.innerHTML += `
                    <div class="${isAdmin ? 'bg-blue-50 border-blue-200' : 'bg-gray-50 border-gray-200'} p-4 rounded-lg shadow-sm mt-2 border">
                        <div class="flex items-center">
                            <h5 class="${isAdmin ? 'font-bold text-blue-600' : 'font-bold'}">${balasan.nama}</h5>
                            ${isAdmin ? 
                              `<span class="ml-2 bg-blue-600 text-white text-xs px-2 py-1 rounded-full flex items-center">
                                  <i class="fas fa-check-circle mr-1"></i> Asli
                               </span>` : ''}
                        </div>
                        <p class="mt-2 ${isAdmin ? 'text-blue-600' : ''}">${balasan.balasan}</p>
                    </div>
                `;
            });
        })
        .catch(error => {
            console.error("Error loading replies:", error);
        });
}

function submitReply(ulasanId) {
    let nama = document.getElementById(`reply-nama-${ulasanId}`).value;
    let balasan = document.getElementById(`reply-text-${ulasanId}`).value;
    
    if (!nama.trim() || !balasan.trim()) {
        alert("Nama dan balasan tidak boleh kosong!");
        return;
    }

    let formData = new FormData();
    formData.append("ulasan_id", ulasanId);
    formData.append("nama", nama);
    formData.append("balasan", balasan);

    fetch("submit_balasan.php", {
        method: "POST",
        body: formData
    }).then(response => response.text())
      .then(data => {
          alert(data);
          loadBalasan(ulasanId);
          // Reset form setelah submit berhasil
          document.getElementById(`reply-nama-${ulasanId}`).value = "";
          document.getElementById(`reply-text-${ulasanId}`).value = "";
          toggleReplyForm(ulasanId); // Tutup form setelah submit
      })
      .catch(error => {
          alert("Terjadi kesalahan saat mengirim balasan.");
          console.error("Error submitting reply:", error);
      });
}

function toggleReplyForm(ulasanId) {
    document.getElementById(`reply-form-${ulasanId}`).classList.toggle("hidden");
}

// Load ulasan saat halaman dimuat
loadUlasan();
</script>
<!-- ULASAN -->


<!-- Footer Start -->
<footer class="bg-gray-900 text-center py-2 mt-0">
    <div class="social-icons py-4 flex justify-center space-x-6">
    <a href="../admin/login.php"><img src="../assets/img/logocns.png" alt="" width="80px"></a>
    </div>

    <div class="footer-links mb-6 space-x-4">
        <a href="#home" class="text-white hover:text-blue-600 text-base px-3 py-2 transition duration-300">Home</a>
        <a href="#jadwal" class="text-white hover:text-blue-600 text-base  px-3 py-2 transition duration-300">jadwal</a>
        <a href="#ulasan" class="text-white hover:text-blue-600 text-base  px-3 py-2 transition duration-300">Ulasan</a>
    </div>

    <div class="credit text-sm text-white mb-4">   
        <p>Created by <a href="" class="font-bold hover:text-indigo-100 transition duration-300">CNS</a>. | &copy; <?php echo date('Y'); ?>.</p>
    </div>
</footer>
<!-- Footer End -->
</body>
</html>


