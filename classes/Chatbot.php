<?php
class Chatbot {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Cari jawaban berdasarkan keyword
    public function cariRespons($userText) {
        $userText = strtolower($userText);

        // ambil semua keyword dan respons dari DB
        $stmt = $this->db->query("SELECT keywords, response_json FROM responses");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $matches = [];

        foreach ($rows as $row) {
            $keywords = explode(',', strtolower($row['keywords']));

            foreach ($keywords as $kw) {
                $kw = trim($kw);
                if ($kw !== "" && strpos($userText, $kw) !== false) {
                    $matches[] = $row['response_json'];
                    break; // biar tidak double match pada baris yang sama
                }
            }
        }

        return !empty($matches) ? $matches : null;
    }
}
