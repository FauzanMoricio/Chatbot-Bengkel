<?php
// Matikan semua error output yang bisa merusak JSON
error_reporting(0);
ini_set('display_errors', 0);



$responseData = cariResponsDatabase($db, $text, 3); // Naikkan minScore ke 3

// Set header JSON
header('Content-Type: application/json');

try {
    require_once 'config/Database.php';
    
    $db = (new Database())->connect();
    $input = json_decode(file_get_contents('php://input'), true);
    
    // Validasi input JSON
    if (!$input) {
        throw new Exception("Invalid JSON input");
    }
} catch (Exception $e) {
    error_log("Webhook initialization error: " . $e->getMessage());
    echo json_encode(['fulfillmentText' => 'Terjadi kesalahan sistem. Silakan coba lagi.']);
    exit;
}

// Log untuk debugging (hapus di production)
error_log("Webhook Input: " . json_encode($input));

//ambil data dari dilogflow
$intent = $input['queryResult']['intent']['displayName'] ?? '';
$text = strtolower($input['queryResult']['queryText'] ?? '');
$params = $input['queryResult']['parameters'] ?? [];

// Log parameters untuk debugging
error_log("Parameters: " . json_encode($params));

// Tambahkan di awal webhook setelah extract intent
$confidence = $input['queryResult']['intentDetectionConfidence'] ?? 0;

// Hanya tolak jika confidence SANGAT rendah DAN benar-benar fallback
if ($confidence < 0.2 && ($intent === 'Default Fallback Intent' || empty($intent))) {
    echo json_encode([
        'fulfillmentText' => "Bisa dijelaskan lebih spesifik?"
    ]);
    exit;
}

// Fungsi bantu: ambil string dari parameter yang bisa berupa array, string, atau object
function paramToString($param) {
    try {
        if (empty($param)) return '';
        
        error_log("Processing param: " . json_encode($param));
        
        // Jika string biasa, return langsung
        if (is_string($param)) {
            return trim($param);
        }
        
        // Jika array numerik, ambil elemen pertama
        if (is_array($param) && isset($param[0])) {
            return trim((string)$param[0]);
        }
        
        // Jika object/associative array
        if (is_array($param)) {
            // Untuk nama: cari key 'name'
            if (isset($param['name'])) {
                return trim((string)$param['name']);
            }
            
            // Untuk tanggal: cari key 'date_time'  
            if (isset($param['date_time'])) {
                return trim((string)$param['date_time']);
            }
            
            // Coba ambil nilai pertama yang tidak kosong
            foreach ($param as $key => $value) {
                if (!empty($value) && is_string($value)) {
                    return trim($value);
                }
            }
            
            // Jika masih kosong, ambil nilai pertama
            $firstValue = reset($param);
            if ($firstValue !== false) {
                return trim((string)$firstValue);
            }
        }
        
        return trim((string)$param);
    } catch (Exception $e) {
        error_log("Error in paramToString: " . $e->getMessage());
        return '';
    }
}

// Fungsi bantu: konversi ke format tanggal yang valid
function parseTanggal($rawTanggal) {
    try {
        if (empty($rawTanggal)) return null;
        
        error_log("Raw tanggal input: " . $rawTanggal);

        // Jika sudah format ISO dari Dialogflow dengan timezone (2025-06-18T14:00:00+07:00)
        if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+\-]\d{2}:\d{2}/', $rawTanggal)) {
            try {
                $date = new DateTime($rawTanggal);
                // Konversi ke waktu lokal tanpa timezone
                $result = $date->format('Y-m-d H:i:s');
                error_log("Parsed ISO with timezone: " . $result);
                return $result;
            } catch (Exception $e) {
                error_log("Error parsing ISO with timezone: " . $e->getMessage());
            }
        }

        // Jika sudah format ISO dari Dialogflow tanpa timezone (2025-06-18T14:00:00)
        if (preg_match('/\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}/', $rawTanggal)) {
            try {
                $date = new DateTime($rawTanggal);
                $result = $date->format('Y-m-d H:i:s');
                error_log("Parsed ISO without timezone: " . $result);
                return $result;
            } catch (Exception $e) {
                error_log("Error parsing ISO date: " . $e->getMessage());
            }
        }

        // Jika format ISO tanpa waktu (hanya tanggal)
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $rawTanggal)) {
            try {
                // Set default jam 09:00 jika tidak ada waktu
                $date = new DateTime($rawTanggal . ' 09:00:00');
                $result = $date->format('Y-m-d H:i:s');
                error_log("Parsed date only: " . $result);
                return $result;
            } catch (Exception $e) {
                error_log("Error parsing date only: " . $e->getMessage());
            }
        }

        // Parsing bahasa Indonesia yang lebih baik
        $tanggalIndo = strtolower($rawTanggal);
        
        // Array bulan Indonesia
        $bulanIndo = [
            'januari' => 'January', 'jan' => 'January',
            'februari' => 'February', 'feb' => 'February', 
            'maret' => 'March', 'mar' => 'March',
            'april' => 'April', 'apr' => 'April',
            'mei' => 'May',
            'juni' => 'June', 'jun' => 'June',
            'juli' => 'July', 'jul' => 'July',
            'agustus' => 'August', 'agu' => 'August', 'ags' => 'August',
            'september' => 'September', 'sep' => 'September', 'sept' => 'September',
            'oktober' => 'October', 'okt' => 'October', 'oct' => 'October',
            'november' => 'November', 'nov' => 'November',
            'desember' => 'December', 'des' => 'December', 'dec' => 'December'
        ];

        // Konversi bulan Indonesia ke Inggris
        foreach ($bulanIndo as $indo => $english) {
            $tanggalIndo = str_replace($indo, $english, $tanggalIndo);
        }

        // Konversi kata waktu
        $waktuKonversi = [
            'pagi' => '09:00',
            'siang' => '12:00', 
            'sore' => '15:00',
            'malam' => '19:00',
            'jam' => ''
        ];

        foreach ($waktuKonversi as $indo => $english) {
            $tanggalIndo = str_replace($indo, $english, $tanggalIndo);
        }

        // Bersihkan string dan tambahkan tahun jika tidak ada
        $tanggalIndo = trim(preg_replace('/\s+/', ' ', $tanggalIndo));
        
        // Jika tidak ada tahun, tambahkan tahun sekarang
        if (!preg_match('/\d{4}/', $tanggalIndo)) {
            $tanggalIndo .= ' ' . date('Y');
        }

        error_log("Processed tanggal: " . $tanggalIndo);

        // Coba parse dengan strtotime
        $timestamp = strtotime($tanggalIndo);
        if ($timestamp !== false) {
            $result = date('Y-m-d H:i:s', $timestamp);
            error_log("Parsed with strtotime: " . $result);
            return $result;
        }

        // Jika masih gagal, coba parsing manual untuk format "20 June 14:00"
        if (preg_match('/(\d{1,2})\s+(january|february|march|april|may|june|july|august|september|october|november|december)\s+(\d{1,2}):(\d{2})/i', $tanggalIndo, $matches)) {
            $day = $matches[1];
            $month = $matches[2];
            $hour = $matches[3];
            $minute = $matches[4];
            $year = date('Y');
            
            $dateString = "$year-" . date('m', strtotime($month)) . "-" . sprintf('%02d', $day) . " $hour:$minute:00";
            error_log("Manual parsed: " . $dateString);
            return $dateString;
        }

        error_log("Failed to parse tanggal: " . $rawTanggal);
        return null;
    } catch (Exception $e) {
        error_log("Error in parseTanggal: " . $e->getMessage());
        return null;
    }
}

// Fungsi untuk validasi ketersediaan tanggal
function cekKetersediaanTanggal($db, $tanggal) {
    try {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE 
                DATE(tanggal) = DATE(?) AND 
                HOUR(tanggal) = HOUR(?) AND 
                status != 'Dibatalkan'
        ");
        $stmt->execute([$tanggal, $tanggal]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        // Maksimal 2 booking per jam pada tanggal yang sama
        return $result['count'] < 2;
    } catch (Exception $e) {
        error_log("Error in cekKetersediaanTanggal: " . $e->getMessage());
        return true;
    }
}


// Fungsi untuk mencari respons dari database (Multi-chat version)
function cariResponsDatabase($db, $text, $minScore = 2) {
    try {
        $stopwords = ['periksa','di','cek','yang','ke','dan','untuk','cara','bagaimana','saya','kamu','anda','adalah','ini','itu','dengan','dari','pada','dalam','akan','sudah','telah','juga','atau','tapi','namun','karena','jika','bila','ketika','saat','waktu','bisa','dapat','harus','perlu','ingin','mau','minta','tolong','silakan','tidak','motor','servis','bengkel'];
        
        // Deteksi apakah query tentang komponen motor
        $komponenMotor = [
            // Komponen asli
            'aki', 'busi', 'ban', 'oli mesin', 'vaan belt', 'rem', 'oli gardan', 'filter udara',
            
            // Tambahan layanan servis
            'servis besar', 'servis ringan', 'servis kelistrikan','servis listrik', 'besar','listrik',  'ringan', 'kelistrikan','servis', 'service'
        ];
        $hasKomponen = false;
        
        foreach ($komponenMotor as $komponen) {
            if (strpos($text, $komponen) !== false) {
                $hasKomponen = true;
                break;
            }
        }
        
        // Filter words seperti biasa
        $words = preg_split('/\s+/', strtolower(trim($text)));
        $words = array_filter($words, function($word) use ($stopwords) {
            return $word && 
                   strlen($word) >= 3 && 
                   !in_array($word, $stopwords) &&
                   !is_numeric($word);
        });
        $words = array_unique($words);
        
        if (count($words) < 1) {
            return null;
        }

        $stmt = $db->prepare("SELECT id, keywords, response_json FROM responses ORDER BY id ASC");
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $foundResponses = [];
        $usedResponseIds = [];

        foreach ($words as $inputWord) {
            $bestScore = 0;
            $bestResponse = null;
            $bestResponseId = null;

            foreach ($rows as $row) {
                if (in_array($row['id'], $usedResponseIds)) {
                    continue;
                }

                $keywords = array_map('trim', explode(',', strtolower($row['keywords'])));
                
                // FILTER: Skip respons lokasi jika ada komponen dalam query
                if ($hasKomponen) {
                    $isLokasiResponse = false;
                    foreach ($keywords as $keyword) {
                        if (in_array($keyword, ['lokasi', 'alamat', 'tempat', 'dimana'])) {
                            $isLokasiResponse = true;
                            break;
                        }
                    }
                    
                    if ($isLokasiResponse) {
                        continue; // Skip respons lokasi
                    }
                }
                
                $score = 0;
                
                foreach ($keywords as $keyword) {
                    if ($inputWord === $keyword) {
                        $score += 3;
                    } elseif (strlen($keyword) >= 4 && strpos($keyword, $inputWord) !== false) {
                        $score += 2;
                    } elseif (strlen($inputWord) >= 4 && strpos($inputWord, $keyword) !== false) {
                        $score += 1;
                    }
                }

                if ($score > $bestScore) {
                    $bestScore = $score;
                    $bestResponse = $row['response_json'];
                    $bestResponseId = $row['id'];
                }
            }

            if ($bestScore >= $minScore && $bestResponse) {
                $responseData = json_decode($bestResponse, true);
                
                // Image conversion...
                foreach ($responseData as &$item) {
                    if (isset($item['image']['imageUri'])) {
                        $imageUrl = $item['image']['imageUri'];
                        $item = [
                            "payload" => [
                                "richContent" => [
                                    [
                                        [
                                            "type" => "image",
                                            "rawUrl" => $imageUrl,
                                            "accessibilityText" => "Gambar dari database"
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    }
                }

                $foundResponses = array_merge($foundResponses, $responseData);
                $usedResponseIds[] = $bestResponseId;
            }
        }

        return !empty($foundResponses) ? $foundResponses : null;

    } catch (Exception $e) {
        error_log("Error in cariResponsDatabase: " . $e->getMessage());
        return null;
    }
}


function isValidIndonesianPhoneNumber($no_hp) {
    $no_hp = preg_replace('/[\s\-]/', '', $no_hp); // Hilangkan spasi dan strip
    return preg_match('/^(?:\+62|62|0)8[1-9][0-9]{7,11}$/', $no_hp);
}


// === 1. Booking Servis ===
if ($intent === 'Booking Servis') {
    // Ambil parameter dengan penanganan object yang lebih baik
    $nama = paramToString($params['nama'] ?? '');
    $no_hp = paramToString($params['no_hp'] ?? '');
    $jenis_motor = paramToString($params['jenis_motor'] ?? '');

    //layanan agar bisa lebih dari 1
    $layananParam = $params['layanan'] ?? [];
    $layanan = '';

    if (is_array($layananParam)) {
        // Jika berupa array (multiple selection dari Dialogflow)
        $layananArray = [];
        foreach ($layananParam as $service) {
            $cleanService = trim(paramToString($service));
            if (!empty($cleanService)) {
                $layananArray[] = $cleanService;
            }
        }
        $layanan = implode(', ', $layananArray);
    } else {
        // Jika berupa string, cek apakah ada multiple layanan
        $serviceString = trim(paramToString($layananParam));
        
        // PERBAIKAN: Pattern yang lebih fleksibel untuk mendeteksi multiple
        if (preg_match('/\s+(dan|sama|,|\+|&)\s+/i', $serviceString)) {
            // Split berdasarkan pattern yang diperbaiki
            $services = preg_split('/\s+(dan|sama|,|\+|&)\s+/i', $serviceString);
            $layananArray = [];
            foreach ($services as $service) {
                $cleanService = trim($service);
                
                // TAMBAHAN: Bersihkan kata-kata yang tidak perlu
                $cleanService = preg_replace('/^(mau|periksa|cek)\s+/i', '', $cleanService);
                $cleanService = preg_replace('/\s+(juga|dong|ya)$/i', '', $cleanService);
                
                if (!empty($cleanService)) {
                    $layananArray[] = $cleanService;
                }
            }
            $layanan = implode(', ', $layananArray);
        } else {
            // TAMBAHAN: Bersihkan juga untuk single service
            $serviceString = preg_replace('/^(mau|periksa|cek)\s+/i', '', $serviceString);
            $serviceString = preg_replace('/\s+(juga|dong|ya)$/i', '', $serviceString);
            $layanan = $serviceString;
        }
    }

    // TAMBAHAN: Log untuk debugging
    error_log("Raw layanan param: " . json_encode($layananParam));
    error_log("Final layanan: " . $layanan);

    $tanggal_raw = paramToString($params['tanggal'] ?? '');
    
    // Log untuk debugging
    error_log("Raw Parameters: " . json_encode($params));
    error_log("Extracted - Nama: '$nama', HP: '$no_hp', Motor: '$jenis_motor', Layanan: '$layanan', Tanggal: '$tanggal_raw'");

    $tanggal = parseTanggal($tanggal_raw);
    
    error_log("Parsed tanggal result: " . ($tanggal ?? 'NULL'));

    // Cek data yang tersedia dan berikan respons bertahap
    $missingData = [];
    if (empty($nama)) $missingData[] = 'nama';
    if (empty($no_hp)) $missingData[] = 'nomor HP';
    if (empty($tanggal)) $missingData[] = 'tanggal';

    // Jika data penting masih kurang, minta dengan friendly
    if (!empty($missingData)) {
        $response = "Baik, saya akan bantu booking servis Anda! 😊\n\n";
        
        // Tampilkan data yang sudah ada
        if (!empty($jenis_motor) || !empty($layanan)) {
            $response .= "📋 **Data yang sudah saya catat:**\n";
            if (!empty($jenis_motor)) $response .= "🏍️ Motor: $jenis_motor\n";
            if (!empty($layanan)) $response .= "🔧 Layanan: $layanan\n";
            $response .= "\n";
        }
        
        // Minta data yang kurang satu per satu
        if (empty($nama)) {
            $response .= "Boleh saya tahu nama Anda?";
        } elseif (empty($no_hp)) {
            $response .= "Terima kasih $nama! Sekarang saya butuh nomor HP Anda untuk konfirmasi booking.";
        } elseif (empty($tanggal)) {
            $response .= "Terima kasih $nama! Kapan Anda ingin booking servisnya? (contoh: besok pagi, 20 Juni jam 10)";
        }
        
        echo json_encode(['fulfillmentText' => $response]);
        exit;
    }

    if (!isValidIndonesianPhoneNumber($no_hp)) {
    echo json_encode([
        'fulfillmentText' => "❗ Nomor HP tidak valid. Pastikan formatnya seperti:\n- 081234567890\n- +6281234567890 (Silahkan Ulangi Proses Bookingnya)"
    ]);
    exit;
    }

    // Set default values jika kosong
    if (empty($jenis_motor)) $jenis_motor = 'Motor (akan dikonfirmasi)';
    if (empty($layanan)) $layanan = 'Servis Ringan';

    // Cek apakah tanggal di masa lalu
    if (strtotime($tanggal) < time()) {
        echo json_encode([
            'fulfillmentText' => "❗ Tanggal yang Anda pilih sudah berlalu. Silakan pilih tanggal yang akan datang dan ulangi proses bookingnya."
        ]);
        exit;
    }

    // Cek ketersediaan tanggal
    if (!cekKetersediaanTanggal($db, $tanggal)) {
        $tanggal_user = (new DateTime($tanggal))->format('d M Y H:i');
        echo json_encode([
            'fulfillmentText' => "❗ Maaf, jadwal pada $tanggal_user sudah penuh. Silakan pilih waktu lain yang masih tersedia dan ulangi proses bookingnya."
        ]);
        exit;
    }


    try {
        // Simpan ke database
        $stmt = $db->prepare("INSERT INTO bookings (nama, no_hp, jenis_motor, layanan, tanggal, status, created_at) VALUES (?, ?, ?, ?, ?, 'Menunggu', NOW())");
        $result = $stmt->execute([$nama, $no_hp, $jenis_motor, $layanan, $tanggal]);

        if ($result) {
            $bookingId = $db->lastInsertId();
            
            // Format tanggal untuk ditampilkan
            $tanggal_user = (new DateTime($tanggal))->format('d M Y, H:i');

            $responseText = "✅ **Booking berhasil disimpan!**\n\n".
                            "📋 **Detail Booking:**\n".
                            "🆔 ID: #$bookingId\n".
                            "👤 Nama: $nama\n".
                            "📱 No HP: $no_hp\n".
                            "🏍️ Motor: $jenis_motor\n".
                            "🔧 Layanan: $layanan\n".
                            "📅 Tanggal: $tanggal_user\n".
                            "⏳ Status: Menunggu Konfirmasi dari bengkel\n\n".
                            "🙏 Terima kasih! Silahkan Refresh setelah melakukan booking, Jika ingin membatalkan (ketik 'Batalkan Booking').";

            echo json_encode(['fulfillmentText' => $responseText]);
        } else {
            echo json_encode([
                'fulfillmentText' => "❗ Terjadi kesalahan saat menyimpan booking. Silakan coba lagi."
            ]);
        }
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode([
            'fulfillmentText' => "❗ Terjadi kesalahan sistem. Silakan coba lagi atau Periksa Internet Anda."
        ]);
    }
    exit;
}

// === 2. Cek Status Booking ===
if ($intent === 'Cek Status Booking') {
    $bookingId = paramToString($params['booking_id'] ?? $params['number'] ?? '');
    $noHp = paramToString($params['phone'] ?? $params['nomor'] ?? '');

    if ($bookingId) {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
    } elseif ($noHp) {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE no_hp LIKE ? ORDER BY created_at DESC LIMIT 1");
        $stmt->execute(["%$noHp%"]);
    } else {
        echo json_encode([
            'fulfillmentText' => "Silakan berikan nomor HP untuk mengecek status."
        ]);
        exit;
    }

    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($booking) {
        $tanggal_user = (new DateTime($booking['tanggal']))->format('d M Y, H:i');
        $response = "📋 *Status Booking #" . $booking['id'] . "*\n\n";
        $response .= "👤 Nama: " . $booking['nama'] . "\n";
        $response .= "🏍️ Motor: " . $booking['jenis_motor'] . "\n";
        $response .= "🔧 Layanan: " . $booking['layanan'] . "\n";
        $response .= "📅 Tanggal: $tanggal_user\n";
        $response .= "⏳ Status: " . $booking['status'];

        echo json_encode(['fulfillmentText' => $response]);
    } else {
        echo json_encode([
            'fulfillmentText' => "❗ Booking tidak ditemukan. Periksa nomor HP Anda."
        ]);
    }
    exit;
}


// === 3. Batalkan Booking Berdasarkan Nomor HP ===//
if ($intent === 'batalkan_booking') {
    $no_hp = paramToString($params['no_hp'] ?? '');

    // Normalisasi nomor HP (hapus spasi, ubah +62 ke 0)
    $no_hp = preg_replace('/\s+/', '', $no_hp);        // hilangkan spasi
    $no_hp = preg_replace('/^\+62/', '0', $no_hp);     // ubah +62 ke 0
    $no_hp = preg_replace('/^62/', '0', $no_hp);       // ubah 62 ke 0

    if (empty($no_hp)) {
        echo json_encode([
            'fulfillmentText' => "Untuk membatalkan booking, saya butuh nomor HP Anda. Silakan sebutkan nomor HP yang digunakan saat booking."
        ]);
        exit;
    }

    // Pastikan class Booking sudah di-include
    require_once 'classes/Booking.php'; 
    $bookingObj = new Booking($db);

    // Cari booking aktif berdasarkan no_hp
    $booking = $bookingObj->cariBookingAktifBerdasarkanNoHp($no_hp);

    if ($booking) {
        $bookingObj->batalkanBooking($booking['id']);

        $tanggal_user = (new DateTime($booking['tanggal']))->format('d M Y, H:i');
        $response = "❌ Booking dengan nomor *{$booking['no_hp']}* untuk tanggal *$tanggal_user* telah dibatalkan. Jika ini tidak sengaja, Anda bisa booking ulang kapan saja ya!";
    } else {
        $response = "Saya tidak menemukan booking aktif dengan nomor *$no_hp*. Mungkin sudah dibatalkan atau diselesaikan sebelumnya.";
    }

    echo json_encode(['fulfillmentText' => $response]);
    exit;
}


// === 4. Cari Respons dari Database ===
$responseData = cariResponsDatabase($db, $text, 2);

if ($responseData) {
    
    // Jika response_json berformat array untuk fulfillmentMessages
    if (is_array($responseData) && !isset($responseData['fulfillmentText'])) {
        echo json_encode([
            'fulfillmentMessages' => $responseData
        ]);
    } else {
        // Jika response_json berformat object dengan fulfillmentText
        echo json_encode($responseData);
    }
    exit;
}


// === 4. Fallback Default ===
$fallbackMessages = [
    "Maaf, saya belum memahami maksud Anda.\n\n Contoh yang bisa saya bantu:\n Bisa dilihat pada MENU atau Silakan coba dengan format yang lebih jelas. 🙏",
    
    "Maaf, informasi yang Anda cari tidak tersedia saat ini.\n\n Saya bisa membantu Anda dengan:\n Membuat booking servis\n, LihatPerawatan Dasar Matic\n, Memberikan informasi umum tentang layanan kami\n,Silakan tanyakan hal lain dapat dilihat pada MENU.🙏",
    
    "Sepertinya saya belum bisa menjawab pertanyaan Anda.\n\n Yang bisa saya bantu:\n Proses booking servis motor\n, Lihat Perawatan Dasar Matic\n, Informasi layanan bengkel\n\nCoba tanyakan dengan cara lain atau lihat MENU untuk informasi lainnya. 🙏",
    
    "Mohon maaf, saya tidak memiliki informasi untuk pertanyaan tersebut.\n\nUntuk bantuan lebih lanjut, Anda dapat:\n Mengetik MENU \n Atau coba tanyakan hal lain yang bisa saya bantu. 🙏"
];

// Pilih salah satu pesan fallback secara random untuk variasi
$randomIndex = array_rand($fallbackMessages);
$fallbackText = $fallbackMessages[$randomIndex];

echo json_encode([
    'fulfillmentText' => $fallbackText
]);

exit;
?>