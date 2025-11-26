<?php
class Booking {
    private $conn;

    // Konstruktor menerima koneksi database
    public function __construct($db) {
        $this->conn = $db;
    }

    // Simpan data booking baru
    public function simpan($nama, $no_hp, $jenis_motor, $layanan, $tanggal) {
        $query = "INSERT INTO bookings (nama, no_hp, jenis_motor, layanan, tanggal, status) 
                  VALUES (:nama, :no_hp, :jenis_motor, :layanan, :tanggal, 'Menunggu')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nama', $nama);
        $stmt->bindParam(':no_hp', $no_hp);
        $stmt->bindParam(':jenis_motor', $jenis_motor);
        $stmt->bindParam(':layanan', $layanan);
        $stmt->bindParam(':tanggal', $tanggal);
        return $stmt->execute();
    }

    // Ambil semua data booking dengan pencarian yang diperbaiki
    public function ambilSemua($sort = 'desc', $search = '') {
        $sort = strtolower($sort) === 'asc' ? 'ASC' : 'DESC';
        $query = "SELECT * FROM bookings";
        $conditions = [];
        $params = [];

        if (!empty($search)) {
            // Pencarian berdasarkan nama
            $conditions[] = "nama LIKE :nama";
            $params[':nama'] = "%$search%";

            // Pencarian berdasarkan nomor HP
            $conditions[] = "no_hp LIKE :no_hp";
            $params[':no_hp'] = "%$search%";

            // Pencarian berdasarkan jenis motor
            $conditions[] = "jenis_motor LIKE :jenis_motor";
            $params[':jenis_motor'] = "%$search%";

            // Pencarian berdasarkan tanggal dengan format fleksibel
            $dateConditions = $this->buildDateConditions($search);
            if (!empty($dateConditions)) {
                $conditions = array_merge($conditions, $dateConditions['conditions']);
                $params = array_merge($params, $dateConditions['params']);
            }
        }

        if (!empty($conditions)) {
            $query .= " WHERE " . implode(" OR ", $conditions);
        }

        $query .= " ORDER BY created_at $sort, tanggal $sort";
        
        $stmt = $this->conn->prepare($query);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Helper function untuk membangun kondisi pencarian tanggal
    private function buildDateConditions($search) {
        $conditions = [];
        $params = [];
        
        // Normalisasi input pencarian
        $search = trim(strtolower($search));
        
        // Array nama bulan dalam bahasa Indonesia
        $months = [
            'jan' => '01', 'januari' => '01',
            'feb' => '02', 'februari' => '02',
            'mar' => '03', 'maret' => '03',
            'apr' => '04', 'april' => '04',
            'mei' => '05', 'may' => '05',
            'jun' => '06', 'juni' => '06',
            'jul' => '07', 'juli' => '07',
            'agu' => '08', 'agustus' => '08',
            'sep' => '09', 'september' => '09',
            'okt' => '10', 'oktober' => '10',
            'nov' => '11', 'november' => '11',
            'des' => '12', 'desember' => '12'
        ];
        
        // Pattern untuk "1 jun", "30 jul", dll
        if (preg_match('/(\d{1,2})\s+(\w+)/', $search, $matches)) {
            $day = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $monthName = strtolower($matches[2]);
            
            if (isset($months[$monthName])) {
                $month = $months[$monthName];
                $currentYear = date('Y');
                
                // Coba tahun ini dan tahun depan
                $conditions[] = "DATE(tanggal) = :date_exact1";
                $conditions[] = "DATE(tanggal) = :date_exact2";
                $params[':date_exact1'] = "$currentYear-$month-$day";
                $params[':date_exact2'] = ($currentYear + 1) . "-$month-$day";
            }
        }
        
        // Pattern untuk "jun", "juli", dll (hanya bulan)
        else if (isset($months[$search])) {
            $month = $months[$search];
            $currentYear = date('Y');
            
            $conditions[] = "MONTH(tanggal) = :month1 AND YEAR(tanggal) = :year1";
            $conditions[] = "MONTH(tanggal) = :month2 AND YEAR(tanggal) = :year2";
            $params[':month1'] = intval($month);
            $params[':year1'] = $currentYear;
            $params[':month2'] = intval($month);
            $params[':year2'] = $currentYear + 1;
        }
        
        // Pattern untuk pencarian tahun "2024", "2025"
        else if (preg_match('/^\d{4}$/', $search)) {
            $conditions[] = "YEAR(tanggal) = :year";
            $params[':year'] = $search;
        }
        
        return ['conditions' => $conditions, 'params' => $params];
    }

    // Update status booking
    public function ubahStatus($id, $status) {
        if ($status === 'Selesai') {
            // Isi selesai_at dengan waktu sekarang
            $stmt = $this->conn->prepare("UPDATE bookings SET status = ?, selesai_at = NOW() WHERE id = ?");
        } else {
            // Jika status bukan selesai, kosongkan selesai_at
            $stmt = $this->conn->prepare("UPDATE bookings SET status = ?, selesai_at = NULL WHERE id = ?");
        }
        return $stmt->execute([$status, $id]);
    }

    // Hapus booking
    public function hapus($id) {
        $stmt = $this->conn->prepare("DELETE FROM bookings WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Cek jumlah booking baru dalam beberapa menit terakhir (default 3 menit)
    public function jumlahBaru($menit = 5) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE created_at >= NOW() - INTERVAL ? MINUTE");
        $stmt->execute([$menit]);
        return $stmt->fetchColumn();
    }

    // Ambil statistik booking untuk dashboard
    public function getStatistik() {
        $stats = [];
        
        // Total booking hari ini
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(created_at) = CURDATE()");
        $stmt->execute();
        $stats['hari_ini'] = $stmt->fetchColumn();
        
        // Booking menunggu
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Menunggu'");
        $stmt->execute();
        $stats['menunggu'] = $stmt->fetchColumn();

        // Booking Silahkan Datang
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Silahkan Datang'");
        $stmt->execute();
        $stats['Silahkan Datang'] = $stmt->fetchColumn();
        
        // Booking diproses
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Diproses'");
        $stmt->execute();
        $stats['diproses'] = $stmt->fetchColumn();

        // Booking dibatalkan
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE status = 'Dibatalkan'");
        $stmt->execute();
        $stats['dibatalkan'] = $stmt->fetchColumn();
        
        // Booking selesai hari ini
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM bookings WHERE DATE(selesai_at) = CURDATE()");
        $stmt->execute();
        $stats['selesai_hari_ini'] = $stmt->fetchColumn();
        
        return $stats;
    }

    // Ambil 1 data booking berdasarkan ID
    public function ambilById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM bookings WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Update data booking lengkap
    public function updateBooking($id, $nama, $no_hp, $jenis_motor, $layanan, $tanggal) {
        $stmt = $this->conn->prepare("UPDATE bookings SET nama = ?, no_hp = ?, jenis_motor = ?, layanan = ?, tanggal = ? WHERE id = ?");
        return $stmt->execute([$nama, $no_hp, $jenis_motor, $layanan, $tanggal, $id]);
    }

    // Ambil daftar layanan yang tersedia
    public function getLayananTersedia() {
        $layanan = [
            'Servis Besar',
            'Servis Listrik',
            'Servis Ringan',
        ];
        return $layanan;
    }
public function ambilJadwalBookingUser($search = '') {
    $query = "SELECT * FROM bookings WHERE status IN ('Menunggu','Silahkan Datang', 'Diproses', 'Selesai')";
    $params = [];

    if (!empty($search)) {
        $query .= " AND (nama LIKE :s OR no_hp LIKE :s OR jenis_motor LIKE :s)";
        $params[':s'] = "%$search%";

        // Tambahkan pencarian berdasarkan tanggal jika cocok format
        $dateConditions = $this->buildDateConditions($search);
        if (!empty($dateConditions['conditions'])) {
            $query .= " OR (" . implode(" OR ", $dateConditions['conditions']) . ")";
            $params = array_merge($params, $dateConditions['params']);
        }
    }

    $query .= " ORDER BY tanggal ASC";
    $stmt = $this->conn->prepare($query);
    foreach ($params as $key => $val) {
        $stmt->bindValue($key, $val);
    }
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Hapus booking yang selesai 7 hari lalu dan dibatalkan lebih dari 14 hari lalu
public function hapusBookingLama() {
    $query = "DELETE FROM bookings 
              WHERE 
                (status = 'Selesai' 
                 AND selesai_at IS NOT NULL 
                 AND selesai_at < DATE_SUB(NOW(), INTERVAL 7 DAY))
              OR
                (status = 'Dibatalkan'
                 AND selesai_at IS NOT NULL
                 AND selesai_at < DATE_SUB(NOW(), INTERVAL 14 DAY))";
    
    $stmt = $this->conn->prepare($query);
    return $stmt->execute();
}

    public function cariBookingAktifBerdasarkanNoHp($no_hp) {
        $query = "SELECT * FROM bookings 
                  WHERE REPLACE(no_hp, ' ', '') = REPLACE(:no_hp, ' ', '') 
                  AND status NOT IN ('Dibatalkan', 'Selesai') 
                  ORDER BY id DESC LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':no_hp', $no_hp);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function batalkanBooking($id) {
        $query = "UPDATE bookings SET status = 'Dibatalkan' WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([':id' => $id]);
    }


}
?>