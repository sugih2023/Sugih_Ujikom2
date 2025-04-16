<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Memeriksa apakah password memiliki panjang minimal 8 karakter
    if (strlen($password) < 8) {
        $error = "Password harus memiliki minimal 8 karakter.";
    } else {
        // Enkripsi password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Periksa apakah username sudah ada
        $sql_check = "SELECT * FROM users WHERE username = '$username'";
        $result = $conn->query($sql_check);

        if ($result->num_rows > 0) {
            $error = "Username sudah digunakan. Silakan pilih username lain.";
        } else {
            // Jika username belum ada, lanjutkan dengan insert
            $sql = "INSERT INTO users (username, password) VALUES ('$username', '$hashed_password')";
            if ($conn->query($sql)) {
                header("Location: login.php?success=Registrasi Berhasil! Silakan Login.");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="styleregisterr.css">
</head>
<body>
<body>
    <!-- GELEMBUNG Latar -->
    <?php for ($i = 0; $i < 25; $i++): ?>
        <div class="bubble" style="
            left: <?= rand(0, 100); ?>vw;
            width: <?= rand(10, 40); ?>px;
            height: <?= rand(10, 40); ?>px;
            animation-duration: <?= rand(10, 30); ?>s;
            animation-delay: <?= rand(0, 20); ?>s;
        "></div>
    <?php endfor; ?>

    <div class="register-container">
        <h2>Register</h2>
        
        <?php if (isset($error)): ?>
            <p class="error-message"><?= $error; ?></p>
        <?php endif; ?>
        
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password (min. 8 karakter)" required minlength="8">
            <button type="submit">Daftar</button>
        </form>
        
        <p>Sudah punya akun? <a href="login.php">Login</a></p>
    </div>
    <script>
document.addEventListener('mousemove', function(e) {
    const trail = document.createElement('div');
    trail.className = 'trail';
    trail.style.left = `${e.pageX}px`;
    trail.style.top = `${e.pageY}px`;
    document.body.appendChild(trail);
    setTimeout(() => trail.remove(), 1000);
});
</script>
<script>
document.addEventListener('mousemove', function(e) {
    const trail = document.createElement('div');
    trail.className = 'trail';
    trail.style.left = `${e.pageX}px`;
    trail.style.top = `${e.pageY}px`;
    document.body.appendChild(trail);
    setTimeout(() => trail.remove(), 1000);
});
</script>
<!-- Bubble Background -->
<div id="bubble-container"></div>

<!-- Cursor Trail Script -->
<script>
document.addEventListener('mousemove', function(e) {
    const trail = document.createElement('div');
    trail.classList.add('cursor-trail');
    trail.style.left = e.clientX + 'px';
    trail.style.top = e.clientY + 'px';
    document.body.appendChild(trail);
    setTimeout(() => {
        trail.remove();
    }, 500);
});

// Bubble Generator
const bubbleContainer = document.getElementById('bubble-container');
for (let i = 0; i < 40; i++) {
    const bubble = document.createElement('div');
    bubble.classList.add('bubble');
    const size = Math.random() * 20 + 10 + 'px';
    bubble.style.width = size;
    bubble.style.height = size;
    bubble.style.left = Math.random() * 100 + '%';
    bubble.style.animationDuration = (Math.random() * 10 + 10) + 's';
    bubbleContainer.appendChild(bubble);
}
</script>

</body>
</html>
