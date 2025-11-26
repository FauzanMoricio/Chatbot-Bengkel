<?php
class Response {
    private $conn;
    private $table = 'responses';

    public function __construct($db) {
        $this->conn = $db;
    }

    public function simpan($keywords, $response_json) {
        try {
            $stmt = $this->conn->prepare("INSERT INTO {$this->table} (keywords, response_json, created_at) VALUES (?, ?, NOW())");
            return $stmt->execute([$keywords, $response_json]);
        } catch (Exception $e) {
            error_log("Error saving response: " . $e->getMessage());
            return false;
        }
    }

    public function getAll() {
        try {
            $stmt = $this->conn->query("SELECT * FROM {$this->table} ORDER BY created_at DESC");
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error getting responses: " . $e->getMessage());
            return [];
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error getting response by ID: " . $e->getMessage());
            return null;
        }
    }

    public function update($id, $keywords, $response_json) {
        try {
            $stmt = $this->conn->prepare("UPDATE {$this->table} SET keywords = ?, response_json = ? WHERE id = ?");
            return $stmt->execute([$keywords, $response_json, $id]);
        } catch (Exception $e) {
            error_log("Error updating response: " . $e->getMessage());
            return false;
        }
    }

    public function delete($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = ?");
            return $stmt->execute([$id]);
        } catch (Exception $e) {
            error_log("Error deleting response: " . $e->getMessage());
            return false;
        }
    }

public function cariByKeyword($text) {
    try {
        $text = strtolower(trim($text));
        $text = preg_replace('/[^a-z0-9\s]/', '', $text);
        error_log("Searching for keywords in text: " . $text);
        
        $stmt = $this->conn->query("SELECT id, keywords, response_json FROM {$this->table} ORDER BY id DESC");
        $responses = $stmt->fetchAll();

        foreach ($responses as $response) {
            $keywords = explode(',', strtolower($response['keywords']));

            foreach ($keywords as $keyword) {
                $keyword = trim($keyword);
                if (empty($keyword)) continue;

                if (preg_match('/\b' . preg_quote($keyword, '/') . '\b/i', $text)) {
                    error_log("Found matching keyword: '$keyword' in response ID: " . $response['id']);

                    $responseData = json_decode($response['response_json'], true);

                    if (json_last_error() === JSON_ERROR_NONE && is_array($responseData)) {
                        // Proses untuk convert imageUri menjadi richContent
                        foreach ($responseData as &$item) {
                            if (isset($item['image']['imageUri'])) {
                                $item = [
                                    'payload' => [
                                        'richContent' => [
                                            [
                                                [
                                                    'type' => 'image',
                                                    'rawUrl' => $item['image']['imageUri'],
                                                    'accessibilityText' => 'Gambar dari database'
                                                ]
                                            ]
                                        ]
                                    ]
                                ];
                            }
                        }

                        return $responseData;
                    } else {
                        error_log("Invalid JSON in response ID: " . $response['id']);
                    }
                }
            }
        }

        error_log("No matching keywords found");
        return null;
    } catch (Exception $e) {
        error_log("Error in cariByKeyword: " . $e->getMessage());
        return null;
    }
}
}
?>
