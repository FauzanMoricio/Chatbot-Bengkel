<?php
session_start();
require_once '../config/Database.php';

try {
    // Buat instance Database dan koneksi
    $db = new Database();
    $conn = $db->connect();
} catch (Exception $e) {
    die("Koneksi ke database gagal: " . $e->getMessage());
}

// Redirect jika sudah login
if (isset($_SESSION['log']) && $_SESSION['log'] === "login") {
    header("Location: index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['login'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Ambil user dari DB
    $stmt = $conn->prepare("SELECT id, username, password FROM login WHERE username = :username");
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

    $user = $stmt->fetch();

    if ($user) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['userid'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['log'] = 'login';

            echo '<script>alert("Anda Berhasil Login Sebagai ' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '"); window.location = "index.php";</script>';
            exit();
        } else {
            $error = "Username atau Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
</head>
<body class="bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-md">
        <div class="bg-white shadow-2xl rounded-2xl overflow-hidden">
            <div class="p-8">
                <div class="flex justify-center">
                    <img src="../../../assets/img/cnslogo.png" alt="" width="180px">
                </div>
                
                <h1 class="text-3xl font-bold text-center text-gray-800 mb-4">Selamat Datang</h1>
                <p class="text-center text-gray-600 mb-6">Silakan masuk keakun admin CNS</p>

                <?php if (isset($error)): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <span class="block sm:inline"><?php echo htmlspecialchars($error); ?></span>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST" class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="user" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <input 
                                type="text"name="username" id="username" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Masukkan username">
                        </div>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password</label>
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <i data-feather="lock" class="w-5 h-5 text-gray-400"></i>
                            </div>
                            <input 
                                type="password" name="password" id="password" required 
                                class="pl-10 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                placeholder="Masukkan password">
                        </div>
                    </div>

                    <div>
                        <button type="submit" name="login" class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-300 ease-in-out transform hover:scale-105">
                            Login
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <div class="text-center mt-4 text-sm text-gray-600">
            &copy; <?php echo date('Y'); ?> CNS. All rights reserved.
        </div>
    </div>

    <script>
        // Initialize Feather Icons
        feather.replace();
    </script>
</body>
</html>