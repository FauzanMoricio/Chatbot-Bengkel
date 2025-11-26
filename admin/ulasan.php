<?php
require_once '../config/Database.php';

// Cek apakah user sudah login
session_start();

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'login') {
    header("Location: login.php");
    exit();
}

try {
    $db = new Database();
    $conn = $db->connect();

    $stmt = $conn->query("SELECT * FROM ulasan ORDER BY dibuat_pada DESC");
    $ulasanList = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "Gagal mengambil data: " . $e->getMessage();
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Ulasan & Balasan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.0/css/all.min.css">
</head>
<body class="bg-gray-100">
<div class="flex h-screen">
    <?php include 'sidebar.php'; ?>

    <div class="flex-1 p-4 mt-20 md:p-8 md:mt-0 overflow-y-auto max-h-screen">
        <div class="mb-6 text-center">
            <h1 class="text-xl md:text-2xl font-bold text-gray-800">Daftar Ulasan & Balasan</h1>
        </div>

        <?php foreach ($ulasanList as $row): ?>
        <div class="bg-white rounded-lg shadow-md p-4 mb-6 border max-w-4xl mx-auto">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-2">
                <div>
                    <p class="font-semibold text-gray-800">Nama: <?= htmlspecialchars($row["nama"]) ?></p>
                    <p class="text-gray-600">Ulasan: <?= htmlspecialchars($row["ulasan"]) ?></p>
                    <p class="text-xs text-gray-400"><?= date("d M Y", strtotime($row["dibuat_pada"])) ?></p>
                </div>
                <div class="flex gap-2 mt-2 md:mt-0">
                    <button onclick="toggleReplyForm(<?= $row['id']; ?>)" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">💬 Balas</button>
                    <button onclick="hapusUlasan(<?= $row['id']; ?>)" class="px-3 py-1 bg-red-600 text-white rounded text-sm hover:bg-red-700">❌ Hapus</button>
                </div>
            </div>

            <?php
            $stmtBalasan = $conn->prepare("SELECT * FROM balasan WHERE ulasan_id = :ulasan_id ORDER BY dibuat_pada ASC");
            $stmtBalasan->execute(['ulasan_id' => $row['id']]);
            $balasanList = $stmtBalasan->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <?php foreach ($balasanList as $balasan):
                $isAdmin = ($balasan["nama"] === "Admin CNS" || $balasan["is_admin"] == 1);
            ?>
            <div class="mt-3 ml-3 p-3 rounded border <?= $isAdmin ? 'bg-blue-50' : 'bg-gray-50' ?>">
                <p class="font-semibold <?= $isAdmin ? 'text-blue-700' : 'text-gray-700' ?>">
                    <?= htmlspecialchars($balasan["nama"]) ?>
                    <?= $isAdmin ? '<span class="ml-1 text-xs bg-blue-600 text-white px-2 py-1 rounded-full">Official</span>' : '(Balasan)' ?>
                </p>
                <p class="text-sm"><?= htmlspecialchars($balasan["balasan"]) ?></p>
                <div class="text-right mt-1">
                    <button onclick="hapusBalasan(<?= $balasan['id']; ?>)" class="text-xs text-red-600">❌ Hapus</button>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- ✅ Form Balas Responsif -->
            <div id="reply-row-<?= $row['id']; ?>" class="mt-4 hidden">
                <div class="bg-blue-50 p-4 rounded-lg shadow-md border border-blue-300">
                    <h4 class="text-lg font-bold text-gray-900 mb-2">Balas Ulasan</h4>
                    <input type="hidden" value="Admin CNS" id="reply-nama-<?= $row['id']; ?>">
                    <textarea id="reply-text-<?= $row['id']; ?>" rows="4" class="w-full px-4 py-2 border border-blue-300 rounded-lg resize-none mb-3 text-sm md:text-base" placeholder="Tulis balasan resmi Anda..."></textarea>
                    <div class="flex justify-end gap-2">
                        <button onclick="toggleReplyForm(<?= $row['id']; ?>)" class="px-4 py-2 border border-gray-300 rounded hover:bg-gray-100">Tutup</button>
                        <button onclick="submitReply(<?= $row['id']; ?>)" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Kirim</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
function hapusUlasan(id) {
    if (confirm("Apakah Anda yakin ingin menghapus ulasan ini?")) {
        fetch(`hapus_ulasan.php?id=${id}`, { method: "GET" })
        .then(res => res.text())
        .then(data => {
            alert(data);
            location.reload();
        });
    }
}

function hapusBalasan(id) {
    if (confirm("Apakah Anda yakin ingin menghapus balasan ini?")) {
        fetch(`hapus_balasan.php?id=${id}`, { method: "GET" })
        .then(res => res.text())
        .then(data => {
            alert(data);
            location.reload();
        });
    }
}

function toggleReplyForm(id) {
    const replyRow = document.getElementById(`reply-row-${id}`);
    if (replyRow) replyRow.classList.toggle("hidden");
}

function submitReply(id) {
    const nama = document.getElementById(`reply-nama-${id}`).value;
    const balasan = document.getElementById(`reply-text-${id}`).value;

    if (!balasan.trim()) {
        alert("Balasan tidak boleh kosong!");
        return;
    }

    const formData = new FormData();
    formData.append("ulasan_id", id);
    formData.append("nama", nama);
    formData.append("balasan", balasan);
    formData.append("is_admin", 1);

    fetch("tambah_balasan.php", {
        method: "POST",
        body: formData
    }).then(res => res.text())
      .then(data => {
          alert(data);
          location.reload();
      }).catch(() => {
          alert("Terjadi kesalahan saat mengirim balasan");
      });
}
</script>
</body>
</html>