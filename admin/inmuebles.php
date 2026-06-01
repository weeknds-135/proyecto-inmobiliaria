<?php
require '../conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'administrador') { 
    http_response_code(403); 
    die("Acceso denegado."); 
}

$msg = ''; $err = '';

if (isset($_POST['crear'])) {
    $t = trim($_POST['tipo']); $a = intval($_POST['area']); $h = intval($_POST['hab']); 
    $p = floatval($_POST['precio']); $d_id = intval($_POST['distrito_id']); $foto = null; $subir_ok = true;

    if (!empty($_FILES['foto']['name'])) {
        $ext = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
        if (!in_array($ext, array("jpg", "jpeg", "png", "gif"))) { 
            $err = "Imagen no válida."; $subir_ok = false;
        } else { $foto = time() . "_" . basename($_FILES["foto"]["name"]); }
    }

    if ($subir_ok) {
        if(empty($t) || $a <= 0 || $p <= 0) { $err = "Complete todos los campos."; } 
        else {
            if ($foto) { move_uploaded_file($_FILES["foto"]["tmp_name"], "../uploads/" . $foto); } 
            $stmt = $conn->prepare("INSERT INTO inmuebles (tipo, area_m2, habitaciones, precio, foto, distrito_id) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("siiisi", $t, $a, $h, $p, $foto, $d_id); $stmt->execute(); 
            $msg = "Inmueble guardado con éxito."; 
        }
    }
}

if (isset($_GET['eliminar_id'])) {
    $stmt = $conn->prepare("DELETE FROM inmuebles WHERE id = ?"); 
    $stmt->bind_param("i", $_GET['eliminar_id']); $stmt->execute();
    $msg = "Inmueble eliminado.";
}

$listado = $conn->query("SELECT i.*, d.nombre AS dist FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id");
$distritos = $conn->query("SELECT id, nombre FROM distritos");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"><title>Panel Inmuebles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>.admin-card { border: none; border-radius: 12px; background: #fff; }.table { border-radius: 8px; overflow: hidden; }</style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container-fluid px-4">
    <?php if($msg): ?><div class="alert alert-success border-0 shadow-sm py-2"><?= $msg ?></div><?php endif; ?>
    <div class="row g-4">
        <div class="col-md-3">
            <div class="card admin-card p-3 shadow-sm">
                <h5 class="fw-bold mb-3 border-bottom pb-2">🏢 Nuevo Inmueble</h5>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-2"><label class="small fw-bold text-secondary">Tipo</label><input type="text" name="tipo" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small fw-bold text-secondary">Área m²</label><input type="number" name="area" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small fw-bold text-secondary">Habitaciones</label><input type="number" name="hab" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small fw-bold text-secondary">Precio ($)</label><input type="number" step="0.01" name="precio" class="form-control form-control-sm" required></div>
                    <div class="mb-2"><label class="small fw-bold text-secondary">Distrito</label>
                        <select name="distrito_id" class="form-select form-select-sm" required>
                            <?php while($d = $distritos->fetch_assoc()): ?><option value="<?= $d['id'] ?>"><?= htmlspecialchars($d['nombre']) ?></option><?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3"><label class="small fw-bold text-secondary">Foto</label><input type="file" name="foto" class="form-control form-control-sm"></div>
                    <button type="submit" name="crear" class="btn btn-primary btn-sm w-100 fw-bold rounded-2">Guardar Inmueble</button>
                </form>
            </div>
        </div>
        <div class="col-md-9">
            <div class="card admin-card p-4 shadow-sm">
                <h4 class="fw-bold mb-3 text-dark border-bottom pb-2">📋 Gestión de Inmuebles</h4>
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-dark">
                        <tr><th>Miniatura</th><th>Tipo Inmueble</th><th>Precio</th><th>Distrito Asignado</th><th class="text-center">Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php while($i = $listado->fetch_assoc()): ?>
                        <tr>
                            <td><?php if($i['foto'] && file_exists("../uploads/".$i['foto'])): ?><img src="../uploads/<?= $i['foto'] ?>" width="50" class="rounded border shadow-sm"><?php else: ?><span class="text-muted small">Sin Foto</span><?php endif; ?></td>
                            <td><strong><?= htmlspecialchars($i['tipo']) ?></strong></td>
                            <td class="text-success fw-bold">$<?= number_format($i['precio'], 2) ?></td>
                            <td><span class="badge bg-info text-dark px-3 py-1 rounded-pill"><?= htmlspecialchars($i['dist']) ?></span></td>
                            <td class="text-center">
                                <a href="../detalle_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-info btn-sm text-white rounded-pill px-3 me-1">Ver</a>
                                <a href="editar_inmueble.php?id=<?= $i['id'] ?>" class="btn btn-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="inmuebles.php?eliminar_id=<?= $i['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3" onclick="return confirm('¿Seguro?')">Borrar</a>
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