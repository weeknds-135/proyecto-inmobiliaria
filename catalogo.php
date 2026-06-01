<?php
require 'conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario normal') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado. Rol no autorizado."); 
} 
$res = $conn->query("SELECT i.*, d.nombre AS distrito, d.ciudad FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Inmobiliario</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container">
    <h2 class="mb-4 fw-bold">Catálogo de Inmuebles Disponibles</h2>
    <div class="row">
        <?php while($i = $res->fetch_assoc()): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm">
                    <?php if($i['foto'] && file_exists("uploads/".$i['foto'])): ?>
                        <img src="uploads/<?= $i['foto'] ?>" class="card-img-top" style="height:200px; object-fit:cover;">
                    <?php else: ?>
                        <div class="bg-secondary text-white text-center py-5" style="height:200px;">[Sin Imagen]</div>
                    <?php endif; ?>
                    <div class="card-body">
                        <h5 class="fw-bold text-primary"><?= htmlspecialchars($i['tipo']) ?></h5>
                        <p class="mb-1"><strong>Precio:</strong> $<?= number_format($i['precio'], 2) ?></p>
                        <p class="text-muted small mb-0">Ubicación: <?= htmlspecialchars($i['distrito']) ?> (<?= htmlspecialchars($i['ciudad']) ?>)</p>
                    </div>
                    <div class="card-footer bg-white border-0 d-flex gap-2 pb-3">
                        <a href="detalle_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-outline-secondary btn-sm w-100">Ver Detalle</a>
                        <a href="carrito.php?agregar_id=<?= $i['id'] ?>" class="btn btn-success btn-sm w-100 fw-bold">🛒 Comprar</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>
</body>
</html>