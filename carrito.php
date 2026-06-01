<?php
require 'conexion.php'; if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario normal') { http_response_code(403); die("Denegado"); }
if (isset($_GET['agregar_id'])) {
    $id = $_GET['agregar_id']; if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = array();
    $_SESSION['carrito'][$id] = isset($_SESSION['carrito'][$id]) ? $_SESSION['carrito'][$id] + 1 : 1;
    header("Location: catalogo.php"); exit;
}
if (isset($_GET['eliminar_id'])) { unset($_SESSION['carrito'][$_GET['eliminar_id']]); header("Location: carrito.php"); exit; }
if (isset($_GET['vaciar'])) { unset($_SESSION['carrito']); header("Location: carrito.php"); exit; }
$items = array(); $total = 0;
if (!empty($_SESSION['carrito'])) {
    $ids = implode(',', array_keys($_SESSION['carrito']));
    $res = $conn->query("SELECT id, tipo, precio, foto FROM inmuebles WHERE id IN ($ids)");
    while ($r = $res->fetch_assoc()) {
        $r['cantidad'] = $_SESSION['carrito'][$r['id']]; $r['subtotal'] = $r['precio'] * $r['cantidad'];
        $total += $r['subtotal']; $items[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Carrito</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><?php registrar_navbar(""); ?><div class="container">
<div class="d-flex justify-content-between mb-3"><h2>Mi Carrito</h2><?php if(!empty($items)): ?><a href="carrito.php?vaciar=1" class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Vaciar todo?')">Vaciar Carrito</a><?php endif; ?></div>
<?php if(empty($items)): ?><div class="alert alert-info">Carrito vacío.</div><?php else: ?>
<div class="card p-3 shadow-sm"><table class="table"><thead><tr><th>Foto</th><th>Tipo</th><th>Precio</th><th>Cant.</th><th>Subtotal</th><th>Acción</th></tr></thead><tbody>
<?php foreach($items as $it): ?><tr>
<td><?php if($it['foto']): ?><img src="uploads/<?= $it['foto'] ?>" width="50" class="img-thumbnail"><?php else: ?>Sin foto<?php endif; ?></td>
<td><strong><?= htmlspecialchars($it['tipo']) ?></strong></td><td>$<?= number_format($it['precio'],2) ?></td><td><?= $it['cantidad'] ?></td><td>$<?= number_format($it['subtotal'],2) ?></td>
<td><a href="carrito.php?eliminar_id=<?= $it['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a></td></tr><?php endforeach; ?>
<tr class="table-light"><td colspan="4" class="text-end"><strong>TOTAL:</strong></td><td colspan="2"><h4 class="text-success">$<?= number_format($total,2) ?></h4></td></tr></tbody></table></div><?php endif; ?>
</div></body></html>