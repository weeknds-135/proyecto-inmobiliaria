<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Acceso denegado."); 
}

$msg = ''; $err = '';

// Procesar Creación
if (isset($_POST['crear'])) {
    $t = trim($_POST['tipo']); 
    $a = intval($_POST['area']); 
    $h = intval($_POST['hab']); 
    $p = floatval($_POST['precio']); 
    $d_id = intval($_POST['distrito_id']); 
    $foto = null; 
    $subir_ok = true;

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, array("jpg", "jpeg", "png", "gif"))) { 
            $err = "El archivo seleccionado no es una imagen válida (jpg, jpeg, png, gif).";
            $subir_ok = false;
        } else {
            $foto = time() . "_" . basename($_FILES["foto"]["name"]);
        }
    }

    if ($subir_ok) {
        if(empty($t) || $a <= 0 || $p <= 0) { 
            $err = "Por favor, complete todos los campos con valores válidos."; 
        } else {
            if ($foto) { 
                move_uploaded_file($_FILES["foto"]["tmp_name"], "../uploads/" . $foto); 
            } 
            $stmt = $conn->prepare("INSERT INTO inmuebles (tipo, area_m2, habitaciones, precio, foto, distrito_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiisi", $t, $a, $h, $p, $foto, $d_id); 
            $stmt->execute(); 
            $msg = "Inmueble guardado con éxito."; 
        }
    }
}

// Procesar Eliminación
if (isset($_GET['eliminar_id'])) {
    $stmt = $conn->prepare("DELETE FROM inmuebles WHERE id = ?"); 
    $stmt->bind_param("i", $_GET['eliminar_id']); 
    $stmt->execute();
    $msg = "El inmueble ha sido eliminado.";
}

$listado = $conn->query("SELECT i.*, d.nombre AS dist FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id");
$distritos = $conn->query("SELECT id, nombre FROM distritos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Inmuebles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container-fluid px-4">
    <?php if($msg): ?><div class="alert alert-success"><?= $msg ?></div><?php endif; ?>
    <?php if($err): ?><div class="alert alert-danger"><?= $err ?></div><?php endif; ?>
    
    <div class="row">
        <div class="col-md-3">
            <div class="card p-3 shadow-sm">
                <h5 class="fw-bold mb-3">Nuevo Inmueble</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-2"><label>Tipo Inmueble</label><input type="text" name="tipo" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label>Área m²</label><input type="number" name="area" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label>Habitaciones</label><input type="number" name="hab" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label>Precio ($)</label><input type="number" step="0.01" name="precio" class="form-control form-control-sm" required></div>
                    <div class="mb-2">
                        <label>Distrito Asignado</label>
                        <select name="distrito_id" class="form-select form-select-sm" required>
                            <?php while($d = $distritos->fetch_assoc()): ?>
                                <option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label>Imagen (Opcional)</label><input type="file" name="foto" class="form-control form-control-sm"></div>
                    <button type="submit" name="crear" class="btn btn-primary btn-sm w-100">Guardar Inmueble</button>
                </form>
            </div>
        </div>
        
        <div class="col-md-9">
            <h2>Gestión de Inmuebles (Tabla Secundaria)</h2>
            <table class="table table-striped align-middle bg-white shadow-sm">
                <thead class="table-dark">
                    <tr>
                        <th>Miniatura</th>
                        <th>Tipo</th>
                        <th>Precio</th>
                        <th>Distrito</th>
                        <th>Acciones de Gestión</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($i = $listado->fetch_assoc()): ?>
                    <tr>
                        <td>
                            <?php if($i['foto'] && file_exists("../uploads/".$i['foto'])): ?>
                                <img src="../uploads/<?= $i['foto'] ?>" width="55" class="img-thumbnail">
                            <?php else: ?>
                                <span class="text-muted small">[Sin Foto]</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($i['tipo']) ?></strong></td>
                        <td>$<?= number_format($i['precio'], 2) ?></td>
                        <td><span class="badge bg-info text-dark"><?= htmlspecialchars($i['dist']) ?></span></td>
                        <td>
                            <a href="../detalle_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-info btn-sm text-white">Ver Detalle</a>
                            <a href="editar_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="inmuebles.php?eliminar_id=<?= $i['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Seguro?')">Borrar</a>
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