<?php
require 'conexion.php'; 
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario normal') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado."); 
} 

// 1. Capturar las variables de los filtros desde la URL (Método GET)
$filtro_tipo = isset($_GET['buscar_tipo']) ? trim($_GET['buscar_tipo']) : '';
$filtro_distrito = isset($_GET['buscar_distrito']) ? intval($_GET['buscar_distrito']) : 0;
$filtro_precio = isset($_GET['buscar_precio']) ? floatval($_GET['buscar_precio']) : 0;

// 2. Construir la consulta SQL dinámica con base en lo que use el usuario
$sql = "SELECT i.*, d.nombre AS distrito, d.ciudad FROM inmuebles i JOIN distritos d ON i.distrito_id = d.id WHERE 1=1";

if (!empty($filtro_tipo)) {
    $sql .= " AND i.tipo LIKE '%" . $conn->real_escape_string($filtro_tipo) . "%'";
}
if ($filtro_distrito > 0) {
    $sql .= " AND i.distrito_id = " . $filtro_distrito;
}
if ($filtro_precio > 0) {
    $sql .= " AND i.precio <= " . $filtro_precio;
}

// Ordenar para mostrar los más recientes primero
$sql .= " ORDER BY i.id DESC";
$res = $conn->query($sql);

// Cargar la lista completa de distritos para llenar el menú desplegable del filtro
$lista_distritos = $conn->query("SELECT id, nombre FROM distritos ORDER BY nombre ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Catálogo Inmobiliario con Filtros</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .inmueble-card { border: none; border-radius: 12px; transition: transform 0.3s ease, box-shadow 0.3s ease; overflow: hidden; background: #fff; }
        .inmueble-card:hover { transform: translateY(-6px); box-shadow: 0 12px 24px rgba(0,0,0,0.1) !important; }
        .img-container { height: 210px; object-fit: cover; background: #eaedf1; }
        .badge-price { font-size: 1.3rem; font-weight: 700; color: #198754; }
        .filter-card { border: none; border-radius: 12px; background: #ffffff; box-shadow: 0 4px 12px rgba(0,0,0,0.05); }
    </style>
</head>
<body class="bg-light">
<?php registrar_navbar(); ?>
<div class="container mt-4">
    
    <div class="border-start border-primary border-4 ps-3 mb-4">
        <h2 class="fw-bold text-dark m-0">Propiedades Disponibles</h2>
        <p class="text-muted mb-0">Usa el panel inferior para filtrar y encontrar tu inmueble ideal</p>
    </div>

    <div class="card filter-card p-4 mb-4">
        <form method="GET" action="catalogo.php" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label text-secondary small fw-bold">🔍 ¿Qué tipo de inmueble buscas?</label>
                <input type="text" name="buscar_tipo" class="form-control" placeholder="Ej. Casa, Departamento, Oficina" value="<?= htmlspecialchars($filtro_tipo) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">📍 Distrito / Ubicación</label>
                <select name="buscar_distrito" class="form-select">
                    <option value="0">-- Todos los Distritos --</option>
                    <?php while($d = $lista_distritos->fetch_assoc()): ?>
                        <option value="<?= $d['id'] ?>" <?= $d['id'] == $filtro_distrito ? 'selected' : '' ?>>
                            <?= htmlspecialchars($d['nombre']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label text-secondary small fw-bold">💰 Presupuesto Máximo ($)</label>
                <input type="number" name="buscar_precio" class="form-control" placeholder="Ej. 150000" value="<?= $filtro_precio > 0 ? $filtro_precio : '' ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-bold rounded-2 py-2">Filtrar</button>
                <?php if(!empty($filtro_tipo) || $filtro_distrito > 0 || $filtro_precio > 0): ?>
                    <a href="catalogo.php" class="btn btn-outline-danger py-2" title="Limpiar Filtros">✖</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="row">
        <?php if($res->num_rows === 0): ?>
            <div class="col-12">
                <div class="alert alert-warning text-center py-4 rounded-3 border-0 shadow-sm">
                    😔 No se encontraron inmuebles que coincidan con los criterios de búsqueda establecidos.
                </div>
            </div>
        <?php else: ?>
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
                                <p class="text-muted small mb-0 mt-1">📐 Área: <?= $i['area_m2'] ?> m² | 🛏️ Hab: <?= $i['habitaciones'] ?></p>
                            </div>
                            <p class="text-secondary small mb-0 mt-3 d-flex align-items-center gap-1 border-top pt-2">
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
        <?php endif; ?>
    </div>
</div>
</body>
</html>