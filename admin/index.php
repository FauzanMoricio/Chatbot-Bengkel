<?php
// index fixed - Improved version with search and pagination
require_once '../config/Database.php';

// Cek apakah user sudah login
session_start();

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'login') {
    header("Location: login.php");
    exit();
}

// Pagination settings
$records_per_page = 10;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $records_per_page;

// Search functionality
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$search_condition = '';
$search_params = [];

if (!empty($search)) {
    $search_condition = "WHERE keywords LIKE :search OR response_json LIKE :search";
    $search_params[':search'] = '%' . $search . '%';
}

try {
    $db = (new Database())->connect();
    
    // Count total records for pagination
    $count_sql = "SELECT COUNT(*) as total FROM responses " . $search_condition;
    $count_stmt = $db->prepare($count_sql);
    $count_stmt->execute($search_params);
    $total_records = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total_records / $records_per_page);
    
    // Get paginated data
    $sql = "SELECT * FROM responses " . $search_condition . " ORDER BY keywords ASC LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($sql);
    
    // Bind search parameters
    foreach ($search_params as $key => $value) {
        $stmt->bindValue($key, $value, PDO::PARAM_STR);
    }
    
    // Bind pagination parameters
    $stmt->bindValue(':limit', $records_per_page, PDO::PARAM_INT);
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    
    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $data = [];
    $total_records = 0;
    $total_pages = 0;
    $error_message = "Error connecting to database atau Kesalahan pada pengambilan data SQL ";
}

// Function to render response content
function renderResponseContent($responses) {
    $output = '';
    
    if (!is_array($responses)) {
        return "<p class='text-red-500'>❌ Format response tidak valid</p>";
    }
    
    foreach ($responses as $res) {
        if (!is_array($res)) continue;
        
        // TEXT RESPONSE
        if (isset($res['text']['text']) && is_array($res['text']['text'])) {
            foreach ($res['text']['text'] as $text) {
                if (!empty(trim($text))) {
                    $output .= "<p class='text-gray-700 leading-relaxed mb-2'>📝 " . htmlspecialchars($text) . "</p>";
                }
            }
        }

        // IMAGE RESPONSE (Simple format)
        if (isset($res['image']['imageUri']) && !empty($res['image']['imageUri'])) {
            $url = htmlspecialchars($res['image']['imageUri']);
            $output .= "<div class='flex justify-start mt-2 mb-2'>";
            $output .= "<img src='$url' alt='Response Image' class='max-w-xs max-h-48 rounded shadow border object-contain' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
            $output .= "<div class='hidden text-red-500 text-xs'>❌ Gagal memuat gambar</div>";
            $output .= "</div>";
        }

        // RICH CONTENT RESPONSES (Dialogflow Messenger)
        if (isset($res['payload']['richContent']) && is_array($res['payload']['richContent'])) {
            foreach ($res['payload']['richContent'] as $group) {
                if (!is_array($group)) continue;
                
                foreach ($group as $item) {
                    if (!is_array($item) || !isset($item['type'])) continue;
                    
                    switch ($item['type']) {
                        case 'image':
                            if (!empty($item['rawUrl'])) {
                                $img = htmlspecialchars($item['rawUrl']);
                                $alt = isset($item['accessibilityText']) ? htmlspecialchars($item['accessibilityText']) : 'Gambar';
                                $output .= "<div class='flex justify-start mt-2 mb-2'>";
                                $output .= "<img src='$img' alt='$alt' class='max-w-xs max-h-48 rounded shadow border object-contain' onerror='this.style.display=\"none\"; this.nextElementSibling.style.display=\"block\";'>";
                                $output .= "<div class='hidden text-red-500 text-xs'>❌ Gagal memuat gambar</div>";
                                $output .= "</div>";
                            }
                            break;
                            
                        case 'chips':
                            if (isset($item['options']) && is_array($item['options'])) {
                                $output .= "<div class='flex flex-wrap gap-2 mt-2 mb-2'>";
                                foreach ($item['options'] as $chip) {
                                    if (isset($chip['text']) && !empty($chip['text'])) {
                                        $label = htmlspecialchars($chip['text']);
                                        if (isset($chip['event'])) {
                                            $output .= "<span class='px-3 py-1 bg-yellow-300 text-gray-800 rounded-full text-xs font-medium'>⚡ $label</span>";
                                        } elseif (isset($chip['link']) && !empty($chip['link'])) {
                                            $link = htmlspecialchars($chip['link']);
                                            $output .= "<a href='$link' target='_blank' rel='noopener noreferrer' class='px-3 py-1 bg-blue-300 text-white rounded-full text-xs font-medium hover:bg-blue-400 transition-colors duration-200'>$label 🔗</a>";
                                        } else {
                                            $output .= "<span class='px-3 py-1 bg-gray-300 text-gray-700 rounded-full text-xs font-medium'>$label</span>";
                                        }
                                    }
                                }
                                $output .= "</div>";
                            }
                            break;
                            
                        case 'info':
                            $output .= "<div class='border border-purple-300 bg-purple-50 p-4 rounded-lg mt-3 mb-2'>";
                            
                            if (isset($item['title']) && !empty($item['title'])) {
                                $output .= "<div class='font-bold text-purple-800 text-base mb-1'>" . htmlspecialchars($item['title']) . "</div>";
                            }
                            
                            if (isset($item['subtitle']) && !empty($item['subtitle'])) {
                                $output .= "<div class='text-sm text-purple-600 mb-2'>" . htmlspecialchars($item['subtitle']) . "</div>";
                            }
                            
                            if (isset($item['image']['src']['rawUrl']) && !empty($item['image']['src']['rawUrl'])) {
                                $img = htmlspecialchars($item['image']['src']['rawUrl']);
                                $output .= "<img src='$img' alt='Info Image' class='w-32 h-24 object-cover rounded mb-2 border' onerror='this.style.display=\"none\";'>";
                            }
                            
                            if (isset($item['actionLink']) && !empty($item['actionLink'])) {
                                $link = htmlspecialchars($item['actionLink']);
                                $output .= "<a href='$link' target='_blank' rel='noopener noreferrer' class='inline-block text-blue-600 underline text-xs hover:text-blue-800 transition-colors duration-200'>🔗 Lihat Detail</a>";
                            }
                            
                            $output .= "</div>";
                            break;
                            
                        case 'list':
                            if (isset($item['title'])) {
                                $output .= "<div class='border border-green-300 bg-green-50 p-4 rounded-lg mt-3 mb-2'>";
                                $output .= "<div class='font-bold text-green-800 text-base mb-2'>" . htmlspecialchars($item['title']) . "</div>";
                                
                                if (isset($item['items']) && is_array($item['items'])) {
                                    $output .= "<ul class='space-y-2'>";
                                    foreach ($item['items'] as $listItem) {
                                        if (isset($listItem['title'])) {
                                            $output .= "<li class='flex items-start'>";
                                            $output .= "<span class='mr-2'>•</span>";
                                            $output .= "<div>";
                                            $output .= "<div class='font-medium text-green-700'>" . htmlspecialchars($listItem['title']) . "</div>";
                                            if (isset($listItem['subtitle'])) {
                                                $output .= "<div class='text-sm text-green-600'>" . htmlspecialchars($listItem['subtitle']) . "</div>";
                                            }
                                            $output .= "</div>";
                                            $output .= "</li>";
                                        }
                                    }
                                    $output .= "</ul>";
                                }
                                $output .= "</div>";
                            }
                            break;
                            
                        case 'accordion':
                            if (isset($item['title'])) {
                                $output .= "<div class='border border-orange-300 bg-orange-50 p-4 rounded-lg mt-3 mb-2'>";
                                $output .= "<div class='font-bold text-orange-800 text-base mb-2'>" . htmlspecialchars($item['title']) . "</div>";
                                
                                if (isset($item['text'])) {
                                    $output .= "<div class='text-sm text-orange-700'>" . htmlspecialchars($item['text']) . "</div>";
                                }
                                $output .= "</div>";
                            }
                            break;
                            
                        default:
                            // Handle other types or unknown types
                            $output .= "<div class='border border-gray-300 bg-gray-50 p-2 rounded text-xs text-gray-600 mt-2 mb-2'>";
                            $output .= "📋 Tipe konten: " . htmlspecialchars($item['type']);
                            $output .= "</div>";
                            break;
                    }
                }
            }
        }
    }
    
    return $output ?: "<p class='text-gray-500 italic'>Tidak ada konten yang dapat ditampilkan.</p>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Respons Chatbot</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
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
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
        <h2 class="text-2xl font-bold text-gray-700">📋 Daftar Respons Chatbot</h2>
        <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
            <!-- Search Form -->
            <form method="GET" class="flex gap-2">
                <input type="hidden" name="page" value="1">
                <div class="relative">
                    <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                           placeholder="Cari keywords atau respons..." 
                           class="px-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent w-64">
                    <button type="submit" class="absolute right-2 top-1/2 transform -translate-y-1/2 text-gray-500 hover:text-blue-600">
                        🔍
                    </button>
                </div>
                <?php if (!empty($search)): ?>
                    <a href="?" class="px-3 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors duration-200 text-sm">
                        ✕ Clear
                    </a>
                <?php endif; ?>
            </form>
            <a href="tambah.php" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md shadow text-sm transition-colors duration-200 whitespace-nowrap">+ Tambah Respons</a>
        </div>
    </div>

    <?php if (!empty($search)): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded mb-4 text-sm">
            🔍 Menampilkan hasil pencarian untuk: "<strong><?= htmlspecialchars($search) ?></strong>" 
            (<?= $total_records ?> hasil ditemukan)
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded mb-4 text-sm">
            ✅ Data berhasil disimpan!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-4 text-sm">
            🗑️ Data berhasil dihapus!
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['updated'])): ?>
        <div class="bg-blue-100 border border-blue-300 text-blue-800 px-4 py-3 rounded mb-4 text-sm">
            ✏️ Data berhasil diperbarui!
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded mb-4 text-sm">
            ❌ <?= htmlspecialchars($error_message) ?>
        </div>
    <?php endif; ?>

    <?php if (empty($data)): ?>
        <div class="text-center py-12">
            <?php if (!empty($search)): ?>
                <p class="text-gray-500 text-lg">Tidak ada hasil yang ditemukan untuk "<?= htmlspecialchars($search) ?>"</p>
                <p class="text-gray-400 text-sm mt-2">Coba gunakan kata kunci yang berbeda atau <a href="?" class="text-blue-600 underline">lihat semua data</a>.</p>
            <?php else: ?>
                <p class="text-gray-500 text-lg">Belum ada data respons.</p>
                <p class="text-gray-400 text-sm mt-2">Silakan tambah respons baru untuk chatbot Anda.</p>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($data as $item): ?>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50 shadow-sm hover:shadow-md transition-shadow duration-200">
                    <div class="flex justify-between items-center cursor-pointer" onclick="toggleDropdown(<?= (int)$item['id'] ?>)">
                        <h3 class="text-lg font-semibold text-blue-700"><?= htmlspecialchars($item['keywords']) ?></h3>
                        <div class="flex items-center gap-3">
                            <span class="text-gray-500 text-sm dropdown-arrow" id="arrow-<?= (int)$item['id'] ?>">▼ Lihat Detail</span>
                            <div class="flex gap-3 text-sm" onclick="event.stopPropagation()">
                                <a href="edit.php?id=<?= (int)$item['id'] ?>" class="text-blue-500 hover:text-blue-700 hover:underline transition-colors duration-200">✏️ Edit</a>
                                <a href="#"class="text-red-500 hover:text-red-700 hover:underline transition-colors duration-200"data-id="<?= (int)$item['id'] ?>" onclick="openDeleteModal(this)">🗑️ Hapus</a>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 space-y-3 text-sm dropdown-content" id="content-<?= (int)$item['id'] ?>" style="display: none;">
                        <?php
                        $responses = json_decode($item['response_json'], true);
                        
                        // Check if JSON decode was successful
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            echo "<p class='text-red-500'>❌ Error parsing response data: " . json_last_error_msg() . "</p>";
                        } else {
                            echo renderResponseContent($responses);
                        }
                        ?>
                    </div>
                    
                    <div class="justify-between items-center mt-4 pt-3 border-t border-gray-200 dropdown-footer" id="footer-<?= (int)$item['id'] ?>" style="display: none;">
                        <div class="flex justify-between items-center">
                            <div class="text-xs text-gray-500">
                                ID: <?= (int)$item['id'] ?>
                            </div>
                            <div class="text-xs text-gray-500">
                                🕒 <?= htmlspecialchars($item['created_at']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="mt-8 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-sm text-gray-600">
                    Menampilkan <?= min($offset + 1, $total_records) ?> - <?= min($offset + $records_per_page, $total_records) ?> dari <?= $total_records ?> data
                </div>
                
                <div class="flex items-center gap-2">
                    <!-- Previous Button -->
                    <?php if ($page > 1): ?>
                        <a href="?page=<?= $page - 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 text-sm">
                            ‹ Previous
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-400 text-sm cursor-not-allowed">
                            ‹ Previous
                        </span>
                    <?php endif; ?>
                    
                    <!-- Page Numbers -->
                    <?php
                    $start_page = max(1, $page - 2);
                    $end_page = min($total_pages, $page + 2);
                    
                    if ($start_page > 1): ?>
                        <a href="?page=1<?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 text-sm">1</a>
                        <?php if ($start_page > 2): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                    <?php endif; ?>
                    
                    <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <?php if ($i == $page): ?>
                            <span class="px-3 py-2 bg-blue-600 text-white rounded-md text-sm font-medium"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                               class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 text-sm"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                    
                    <?php if ($end_page < $total_pages): ?>
                        <?php if ($end_page < $total_pages - 1): ?>
                            <span class="px-2 text-gray-500">...</span>
                        <?php endif; ?>
                        <a href="?page=<?= $total_pages ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 text-sm"><?= $total_pages ?></a>
                    <?php endif; ?>
                    
                    <!-- Next Button -->
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?= $page + 1 ?><?= !empty($search) ? '&search=' . urlencode($search) : '' ?>" 
                           class="px-3 py-2 bg-white border border-gray-300 rounded-md hover:bg-gray-50 transition-colors duration-200 text-sm">
                            Next ›
                        </a>
                    <?php else: ?>
                        <span class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md text-gray-400 text-sm cursor-not-allowed">
                            Next ›
                        </span>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="mt-6 text-center text-gray-500 text-sm">
            <?php if (!empty($search)): ?>
                Hasil pencarian: <?= count($data) ?> dari <?= $total_records ?> total data
            <?php else: ?>
                Total: <?= $total_records ?> respons (Halaman <?= $page ?> dari <?= $total_pages ?>)
            <?php endif; ?>
        </div>
    <?php endif; ?>
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


<script>
// Dropdown functionality
function toggleDropdown(id) {
    const content = document.getElementById('content-' + id);
    const footer = document.getElementById('footer-' + id);
    const arrow = document.getElementById('arrow-' + id);
    
    if (content.style.display === 'none') {
        content.style.display = 'block';
        footer.style.display = 'flex';
        arrow.innerHTML = '▲ Sembunyikan';
        arrow.classList.add('text-blue-600');
    } else {
        content.style.display = 'none';
        footer.style.display = 'none';
        arrow.innerHTML = '▼ Lihat Detail';
        arrow.classList.remove('text-blue-600');
    }
}

// Simple JavaScript for enhanced UX
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide success messages after 5 seconds
    const alerts = document.querySelectorAll('.bg-green-100, .bg-red-100, .bg-blue-100');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
    
    // Smooth scroll for better UX
    document.documentElement.style.scrollBehavior = 'smooth';
    
    // Add hover effect for dropdown items
    const dropdownHeaders = document.querySelectorAll('[onclick*="toggleDropdown"]');
    dropdownHeaders.forEach(header => {
        header.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#f8fafc';
        });
        header.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
    
    // Auto-submit search form on input (debounced)
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        let searchTimeout;
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                if (this.value.length >= 3 || this.value.length === 0) {
                    this.form.submit();
                }
            }, 500);
        });
    }
    
    // Highlight search terms
    const searchTerm = '<?= addslashes($search) ?>';
    if (searchTerm) {
        highlightSearchTerms(searchTerm);
    }
});

// Function to highlight search terms
function highlightSearchTerms(term) {
    if (!term) return;
    
    const regex = new RegExp(`(${term})`, 'gi');
    const elements = document.querySelectorAll('.text-blue-700, .text-gray-700');
    
    elements.forEach(element => {
        if (element.innerHTML && !element.querySelector('mark')) {
            element.innerHTML = element.innerHTML.replace(regex, '<mark class="bg-yellow-200 px-1 rounded">$1</mark>');
        }
    });
}

//HapusBUTtON
function openDeleteModal(button) {
    const id = button.getAttribute('data-id');
    const deleteLink = document.getElementById('confirmDeleteLink');
    deleteLink.href = 'hapus.php?id=' + id;

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