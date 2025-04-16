<?php
include 'config.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Cek panjang password minimal 8 karakter
    if (strlen($password) < 8) {
        $error = "Password harus memiliki minimal 8 karakter.";
    } else {
        $result = $conn->query("SELECT * FROM users WHERE username='$username'");
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                header("Location: index.php");
                exit();
            } else {
                $error = "Password salah!😡";
            }
        } else {
            $error = "Username tidak ditemukan!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="stylelogin.css">
</head>
<body>

<!-- TRAIL khusus background -->
<div class="background-trail"></div>

<div class="animated-gradient"></div>
<div class="parallax"></div>
<div class="starry-bg"></div>

<div class="loader-wrapper" id="loader">
  <div class="loader"></div>
  <p>Loading dulu yaa...</p>
  <div class="fairy" style="top: 20%; left: 0;"></div>
  <div class="fairy" style="top: 60%; left: -100px; animation-delay: 3s;"></div>
</div>


<div class="login-container">
    <h2>Login</h2>

    <?php if (isset($error)): ?>
        <p class="error-message"><?= $error; ?></p>
    <?php endif; ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password (minimal 8 karakter)" required minlength="8">
        <button type="submit">Login</button>
    </form>

    <p>Belum punya akun? <a href="register.php">Daftar</a></p>
</div>

<div class="particles"></div>

<div class="clouds">
    <div class="cloud1"></div>
    <div class="cloud2"></div>
    <div class="cloud3"></div>
</div>

<script>
document.querySelector("button[type='submit']").addEventListener("click", function(e) {
    const btn = this;
    btn.classList.add("shake-button");

    setTimeout(() => {
        btn.classList.remove("shake-button");
    }, 500);
});
</script>

<script>
window.addEventListener("load", function() {
    setTimeout(() => {
        document.querySelector(".loader-wrapper").style.display = "none";
    }, 3000);
});
</script>

<script>
// ❄️ Salju
for (let i = 0; i < 40; i++) {
    let snow = document.createElement("div");
    snow.className = "snowflake";
    snow.style.width = snow.style.height = Math.random() * 5 + 2 + "px";
    snow.style.left = Math.random() * 100 + "vw";
    snow.style.animationDuration = Math.random() * 10 + 10 + "s";
    document.body.appendChild(snow);
}

// 🫧 Gelembung
for (let i = 0; i < 20; i++) {
    let bub = document.createElement("div");
    bub.className = "bubble";
    bub.style.left = Math.random() * 100 + "vw";
    bub.style.width = bub.style.height = Math.random() * 25 + 10 + "px";
    bub.style.animationDuration = Math.random() * 8 + 5 + "s";
    document.body.appendChild(bub);
}

// 🐾 Cursor Trail di background aja
document.addEventListener("mousemove", function(e) {
    const bg = document.querySelector(".background-trail");
    const dot = document.createElement("div");
    dot.classList.add("trail-dot");
    dot.style.left = `${e.clientX}px`;
    dot.style.top = `${e.clientY}px`;
    bg.appendChild(dot);
    setTimeout(() => dot.remove(), 500);
});

// Inject CSS trail-dot langsung dari JS
const style = document.createElement("style");
style.innerHTML = `
    .trail-dot {
        position: absolute;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.8);
        pointer-events: none;
        transform: translate(-50%, -50%);
        animation: fadeOut 0.5s ease-out forwards;
        z-index: 0;
    }

    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(2); }
    }

    .background-trail {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 0;
        overflow: hidden;
    }
`;
document.head.appendChild(style);
</script>
<script>
  function isOnline() {
    return window.navigator.onLine;
  }

  window.addEventListener('load', function () {
    const flowerContainer = document.getElementById('flower-container');
    if (!isOnline()) {
      flowerContainer.style.display = 'none';
    }
  });
</script>


</body>
</html>
