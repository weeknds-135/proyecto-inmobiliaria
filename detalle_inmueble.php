<?php
require 'conexion.php';
if (!isset($_SESSION['user_id'])) { 
    header("Location: login.php"); 
    exit; 
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT i.*, d.nombre AS distrito, d.ciudad FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id WHERE i.id = ?");
$stmt->bind_param("i", $id); 
$stmt->execute(); 
$inmueble = $stmt->get_result()->fetch_assoc();

if (!$inmueble) { 
    die("El registro solicitado no existe."); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Inmueble</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container" style="max-width: 800px;">
    <div class="card shadow p-4">
        <h2 class="mb-3 fw-bold"><?= htmlspecialchars($inmueble['tipo']) ?> - Vista de Detalle</h2>
        
        <div class="mb-4 text-center bg-dark p-2 rounded">
            <?php if($inmueble['foto'] && file_exists("uploads/".$inmueble['foto'])): ?>
                <img src="uploads/<?= $inmueble['foto'] ?>" class="img-fluid rounded" style="max-height: 400px;">
            <?php elseif($inmueble['foto'] && file_exists("../uploads/".$inmueble['foto'])): ?>
                <img src="../uploads/<?= $inmueble['foto'] ?>" class="img-fluid rounded" style="max-height: 400px;">
            <?php else: ?>
                <div class="text-white py-5">[Imagen de Reemplazo - No se subió archivo]</div>
            <?php endif; ?>
        </div>
        
        <div class="row fs-5 mb-3">
            <div class="col-md-6">
                <p><strong>Precio:</strong> <span class="text-success fw-bold">$<?= number_format($inmueble['precio'], 2) ?></span></p>
                <p><strong>Área Física:</strong> <?= $inmueble['area_m2'] ?> m²</p>
            </div>
            <div class="col-md-6">
                <p><strong>Habitaciones:</strong> <?= $inmueble['habitaciones'] ?></p>
                <p><strong>Distrito:</strong> <?= htmlspecialchars($inmueble['distrito']) ?> (<?= htmlspecialchars($inmueble['ciudad']) ?>)</p>
            </div>
        </div>
        
        <div class="mt-4 pt-3 border-top d-flex gap-2">
            <?php if($_SESSION['rol'] == 'administrador'): ?>
                <a href="admin/inmuebles.php" class="btn btn-secondary">Regresar al Panel</a>
            <?php else: ?>
                <a href="carrito.php?agregar_id=<?= $inmueble['id'] ?>" class="btn btn-success btn-lg px-4 fw-bold">🛒 Comprar / Añadir al Carrito</a>
                <a href="catalogo.php" class="btn btn-outline-secondary btn-lg">Volver al Catálogo</a>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>