<?php
session_start();
include('includes/db.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];  // Diganti dari email ke username
    $password = $_POST['password'];

    // Lindungi dari SQL Injection
    $username = $conn->real_escape_string($username);
    $sql = "SELECT id, username, password, role FROM users WHERE username = '$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        // Verifikasi password dengan hash
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['role'] = $row['role'];

            if ($row['role'] == 'pinkoncab') {
                header("Location: admin/dashboard_pinkoncab.php");
                exit();
            } elseif ($row['role'] == 'pinkonran') {
                header("Location: admin/dashboard_pinkonran.php");
                exit();
            }
        } else {
            $error_message = "Password salah.";
        }
    } else {
        $error_message = "Username tidak ditemukan.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Raimuna Jawa Barat XIV</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="font-sans bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="login-container bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <h1 class="text-3xl font-bold text-center text-gray-800 mb-6">Login Raimuna Jawa Barat XIV</h1>
        <?php if (!empty($error_message)): ?>
            <p class="text-red-600 text-center mb-4"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <form action="index.php" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username:</label>
                <input type="text" id="username" name="username" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
            </div>
            <div>
                <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password:</label>
                <input type="password" id="password" name="password" required 
                       class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 focus:outline-none focus:shadow-outline">
            </div>
            <button type="submit" 
                    class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded w-full focus:outline-none focus:shadow-outline">
                Login
            </button>
        </form>
    </div>
</body>
</html>
