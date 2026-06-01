<?php
require 'conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario normal') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado."); 
} 
$res = $conn->query("SELECT i.*, d.nombre AS distrito, d.ciudad FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Inmobiliario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .inmueble-card { border: none; border-radius: 12px; transition: transform 0.3s ease, box-shadow 0.3s ease; overflow: hidden; background: #fff; }
        .inmueble-card:hover { transform: translateY(-6px); box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important; }
        .img-container { height: 210px; object-fit: cover; background: #eaedf1; }
        .badge-price { font-size: 1.3rem; font-weight: 700; color: #198754; }
    </style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container mt-4">
    <div class="border-start border-primary border-4 ps-3 mb-4">
        <h2 class="fw-bold text-dark m-0">Propiedades Disponibles</h2>
        <p class="text-muted mb-0">Explora inmuebles exclusivos listos para adquirir</p>
    </div>
    <div class="row">
        <?php while($i = $res->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card inmueble-card h-100 shadow-sm">
                    <?php if($i['foto'] && file_exists("uploads/".$i['foto'])): ?>
                        <img src="uploads/<?= $i['foto'] ?>" class="card-img-top img-container">
                    <?php else: ?>
                        <div class="img-container d-flex align-items-center justify-content-center text-muted">📁 Sin Imagen Cargada</div>
                    <?php endif; ?>
                    <div class="card-body d-flex flex-column justify-content-between">
                        <div>
                            <span class="badge bg-primary bg-opacity-10 text-primary mb-2 px-3 py-1 rounded-pill text-uppercase font-monospace"><?= htmlspecialchars($i['tipo']) ?></span>
                            <div class="badge-price mb-2">$<?= number_format($i['precio'], 2) ?></div>
                        </div>
                        <p class="text-secondary small mb-0 mt-2 d-flex align-items-center gap-1">
                            📍 <?= htmlspecialchars($i['distrito']) ?>, <?= htmlspecialchars($i['ciudad']) ?>
                        </p>
                    </div>
                    <div class="card-footer bg-light border-0 d-flex gap-2 p-3">
                        <a href="detalle_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-outline-secondary btn-sm w-100 rounded-2">Detalles</a>
                        <a href="carrito.php?agregar_id=<?= $i['id'] ?>" class="btn btn-success btn-sm w-100 rounded-2 fw-bold shadow-sm">🛒 Comprar</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>