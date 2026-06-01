<?php
if (session_status() == PHP_SESSION_NONE) { 
    session_start(); 
}

$host = "localhost"; 
$user = "root"; 
$pass = ""; 
$db = "inmobiliaria_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) { 
    die("Error de conexión a la base de datos."); 
}

function registrar_navbar($ruta_no_usada = "") {
    if (!isset($_SESSION['rol'])) return;
    $cant = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
    $url_base = "http://localhost/inmobiliaria/";
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow-sm py-3">
      <div class="container">
        <a class="navbar-brand fw-bold fs-4 text-uppercase tracking-wider" href="#">
            <span class="text-primary">🏠</span> Inmobiliaria
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
          <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
          <ul class="navbar-nav me-auto mb-2 mb-lg-0 gap-2">
            <?php if ($_SESSION['rol'] == 'administrador'): ?>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill transition" href="<?= $url_base ?>admin/distritos.php">📁 Gestión Distritos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill transition" href="<?= $url_base ?>admin/inmuebles.php">🏢 Gestión Inmuebles</a>
                </li>
            <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill transition" href="<?= $url_base ?>catalogo.php">🔍 Ver Catálogo</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-3 rounded-pill transition position-relative" href="<?= $url_base ?>carrito.php">
                        🛒 Mi Carrito
                        <?php if($cant > 0): ?>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"><?= $cant ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
          </ul>
          <div class="d-flex align-items-center gap-3">
            <span class="navbar-text text-white bg-secondary bg-opacity-25 px-3 py-1 rounded-pill border border-secondary border-opacity-50">
                👤 Conectado: <strong class="text-info"><?= htmlspecialchars($_SESSION['nombre']) ?></strong>
            </span>
            <a href="<?= $url_base ?>logout.php" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold transition">Salir</a>
          </div>
        </div>
      </div>
    </nav>
    <style>
        .transition { transition: all 0.3s ease; }
        .nav-link { color: #rgba(255,255,255,0.75); }
        .nav-link:hover { background-color: rgba(255,255,255,0.1); color: #fff !important; }
    </style>
    <?php
}
?>