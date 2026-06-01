<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Acceso denegado."); 
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = ''; $err = '';

// Obtener los datos actuales del distrito
$stmt = $conn->prepare("SELECT * FROM distritos WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$distrito = $stmt->get_result()->fetch_assoc();

if (!$distrito) {
    die("El distrito solicitado no existe.");
}

// Procesar la actualización
if (isset($_POST['actualizar'])) {
    $nombre = trim($_POST['nombre']);
    $ciudad = trim($_POST['ciudad']);
    
    if (empty($nombre) || empty($ciudad)) {
        $err = "Todos los campos son obligatorios.";
    } else {
        $update = $conn->prepare("UPDATE distritos SET nombre = ?, ciudad = ? WHERE id = ?");
        $update->bind_param("ssi", $nombre, $ciudad, $id);
        if ($update->execute()) {
            $msg = "Distrito actualizado correctamente.";
            // Refrescar los datos en pantalla
            $distrito['nombre'] = $nombre;
            $distrito['ciudad'] = $ciudad;
        } else {
            $err = "Error al actualizar el registro.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Distrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-card { border: none; border-radius: 12px; background: #fff; }
    </style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container mt-4" style="max-width: 500px;">
    <?php if($msg): ?><div class="alert alert-success border-0 shadow-sm py-2"><?= $msg ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger border-0 shadow-sm py-2"><?= $err ?></div><?php endif; ?>

    <div class="card admin-card p-4 shadow-sm">
        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">✏️ Editar Distrito (ID: <?= $id ?>)</h5>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">Nombre del Distrito</label>
                <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($distrito['nombre']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label text-secondary small fw-bold">Ciudad</label>
                <input type="text" name="ciudad" class="form-control" value="<?= htmlspecialchars($distrito['ciudad']) ?>" required>
            </div>
            <div class="d-flex gap-2 mt-4">
                <a href="distritos.php" class="btn btn-outline-secondary w-50 rounded-2">Volver</a>
                <button type="submit" name="actualizar" class="btn btn-warning w-50 rounded-2 fw-bold text-dark">Actualizar</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>