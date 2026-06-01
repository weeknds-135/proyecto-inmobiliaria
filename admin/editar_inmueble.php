<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Acceso denegado."); 
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = ''; $err = '';

// Obtener los datos actuales del inmueble
$stmt = $conn->prepare("SELECT * FROM inmuebles WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$inmueble = $stmt->get_result()->fetch_assoc();

if (!$inmueble) {
    die("El inmueble solicitado no existe.");
}

// Cargar distritos para el selector dinámico
$distritos = $conn->query("SELECT id, nombre FROM distritos");

// Procesar la actualización
if (isset($_POST['actualizar'])) {
    $tipo = trim($_POST['tipo']);
    $area = intval($_POST['area']);
    $hab = intval($_POST['hab']);
    $precio = floatval($_POST['precio']);
    $distrito_id = intval($_POST['distrito_id']);
    $foto = $inmueble['foto']; // Mantener la foto actual por defecto

    $subir_ok = true;
    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, array("jpg", "jpeg", "png", "gif"))) { 
            $err = "Formato de imagen no válido (use JPG, PNG o GIF).";
            $subir_ok = false;
        } else {
            $foto = time() . "_" . basename($_FILES["foto"]["name"]);
        }
    }

    if ($subir_ok) {
        if (empty($tipo) || $area <= 0 || $precio <= 0) {
            $err = "Por favor, introduzca valores válidos.";
        } else {
            if (!empty($_FILES['foto']['name'])) {
                move_uploaded_file($_FILES["foto"]["tmp_name"], "../uploads/" . $foto);
            }

            $update = $conn->prepare("UPDATE inmuebles SET tipo = ?, area_m2 = ?, habitaciones = ?, precio = ?, foto = ?, distrito_id = ? WHERE id = ?");
            $update->bind_param("siiisii", $tipo, $area, $hab, $precio, $foto, $distrito_id, $id);
            
            if ($update->execute()) {
                $msg = "Inmueble actualizado correctamente.";
                // Recargar variables locales para refrescar la vista
                $inmueble['tipo'] = $tipo;
                $inmueble['area_m2'] = $area;
                $inmueble['habitaciones'] = $hab;
                $inmueble['precio'] = $precio;
                $inmueble['foto'] = $foto;
                $inmueble['distrito_id'] = $distrito_id;
            } else {
                $err = "Error al actualizar en la base de datos.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Inmueble</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.admin-card { border: none; border-radius: 12px; background: #fff; }</style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container mt-4" style="max-width: 550px;">
    <?php if($msg): ?><div class="alert alert-success border-0 shadow-sm py-2"><?= $msg ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger border-0 shadow-sm py-2"><?= $err ?></div><?php endif; ?>

    <div class="card admin-card p-4 shadow-sm">
        <h5 class="fw-bold mb-3 text-dark border-bottom pb-2">✏️ Editar Inmueble (ID: <?= $id ?>)</h5>
        <form method="POST" enctype="multipart/form-data">
            <div class="mb-2">
                <label class="small fw-bold text-secondary">Tipo de Inmueble</label>
                <input type="text" name="tipo" class="form-control" value="<?= htmlspecialchars($inmueble['tipo']) ?>" required>
            </div>
            <div class="row">
                <div class="col-md-6 mb-2">
                    <label class="small fw-bold text-secondary">Área m²</label>
                    <input type="number" name="area" class="form-control" value="<?= $inmueble['area_m2'] ?>" required>
                </div>
                <div class="col-md-6 mb-2">
                    <label class="small fw-bold text-secondary">Habitaciones</label>
                    <input type="number" name="hab" class="form-control" value="<?= $inmueble['habitaciones'] ?>" required>
                </div>
            </div>
            <div class="mb-2">
                <label class="small fw-bold text-secondary">Precio ($)</label>
                <input type="number" step="0.01" name="precio" class="form-control" value="<?= $inmueble['precio'] ?>" required>
            </div>
            <div class="mb-2">
                <label class="small fw-bold text-secondary">Distrito Asignado</label>
                <select name="distrito_id" class="form-select" required>
                    <?php while($d = $distritos->fetch_assoc()): ?>
                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $inmueble['distrito_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="mb-3 mt-3">
                <label class="small fw-bold text-secondary d-block mb-1">Imagen del Inmueble</label>
                <?php if($inmueble['foto'] && file_exists("../uploads/".$inmueble['foto'])): ?>
                    <img src="../uploads/<?= $inmueble['foto'] ?>" width="80" class="rounded mb-2 d-block border">
                <?php endif; ?>
                <input type="file" name="foto" class="form-control form-control-sm">
                <small class="text-muted text-xs">Dejar vacío si no deseas cambiar la imagen actual.</small>
            </div>
            
            <div class="d-flex gap-2 mt-4">
                <a href="inmuebles.php" class="btn btn-outline-secondary w-50 rounded-2">Volver</a>
                <button type="submit" name="actualizar" class="btn btn-warning w-50 rounded-2 fw-bold text-dark">Guardar Cambios</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>