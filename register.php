<?php
include('includes/db.php');

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil dan bersihkan input dari form
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password']; // Password akan di-hash, tidak perlu di-escape
    $role = $conn->real_escape_string($_POST['role']);
    $kontingen = $conn->real_escape_string($_POST['kontingen']); // Data kontingen yang baru
    
    // Hash password untuk keamanan
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Cek apakah username sudah ada
    $sql_check = "SELECT id FROM users WHERE username = '$username'";
    $check_result = $conn->query($sql_check);

    if ($check_result->num_rows > 0) {
        $error_message = "Username sudah digunakan.";
    } else {
        // Tambahkan 'kontingen' ke dalam query INSERT
        $sql_insert = "INSERT INTO users (username, password, role, kontingen, created_at) VALUES ('$username', '$hashed_password', '$role', '$kontingen', NOW())";

        if ($conn->query($sql_insert) === TRUE) {
            $success_message = "Pendaftaran berhasil! Silakan <a href='index.php' class='text-blue-600 underline'>Login</a>.";
        } else {
            $error_message = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register - Raimuna Jawa Barat XIV</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen font-sans">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-sm">
        <h1 class="text-2xl font-bold text-center text-gray-800 mb-6">Register</h1>
        
        <?php if ($error_message): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded text-center mb-4"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if ($success_message): ?>
            <div class="bg-green-100 text-green-600 p-3 rounded text-center mb-4"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <form action="register.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input id="username" name="username" required class="border rounded w-full p-2 text-gray-700 focus:outline-none focus:border-blue-500" />
            </div>
            
            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input id="password" name="password" type="password" required class="border rounded w-full p-2 text-gray-700 focus:outline-none focus:border-blue-500" />
            </div>

            <div>
                <label for="role" class="block text-gray-700 text-sm font-bold mb-2">Role:</label>
                <select id="role" name="role" required class="border rounded w-full p-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="pinkoncab">pinkoncab</option>
                    <option value="pinkonran">pinkonran</option>
                    <option value="saka">saka</option>
                    <option value="admin">admin</option>
                </select>
            </div>

            <div>
                <label for="kontingen" class="block text-gray-700 text-sm font-bold mb-2">Kontingen:</label>
                <select id="kontingen" name="kontingen" required class="border rounded w-full p-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="selatan">Selatan</option>
                    <option value="utara">Utara</option>
                    <option value="tengah">Tengah</option>
                    <option value="cabang">Cabang</option>
                    <option value="saka">Saka</option>
                    <option value="admin">Admin</option>
                </select>
            </div>
            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold rounded p-2 w-full">Register</button>
        </form>
    </div>
</body>
</html>