<?php
session_start();
include 'config.php';

// Cek apakah user sudah login 
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Menambah tugas ke dalam database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_task'])) {
    $nama = $_POST['nama'];
    $prioritas = $_POST['prioritas'];
    $tanggal = $_POST['tanggal'];

    if (!empty($nama) && !empty($prioritas) && !empty($tanggal)) {
        $sql = "INSERT INTO tasks (nama, prioritas, tanggal, user_id, status) 
                VALUES ('$nama', '$prioritas', '$tanggal', '$user_id', 'Belum Selesai')";
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Semua field harus diisi!";
    }
}

// Update status tugas
if (isset($_GET['toggle_status'])) {
    $task_id = $_GET['toggle_status'];
    $current_status = $_GET['current_status'];
    $new_status = ($current_status == "Selesai") ? "Belum Selesai" : "Selesai";
    $conn->query("UPDATE tasks SET status='$new_status' WHERE id='$task_id'");
    header("Location: index.php");
    exit();
}

// Menambah subtasks ke dalam database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_subtask'])) {
    $task_id = $_POST['task_id'];
    $subtask_nama = $_POST['subtask_nama'];

    if (!empty($task_id) && !empty($subtask_nama)) {
        $sql = "INSERT INTO subtasks (task_id, nama, status) 
                VALUES ('$task_id', '$subtask_nama', 'Belum Selesai')";
        if ($conn->query($sql) === TRUE) {
            header("Location: index.php");
            exit();
        } else {
            echo "Error: " . $conn->error;
        }
    } else {
        echo "Subtask tidak boleh kosong!";
    }
}

// Pencarian dan filter tugas
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$query = "SELECT * FROM tasks WHERE user_id='$user_id' ORDER BY id DESC";
$result = $conn->query($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <!-- Flatpickr CSS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List</title>
    <link rel="stylesheet" href="cssindex.css">
    <script>
        function confirmDelete(id) {
            return confirm("Apakah Anda yakin ingin menghapus item ini?");
        }
        function checkDeadlines() {
            let today = new Date().toISOString().split('T')[0];
            document.querySelectorAll('.task-item').forEach(task => {
                let deadline = task.getAttribute('data-deadline');
                if (deadline < today) {
                    task.style.color = 'red';
                    alert("Tugas '" + task.getAttribute('data-name') + "' sudah melewati deadline!");
                }
            });
        }
        window.onload = checkDeadlines;
    </script>
</head>
<body>
<div class="container">
    <h2>To-Do List</h2>
    <p>Halo, <?= $_SESSION['user_id']; ?>! <a href="logout.php">Logout</a></p>
    
    <!-- Form Pencarian -->
    <form method="GET">
        <input type="text" name="search" placeholder="Cari Tugas" value="<?= $search; ?>">
        <select name="status">
            <option value="">Semua Status</option>
            <option value="Belum Selesai" <?= $status_filter == "Belum Selesai" ? "selected" : ""; ?>>Belum Selesai</option>
            <option value="Selesai" <?= $status_filter == "Selesai" ? "selected" : ""; ?>>Selesai</option>
        </select>
        <button type="submit">Cari</button>
    </form>

    <!-- Form Tambah Tugas -->
    <form method="POST">
        <input type="text" name="nama" placeholder="Nama Tugas" required>
        <select name="prioritas">
            <option value="Tinggi">Tinggi</option>
            <option value="Sedang">Sedang</option>
            <option value="Rendah">Rendah</option>
        </select>
        <input type="date" name="tanggal" required>
        <button type="submit" name="add_task">Tambah Tugas</button>
    </form>

    <h3>Daftar Tugas</h3>
    <ul>
        <?php while ($row = $result->fetch_assoc()): ?>
            <li class="task-item" data-deadline="<?= $row['tanggal']; ?>" data-name="<?= $row['nama']; ?>">
                <strong><?= $row['nama']; ?></strong> (<?= $row['prioritas']; ?>, <?= $row['tanggal']; ?>, <?= $row['status']; ?>)
                <a href="?toggle_status=<?= $row['id']; ?>&current_status=<?= $row['status']; ?>">
                    <?= $row['status'] == "Selesai" ? "Tidak Selesai" : "Selesai"; ?>
                </a> |
                <a href="delete.php?id=<?= $row['id']; ?>" class="btn-delete" onclick="return confirmDelete(<?= $row['id']; ?>)">Hapus</a>
                |
                <a href="edit.php?id=<?= $row['id']; ?>" class="btn-delete" onclick="return confirmUpdate(<?= $row['id']; ?>)">Edit</a>
                <!-- Query untuk subtasks -->
                <?php
                $task_id = $row['id'];
                $subtask_query = "SELECT * FROM subtasks WHERE task_id='$task_id'";
                $subtask_result = $conn->query($subtask_query);
                ?>
                <ul>
                    <?php while ($subtask = $subtask_result->fetch_assoc()): ?>
                        <li>
                            <?= $subtask['nama']; ?> - <?= $subtask['status']; ?>
                            <a href="toggle_subtask.php?id=<?= $subtask['id']; ?>">Selesai</a>
                        </li>
                    <?php endwhile; ?>
                </ul>
                
                <!-- Form tambah subtasks -->
                <form method="POST">
                    <input type="hidden" name="task_id" value="<?= $row['id']; ?>">
                    <input type="text" name="subtask_nama" placeholder="Tambah Subtask" required>
                    <button type="submit" name="add_subtask">Tambah</button>
                </form>
            </li>
        <?php endwhile; ?>
    </ul>
</div>
<!-- Flatpickr JS -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("input[name='tanggal']", {
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "l, d F Y",
            minDate: "today",
            theme: "dark",
            locale: "id", // Bahasa Indonesia
            onReady: function(selectedDates, dateStr, instance) {
                instance.altInput.setAttribute("placeholder", "Atur Deadline");
            }
        });
    });
</script>

<!-- Particles.js -->
<script src="https://cdn.jsdelivr.net/npm/particles.js@2.0.0/particles.min.js"></script>

<!-- Canvas Particles -->
<div id="tinkerbell-particles"></div>

<style>
  #tinkerbell-particles {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: -10;
  }
</style>

<script>
  particlesJS("tinkerbell-particles", {
    "particles": {
      "number": { "value": 80 },
      "color": { "value": "#b5f2c2" },
      "shape": {
        "type": "circle",
        "stroke": { "width": 0, "color": "#000000" }
      },
      "opacity": {
        "value": 0.5,
        "random": true
      },
      "size": {
        "value": 6,
        "random": true
      },
      "line_linked": {
        "enable": true,
        "distance": 150,
        "color": "#b5f2c2",
        "opacity": 0.4,
        "width": 1
      },
      "move": {
        "enable": true,
        "speed": 2,
        "direction": "none",
        "out_mode": "bounce"
      }
    },
    "interactivity": {
      "events": {
        "onhover": { "enable": true, "mode": "repulse" },
        "onclick": { "enable": true, "mode": "push" }
      },
      "modes": {
        "repulse": { "distance": 100, "duration": 0.4 },
        "push": { "particles_nb": 4 }
      }
    },
    "retina_detect": true
  });
</script>

<!-- Trail Cursor Effect -->
<style>
  .trail-dot {
    position: fixed;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    pointer-events: none;
    z-index: 9999;
    background: radial-gradient(circle, #d0ffb7 0%, #90ee90 80%);
    animation: blink 1.5s infinite ease-in-out;
  }

  @keyframes blink {
    0%, 100% { opacity: 0.5; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.3); }
  }
</style>

<script>
  document.addEventListener("mousemove", function(e) {
    const dot = document.createElement("div");
    dot.className = "trail-dot";
    dot.style.left = `${e.pageX}px`;
    dot.style.top = `${e.pageY}px`;
    document.body.appendChild(dot);
    setTimeout(() => dot.remove(), 600);
  });
</script>

<div id="trail-container"></div>
<script>
    const trailContainer = document.getElementById('trail-container');

    document.addEventListener('mousemove', e => {
        const dot = document.createElement('div');
        dot.className = 'trail-dot';
        dot.style.left = `${e.clientX}px`;
        dot.style.top = `${e.clientY}px`;
        trailContainer.appendChild(dot);

        setTimeout(() => {
            dot.remove();
        }, 1000);
    });
</script>
<!-- Background Canvas & Cursor Trail -->
<canvas id="particleCanvas"></canvas>
<div id="cursor-trail"></div>

<script>
// Particle + Bubbles + Salju
const canvas = document.getElementById('particleCanvas');
const ctx = canvas.getContext('2d');
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;

let particles = [];

for (let i = 0; i < 100; i++) {
  particles.push({
    x: Math.random() * canvas.width,
    y: Math.random() * canvas.height,
    r: Math.random() * 4 + 1,
    d: Math.random() * 100,
    vx: -0.5 + Math.random(),
    vy: Math.random() + 0.5,
  });
}

function drawParticles() {
  ctx.clearRect(0, 0, canvas.width, canvas.height);
  ctx.fillStyle = "rgba(255, 255, 255, 0.8)";
  ctx.beginPath();
  for (let i = 0; i < particles.length; i++) {
    const p = particles[i];
    ctx.moveTo(p.x, p.y);
    ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2, true);
  }
  ctx.fill();
  moveParticles();
}

let angle = 0;

function moveParticles() {
  angle += 0.01;
  for (let i = 0; i < particles.length; i++) {
    const p = particles[i];
    p.y += Math.cos(angle + p.d) + 1 + p.r / 2;
    p.x += Math.sin(angle) * 2;

    if (p.x > canvas.width + 5 || p.x < -5 || p.y > canvas.height) {
      if (i % 3 > 0) {
        particles[i] = {x: Math.random() * canvas.width, y: -10, r: p.r, d: p.d, vx: p.vx, vy: p.vy};
      } else {
        if (Math.sin(angle) > 0) {
          particles[i] = {x: -5, y: Math.random() * canvas.height, r: p.r, d: p.d, vx: p.vx, vy: p.vy};
        } else {
          particles[i] = {x: canvas.width + 5, y: Math.random() * canvas.height, r: p.r, d: p.d, vx: p.vx, vy: p.vy};
        }
      }
    }
  }
}

setInterval(drawParticles, 33);

// Cursor Trail
const trailContainer = document.getElementById('cursor-trail');
window.addEventListener('mousemove', e => {
  const trail = document.createElement('div');
  trail.className = 'trail';
  trail.style.left = `${e.pageX}px`;
  trail.style.top = `${e.pageY}px`;
  trailContainer.appendChild(trail);
  setTimeout(() => trail.remove(), 800);
});
</script>


</body>
</html>