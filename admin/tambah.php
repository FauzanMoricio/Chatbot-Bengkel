<?php
require_once '../config/Database.php';

$error = '';
$success = '';
$responses = [];

// Inisialisasi koneksi DB
$db = (new Database())->connect();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keywords = trim($_POST['keywords'] ?? '');
    $mode = $_POST['mode'] ?? '';
    $mode = strtolower($mode);

    // Validasi awal
    if (empty($keywords)) {
        $error = 'Kata kunci tidak boleh kosong.';
    } elseif (!in_array($mode, ['form', 'json'])) {
        $error = 'Silakan pilih mode input yang valid (form atau json).';
    } else {
        // ==================
        // Mode: Manual JSON
        // ==================
        if ($mode === 'json') {
            $json = $_POST['json_response'] ?? '';
            if (empty(trim($json))) {
                $error = 'Silakan isi JSON response.';
            } else {
                $decoded = json_decode($json, true);
                if (!is_array($decoded)) {
                    $error = 'Format JSON tidak valid.';
                } elseif (empty($decoded)) {
                    $error = 'JSON tidak boleh kosong.';
                } else {
                    $responses = $decoded;
                }
            }
        }

        // ==================
        // Mode: Form Builder
        // ==================
        elseif ($mode === 'form') {
            $order = $_POST['order'] ?? [];
            if (empty($order)) {
                $error = 'Minimal harus ada satu respons pada form.';
            } else {
                $texts = $_POST['texts'] ?? [];
                $images = $_POST['images'] ?? [];
                $chip_texts = $_POST['chip_texts'] ?? [];
                $chip_events = $_POST['chip_events'] ?? [];
                $chip_links = $_POST['chip_links'] ?? [];
                $info_titles = $_POST['info_titles'] ?? [];
                $info_subtitles = $_POST['info_subtitles'] ?? [];
                $info_images = $_POST['info_images'] ?? [];
                $info_links = $_POST['info_links'] ?? [];

                $ti = $ii = $ci = $ifi = 0;

                foreach ($order as $type) {
                    if ($type === 'text') {
                        $val = trim($texts[$ti++] ?? '');
                        if ($val !== '') {
                            $responses[] = ['text' => ['text' => [$val]]];
                        }
                    } elseif ($type === 'image') {
                        $url = trim($images[$ii++] ?? '');
                        if (filter_var($url, FILTER_VALIDATE_URL)) {
                            $responses[] = ['image' => ['imageUri' => $url]];
                        }
                    } elseif ($type === 'chip') {
                        $label = trim($chip_texts[$ci] ?? '');
                        $event = trim($chip_events[$ci] ?? '');
                        $link = trim($chip_links[$ci] ?? '');
                        $ci++;

                        if ($label !== '') {
                            $option = ['text' => $label];
                            if ($event !== '') {
                                $option['event'] = ['name' => 'sendText', 'parameters' => ['text' => $event]];
                            } elseif ($link !== '' && filter_var($link, FILTER_VALIDATE_URL)) {
                                $option['link'] = $link;
                            }

                            $responses[] = [
                                'payload' => [
                                    'richContent' => [
                                        [[
                                            'type' => 'chips',
                                            'options' => [$option]
                                        ]]
                                    ]
                                ]
                            ];
                        }
                    } elseif ($type === 'info') {
                        $title = trim($info_titles[$ifi] ?? '');
                        $subtitle = trim($info_subtitles[$ifi] ?? '');
                        $image = trim($info_images[$ifi] ?? '');
                        $link = trim($info_links[$ifi] ?? '');
                        $ifi++;

                        if ($title !== '') {
                            $info = [
                                'type' => 'info',
                                'title' => $title,
                                'subtitle' => $subtitle
                            ];
                            if (filter_var($image, FILTER_VALIDATE_URL)) {
                                $info['image'] = ['src' => ['rawUrl' => $image]];
                            }
                            if (filter_var($link, FILTER_VALIDATE_URL)) {
                                $info['actionLink'] = $link;
                            }

                            $responses[] = [
                                'payload' => [
                                    'richContent' => [
                                        [$info]
                                    ]
                                ]
                            ];
                        }
                    }
                }

                if (empty($responses)) {
                    $error = 'Isi respons tidak boleh kosong.';
                }
            }
        }

        // ==================
        // Simpan ke Database
        // ==================
        if (empty($error)) {
            try {
                $stmt = $db->prepare("INSERT INTO responses (keywords, response_json, created_at) VALUES (?, ?, NOW())");
                $stmt->execute([
                    $keywords,
                    json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
                ]);

                header("Location: index.php?success=1");
                exit;
            } catch (PDOException $e) {
                if ($e->getCode() == 23000) {
                    $error = "❌ Keyword sudah Ada. Gunakan keyword (katakunci) lain.";
                } else {
                    $error = "❌ Gagal menyimpan: Webhook belum aktif atau terjadi kesalahan sistem.";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Respons Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen py-10 text-gray-800 font-sans">

<div x-data="{ open: false }" class="flex min-h-screen bg-gray-100">
    <!-- Include the sidebar -->
    <?php include 'sidebar.php' ?>

    <!-- Overlay for mobile sidebar -->
    <div 
        @click="open = false"
        :class="open ? 'opacity-50 pointer-events-auto' : 'opacity-0 pointer-events-none'"
        class="fixed inset-0 z-20 bg-black transition-opacity duration-300 ease-in-out md:hidden">
    </div>
    
<!-- Main Content -->
<div class="flex-1 p-4 mt-16 md:p-8 md:mt-0 ">
<div class="max-w-5xl mx-auto bg-white p-6 rounded-lg shadow-lg">
    <h2 class="text-2xl font-semibold mb-4">Tambah Respons Chatbot</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <div>
            <label class="block font-medium">Kata Kunci:</label>
            <input type="text" name="keywords" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label class="block font-medium">Mode Input:</label>
            <select name="mode" id="modeSelect" onchange="toggleJson()" class="w-full border px-3 py-2 rounded">
                <option value="form">Form</option>
                <!-- <option value="json">Manual JSON</option> -->
            </select>
        </div>

        <div id="jsonInput" class="hidden">
            <label class="block font-medium">JSON Response:</label>
            <textarea name="json_response" rows="6" class="w-full border px-3 py-2 rounded" placeholder='[{"text": {"text": ["Halo!"]}}]'></textarea>
        </div>

        <div id="formInput">
            <label class="block font-medium">Tambah Respons:</label>
            <div id="responseList" class="space-y-4"></div>

            <div class="flex flex-wrap gap-2 mt-2">
                <button type="button" onclick="addResponse('text')" class="px-3 py-1 bg-blue-500 text-white rounded">+ Text</button>
                <button type="button" onclick="addResponse('image')" class="px-3 py-1 bg-green-500 text-white rounded">+ Image</button>
                <button type="button" onclick="addResponse('chip')" class="px-3 py-1 bg-yellow-500 text-white rounded">+ Menu</button>
                <button type="button" onclick="addResponse('info')" class="px-3 py-1 bg-purple-500 text-white rounded">+ Video</button>
            </div>
        </div>

        <div class="flex justify-between items-center mt-6">
            <a href="index.php" class="text-sm text-blue-600 hover:underline">&larr; Kembali ke Daftar</a>
            <button type="submit" class="bg-blue-500 text-white px-5 py-2 rounded">Simpan</button>
        </div>
    </form>
</div>

<script>
function toggleJson() {
    const mode = document.getElementById("modeSelect").value;
    document.getElementById("jsonInput").style.display = (mode === 'json') ? 'block' : 'none';
    document.getElementById("formInput").style.display = (mode === 'json') ? 'none' : 'block';
}

function addResponse(type) {
    const container = document.createElement("div");
    container.classList.add("border", "p-3", "rounded", "bg-gray-50", "relative");

    const closeBtn = document.createElement("button");
    closeBtn.type = "button";
    closeBtn.innerHTML = "&#10006;";
    closeBtn.className = "absolute top-1 right-2 text-red-500 hover:text-red-700 text-xl";
    closeBtn.onclick = () => container.remove();

    container.innerHTML = `<input type="hidden" name="order[]" value="${type}">`;

    if (type === 'text') {
        container.innerHTML += `<label>Text:</label><input type="text" name="texts[]" class="w-full border px-2 py-1 rounded">`;
    } else if (type === 'image') {
        container.innerHTML += `<label>Image URL:</label><input type="text" name="images[]" class="w-full border px-2 py-1 rounded">`;
    } else if (type === 'chip') {
        container.innerHTML += `
            <label>Menu Chip Text:</label><input type="text" name="chip_texts[]" class="w-full border px-2 py-1 rounded">
            <label>Text Event (opsional):</label><input type="text" placeholder="Kosongkan Jika Tidak Ada" name="chip_events[]" class="w-full border px-2 py-1 rounded">
            <label>Link Aksi (opsional):</label><input type="text" placeholder="Kosongkan Jika Tidak Ada" name="chip_links[]" class="w-full border px-2 py-1 rounded">
        `;
    } else if (type === 'info') {
        container.innerHTML += `
            <label>Judul:</label><input type="text" name="info_titles[]" class="w-full border px-2 py-1 rounded">
            <label>Platform:</label><input type="text" name="info_subtitles[]" class="w-full border px-2 py-1 rounded">
            <label>Logo Gambar:</label><input type="text" placeholder="Opsional (Kosongkan Jika Tidak Ada)" name="info_images[]" class="w-full border px-2 py-1 rounded">
            <label>Link Aksi:</label><input type="text" name="info_links[]" class="w-full border px-2 py-1 rounded">
        `;
    }

    container.appendChild(closeBtn);
    document.getElementById("responseList").appendChild(container);
}
</script>
</body>
</html>
