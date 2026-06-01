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

// Función con rutas absolutas automáticas (Evita errores de carpeta ../)
function registrar_navbar($ruta_no_usada = "") {
    if (!isset($_SESSION['rol'])) return;
    $cant = isset($_SESSION['carrito']) ? array_sum($_SESSION['carrito']) : 0;
    
    // Base de la URL de tu servidor local
    $url_base = "http://localhost/inmobiliaria/";
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4 shadow">
      <div class="container">
        <span class="navbar-brand fw-bold">Inmobiliaria</span>
        <div class="collapse navbar-collapse">
          <ul class="navbar-nav me-auto">
            <?php if ($_SESSION['rol'] == 'administrador'): ?>
                <li class="nav-item"><a class="nav-link" href="<?= $url_base ?>admin/distritos.php">Gestión Distritos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $url_base ?>admin/inmuebles.php">Gestión Inmuebles</a></li>
            <?php else: ?>
                <li class="nav-item"><a class="nav-link" href="<?= $url_base ?>catalogo.php">Catálogo</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= $url_base ?>carrito.php">Carrito <span class="badge bg-danger ms-1"><?= $cant ?></span></a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="<?= $url_base ?>perfil.php">Mi Perfil</a></li>
          </ul>
          <span class="navbar-text me-3 text-white">Hola, <strong><?= htmlspecialchars($_SESSION['nombre']) ?></strong></span>
          <a href="<?= $url_base ?>logout.php" class="btn btn-outline-danger btn-sm">Salir</a>
        </div>
      </div>
    </nav>
    <?php
}
?>