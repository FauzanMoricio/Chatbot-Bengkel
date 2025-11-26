<?php
// Cek apakah user sudah login
session_start();

if (!isset($_SESSION['log']) || $_SESSION['log'] !== 'login') {
    header("Location: login.php");
    exit();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Upload Gambar Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex items-center justify-center min-h-screen">

<!-- Sidebar Admin -->
<div class="flex h-screen">
    <?php include 'sidebar.php' ?>

    <div class="flex-1 p-4 mt-20 md:p-8 md:mt-0">
        <div class="mb-6">
            <div class="flex justify-center">
                <h1 class="text-xl font-bold text-gray-800 md:text-2xl text-center">Buat Link Gambar</h1>
            </div>
        </div>
<!-- Link Gambar -->
  <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg w-full">
    <h2 class="text-2xl font-semibold text-center text-gray-700 mb-6">Upload Gambar ke Imgur</h2>

    <form id="uploadForm" enctype="multipart/form-data">
      <input type="file" name="image" accept="image/*" required
        class="w-full p-3 mb-4 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-400 focus:border-blue-500">

      <button type="submit" class="w-full p-3 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-400">
        Dapatkan Link
      </button>
    </form>

    <div id="result" class="mt-6 text-center"></div>
  </div>

  <script>
    const form = document.getElementById('uploadForm');
    const result = document.getElementById('result');

    form.addEventListener('submit', function(e) {
      e.preventDefault();
      const formData = new FormData(form);

      fetch('upload.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          result.innerHTML = `
            <p class="text-green-500 font-medium">Gambar berhasil diupload!</p>
            <img src="${data.link}" class="my-4 max-w-full h-auto rounded-md" />
            <p><strong>Link:</strong> 
              <a href="${data.link}" target="_blank" class="text-blue-500 hover:text-blue-700" id="imageLink">${data.link}</a>
              <button id="copyButton" class="ml-4 mt-2 text-white text-base bg-blue-500 rounded-md hover:bg-blue-600 p-2">Salin</button>
            </p>
            <p class="mt-2 text-gray-600">Gunakan link ini untuk <code>rawUrl</code> di Dialogflow.</p>
          `;

          // Menambahkan event listener untuk tombol salin
          const copyButton = document.getElementById('copyButton');
          const imageLink = document.getElementById('imageLink');

          copyButton.addEventListener('click', function() {
            // Salin link ke clipboard
            navigator.clipboard.writeText(imageLink.href)
              .then(() => {
                alert('Link berhasil disalin!');
              })
              .catch(err => {
                alert('Gagal menyalin link: ' + err);
              });
          });

        } else {
          result.innerHTML = `<p class="text-red-500 font-medium">Gagal upload: ${data.message}</p>`;
        }
      })
      .catch(err => {
        result.innerHTML = `<p class="text-red-500 font-medium">Terjadi kesalahan: gambar harus PNG, JPG, JPEG</p>`;
      });
    });
  </script>
  <!-- Link Gambar -->

<!-- Custom Payload -->
 

</body>
</html>
