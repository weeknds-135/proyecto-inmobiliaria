<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado."); 
}

$msg = ''; 
$errors = array('nombre' => '', 'ciudad' => '');
$val_nombre = ''; 
$val_ciudad = '';

// 1. PRIMERO PROCESAMOS LA INSERCIÓN O ELIMINACIÓN
if (isset($_POST['crear'])) {
    $val_nombre = trim($_POST['nombre']); 
    $val_ciudad = trim($_POST['ciudad']);
    
    if (empty($val_nombre)) { 
        $errors['nombre'] = "El campo nombre del distrito es obligatorio."; 
    } elseif(strlen($val_nombre) > 50) { 
        $errors['nombre'] = "Máximo 50 caracteres."; 
    }
    
    if (empty($val_ciudad)) { 
        $errors['ciudad'] = "El campo ciudad es obligatorio."; 
    }
    
    if (!array_filter($errors)) {
        $stmt = $conn->prepare("INSERT INTO distritos (nombre, ciudad) VALUES (?, ?)"); 
        $stmt->bind_param("ss", $val_nombre, $val_ciudad); 
        $stmt->execute();
        $msg = "El distrito se ha creado y guardado exitosamente."; 
        // Limpiamos las variables para vaciar el formulario tras el éxito
        $val_nombre = ''; 
        $val_ciudad = ''; 
    }
}

if (isset($_GET['eliminar_id'])) {
    $stmt = $conn->prepare("DELETE FROM distritos WHERE id = ?"); 
    $stmt->bind_param("i", $_GET['eliminar_id']); 
    $stmt->execute();
    $msg = "Registro eliminado permanentemente del sistema.";
}

// 2. SEGUNDO: HACEMOS LA CONSULTA (Así traerá el nuevo registro inmediatamente)
$listado = $conn->query("SELECT * FROM distritos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Distritos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container-fluid px-4">
    <?php if($msg): ?>
        <div class="alert alert-success shadow-sm"><?= $msg ?></div>
    <?php endif; ?>
    
    <div class="row">
        <div class="col-md-4">
            <div class="card p-3 shadow-sm">
                <h5 class="fw-bold mb-3">Crear Nuevo Distrito</h5>
                <form method="POST">
                    <div class="mb-2">
                        <label class="form-label mb-1">Nombre del Distrito</label>
                        <input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($val_nombre) ?>">
                        <?php if($errors['nombre']): ?><small class="text-danger"><?= $errors['nombre'] ?></small><?php endif; ?>
                    </div>
                    <div class="mb-2">
                        <label class="form-label mb-1">Ciudad</label>
                        <input type="text" name="ciudad" class="form-control" value="<?= htmlspecialchars($val_ciudad) ?>">
                        <?php if($errors['ciudad']): ?><small class="text-danger"><?= $errors['ciudad'] ?></small><?php endif; ?>
                    </div>
                    <button type="submit" name="crear" class="btn btn-primary w-100 mt-2">Guardar Registro</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-8">
            <h2 class="mb-3">Listado de Distritos (Tabla Principal)</h2>
            <table class="table bg-white table-striped align-middle shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre Distrito</th>
                        <th>Ciudad</th>
                        <th>Acciones Obligatorias</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($d = $listado->fetch_assoc()): ?>
                    <tr>
                        <td><?= $d['id'] ?></td>
                        <td><strong><?= htmlspecialchars($d['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($d['ciudad']) ?></td>
                        <td>
                            <a href="editar_distrito.php?id=<?= $d['id'] ?>" class="btn btn-warning btn-sm me-2">Editar</a>
                            <a href="distritos.php?eliminar_id=<?= $d['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de eliminar este distrito? Se borrarán sus inmuebles asociados.')">Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>