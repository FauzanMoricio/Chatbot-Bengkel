<?php
require_once '../config/Database.php';

$error = '';
$success = '';
$responses = [];
$keywords = '';
$mode = 'form';

$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
if (!$id) {
    die('ID tidak ditemukan.');
}

$db = (new Database())->connect();

// Ambil data dari database
$stmt = $db->prepare("SELECT * FROM responses WHERE id = ?");
$stmt->execute([$id]);
$row = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    die('Data tidak ditemukan.');
}

$keywords = $row['keywords'];
$storedResponses = json_decode($row['response_json'], true);
$responses = $storedResponses ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $keywords = trim($_POST['keywords'] ?? '');
    $mode = $_POST['mode'] ?? 'form';
    $responses = [];

    // Validasi kata kunci
    if (empty($keywords)) {
        $error = '❌ Kata kunci tidak boleh kosong.';
    } else {
        if ($mode === 'json') {
            $json = $_POST['json_response'] ?? '[]';
            $decoded = json_decode($json, true);
            if (!is_array($decoded)) {
                $error = '❌ Format JSON tidak valid.';
            } elseif (count($decoded) === 0) {
                $error = '❌ Minimal harus ada satu respons di JSON.';
            } else {
                $responses = $decoded;
            }
        } else {
            // Ambil input form
            $order = $_POST['order'] ?? [];
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
                $error = '❌ Minimal harus ada satu respons diisi.';
            }
        }

        // Proses penyimpanan jika tidak ada error
        if (empty($error)) {
            try {
                // Pastikan keyword tidak duplikat untuk ID lain
                $check = $db->prepare("SELECT COUNT(*) FROM responses WHERE keywords = ? AND id != ?");
                $check->execute([$keywords, $id]);
                if ($check->fetchColumn() > 0) {
                    $error = '❌ Keyword sudah digunakan oleh data lain. Gunakan keyword yang berbeda.';
                } else {
                    $stmt = $db->prepare("UPDATE responses SET keywords = ?, response_json = ? WHERE id = ?");
                    $stmt->execute([
                        $keywords,
                        json_encode($responses, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
                        $id
                    ]);
                    header("Location: index.php?success=1");
                    exit;
                }
            } catch (PDOException $e) {
                $error = 'Terjadi kesalahan: ' . $e->getMessage();
            }
        }
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Respons</title>
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
    <h2 class="text-2xl font-semibold mb-4">Edit Respons Chatbot</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?= htmlspecialchars($error) ?></div>
    <?php elseif ($success): ?>
        <div class="bg-green-100 text-green-700 p-3 rounded mb-4"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
        <input type="hidden" name="id" value="<?= (int) $id ?>">

        <div>
            <label class="block font-medium">Kata Kunci:</label>
            <input type="text" name="keywords" value="<?= htmlspecialchars($keywords) ?>" required class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label class="block font-medium">Mode Input:</label>
            <select name="mode" id="modeSelect" onchange="toggleJson()" class="w-full border px-3 py-2 rounded">
                <option value="form" <?= $mode === 'form' ? 'selected' : '' ?>>Form</option>
                <!-- <option value="json" <?= $mode === 'json' ? 'selected' : '' ?>>Manual JSON</option> -->
            </select>
        </div>

        <div id="jsonInput" class="<?= $mode === 'json' ? '' : 'hidden' ?>">
            <label class="block font-medium">JSON Response:</label>
            <textarea name="json_response" rows="6" class="w-full border px-3 py-2 rounded"><?= htmlspecialchars(json_encode($storedResponses, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) ?></textarea>
        </div>

        <div id="formInput" class="<?= $mode === 'json' ? 'hidden' : '' ?>">
            <label class="block font-medium">Edit Respons:</label>
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
            <button type="submit" class="bg-yellow-500 text-white px-5 py-2 rounded">Ubah</button>
        </div>
    </form>
</div>

<script>
function toggleJson() {
    const mode = document.getElementById("modeSelect").value;
    document.getElementById("jsonInput").style.display = (mode === 'json') ? 'block' : 'none';
    document.getElementById("formInput").style.display = (mode === 'json') ? 'none' : 'block';
}

function addResponse(type, data = {}) {
    const container = document.createElement("div");
    container.classList.add("border", "p-3", "rounded", "bg-gray-50", "relative");

    const closeBtn = document.createElement("button");
    closeBtn.type = "button";
    closeBtn.innerHTML = "&#10006;";
    closeBtn.className = "absolute top-1 right-2 text-red-500 hover:text-red-700 text-xl";
    closeBtn.onclick = () => container.remove();

    container.innerHTML = `<input type="hidden" name="order[]" value="${type}">`;

    if (type === 'text') {
        container.innerHTML += `<label>Text:</label><input type="text" name="texts[]" class="w-full border px-2 py-1 rounded" value="${data.text || ''}">`;
    } else if (type === 'image') {
        container.innerHTML += `<label>Image URL:</label><input type="text" name="images[]" class="w-full border px-2 py-1 rounded" value="${data.url || ''}">`;
    } else if (type === 'chip') {
        container.innerHTML += `
            <label>Chip Label:</label><input type="text" name="chip_texts[]" class="w-full border px-2 py-1 rounded" value="${data.label || ''}">
            <label>Event (opsional):</label><input type="text" placeholder="Opsional (Kosongkan Jika Tidak Ada)" name="chip_events[]" class="w-full border px-2 py-1 rounded" value="${data.event || ''}">
            <label>Link (opsional):</label><input type="text" placeholder="Opsional (Kosongkan Jika Tidak Ada)" name="chip_links[]" class="w-full border px-2 py-1 rounded" value="${data.link || ''}">
        `;
    } else if (type === 'info') {
        container.innerHTML += `
            <label>Judul:</label><input type="text" name="info_titles[]" class="w-full border px-2 py-1 rounded" value="${data.title || ''}">
            <label>Platform:</label><input type="text" name="info_subtitles[]" class="w-full border px-2 py-1 rounded" value="${data.subtitle || ''}">
            <label>Logo Gambar:</label><input type="text"  placeholder="Opsional (Kosongkan Jika Tidak Ada)" name="info_images[]"  class="w-full border px-2 py-1 rounded" value="${data.image || ''}">
            <label>Link Aksi:</label><input type="text" name="info_links[]" class="w-full border px-2 py-1 rounded" value="${data.link || ''}">
        `;
    }

    container.appendChild(closeBtn);
    document.getElementById("responseList").appendChild(container);
}

// Auto-load existing responses into form
<?php
if ($mode === 'form') {
    foreach ($storedResponses as $res) {
        if (isset($res['text']['text'][0])) {
            echo "addResponse('text', {text: " . json_encode($res['text']['text'][0]) . "});\n";
        } elseif (isset($res['image']['imageUri'])) {
            echo "addResponse('image', {url: " . json_encode($res['image']['imageUri']) . "});\n";
        } elseif (isset($res['payload']['richContent'][0][0]['type']) && $res['payload']['richContent'][0][0]['type'] === 'chips') {
            $chip = $res['payload']['richContent'][0][0]['options'][0];
            $label = json_encode($chip['text']);
            $event = isset($chip['event']['parameters']['text']) ? json_encode($chip['event']['parameters']['text']) : '""';
            $link = isset($chip['link']) ? json_encode($chip['link']) : '""';
            echo "addResponse('chip', {label: $label, event: $event, link: $link});\n";
        } elseif (isset($res['payload']['richContent'][0][0]['type']) && $res['payload']['richContent'][0][0]['type'] === 'info') {
            $info = $res['payload']['richContent'][0][0];
            $title = json_encode($info['title'] ?? '');
            $subtitle = json_encode($info['subtitle'] ?? '');
            $image = isset($info['image']['src']['rawUrl']) ? json_encode($info['image']['src']['rawUrl']) : '""';
            $link = isset($info['actionLink']) ? json_encode($info['actionLink']) : '""';
            echo "addResponse('info', {title: $title, subtitle: $subtitle, image: $image, link: $link});\n";
        }
    }
}
?>
</script>
</body>
</html>
