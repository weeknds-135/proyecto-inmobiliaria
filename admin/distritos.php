<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado."); 
}

$msg = ''; $errors = array('nombre' => '', 'ciudad' => '');
$val_nombre = ''; $val_ciudad = '';

if (isset($_POST['crear'])) {
    $val_nombre = trim($_POST['nombre']); $val_ciudad = trim($_POST['ciudad']);
    if (empty($val_nombre)) { $errors['nombre'] = "El campo nombre es obligatorio."; }
    if (empty($val_ciudad)) { $errors['ciudad'] = "El campo ciudad es obligatorio."; }
    
    if (!array_filter($errors)) {
        $stmt = $conn->prepare("INSERT INTO distritos (nombre, ciudad) VALUES (?, ?)"); 
        $stmt->bind_param("ss", $val_nombre, $val_ciudad); 
        $stmt->execute();
        $msg = "Distrito guardado exitosamente."; 
        $val_nombre = ''; $val_ciudad = ''; 
    }
}

if (isset($_GET['eliminar_id'])) {
    $stmt = $conn->prepare("DELETE FROM distritos WHERE id = ?"); 
    $stmt->bind_param("i", $_GET['eliminar_id']); 
    $stmt->execute();
    $msg = "Registro eliminado con éxito.";
}

$listado = $conn->query("SELECT * FROM distritos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Distritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .admin-card { border: none; border-radius: 12px; background: #fff; }
        .table { border-radius: 8px; overflow: hidden; }
    </style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container mt-2">
    <?php if($msg): ?>
        <div class="alert alert-success border-0 shadow-sm py-2 rounded-3 mb-3"><?= $msg ?></div>
    <?php endif; ?>
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card admin-card p-4 shadow-sm">
                <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">➕ Nuevo Distrito</h5>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Nombre del Distrito</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($val_nombre) ?>" placeholder="Ej. Miraflores">
                        <?php if($errors['nombre']): ?><small class="text-danger"><?= $errors['nombre'] ?></small><?php endif; ?>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" value="<?= htmlspecialchars($val_ciudad) ?>" placeholder="Ej. Lima">
                        <?php if($errors['ciudad']): ?><small class="text-danger"><?= $errors['ciudad'] ?></small><?php endif; ?>
                    </div>
                    <button type="submit" name="crear" class="btn btn-primary w-100 mt-2 rounded-2 fw-bold">Guardar Registro</button>
                </form>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card admin-card p-4 shadow-sm">
                <h4 class="fw-bold mb-3 text-dark border-bottom pb-2">📁 Distritos (Tabla Principal)</h4>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr>
                            <th width="80">ID</th>
                            <th>Nombre Distrito</th>
                            <th>Ciudad</th>
                            <th width="180" class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($d = $listado->fetch_assoc()): ?>
                        <tr>
                            <td><span class="text-muted font-monospace"><?= $d['id'] ?></span></td>
                            <td><strong class="text-dark"><?= htmlspecialchars($d['nombre']) ?></strong></td>
                            <td><?= htmlspecialchars($d['ciudad']) ?></td>
                            <td class="text-center">
                                <a href="editar_distrito.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm px-3 rounded-pill me-1">Editar</a>
                                <a href="distritos.php?eliminar_id=<?= $d['id'] ?>" class="btn btn-outline-danger btn-sm px-3 rounded-pill" onclick="return confirm('¿Seguro?')">Eliminar</a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
</body>
</html>