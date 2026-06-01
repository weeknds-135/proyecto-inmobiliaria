<?php
require 'conexion.php';
if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'usuario normal') { 
    http_response_code(403); 
    die("Mensaje: Acceso denegado."); 
}

$items = array(); $total = 0; $compra_exitosa = false;

// Si el usuario presiona el botón de confirmación final
if (isset($_POST['confirmar_pago'])) {
    // Aquí se procesaría la orden en la BD si fuera necesario.
    unset($_SESSION['carrito']); // Vaciamos el carrito automáticamente tras la compra
    $compra_exitosa = true;
}

// Cargar los datos del carrito actual si no se ha procesado la compra aún
if (!$compra_exitosa && !empty($_SESSION['carrito'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['carrito'])));
    $res = $conn->query("SELECT id, tipo, precio, foto FROM inmuebles WHERE id IN ($ids)");
    while ($r = $res->fetch_assoc()) {
        $r['cantidad'] = $_SESSION['carrito'][$r['id']];
        $r['subtotal'] = $r['precio'] * $r['cantidad'];
        $total += $r['subtotal'];
        $items[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Verificar Compra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<?php registrar_navbar(""); ?>
<div class="container" style="max-width: 700px;">
    <?php if ($compra_exitosa): ?>
        <div class="card p-5 shadow text-center mt-4">
            <h1 class="text-success fw-bold mb-3">¡Compra Verificada Exitosamente! 🎉</h1>
            <p class="fs-5 text-muted">Tu orden ha sido procesada de manera conforme por nuestro sistema inmobiliario.</p>
            <div class="mt-4">
                <a href="catalogo.php" class="btn btn-primary btn-lg px-4">Volver al Catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <h2 class="mb-4 fw-bold text-dark">Verificación y Resumen de Compra</h2>
        
        <?php if (empty($items)): ?>
            <div class="alert alert-warning text-center">No hay productos en el carrito para verificar. <a href="catalogo.php">Ir al catálogo</a></div>
        <?php else: ?>
            <div class="card p-4 shadow-sm mb-4 bg-white">
                <h5 class="fw-bold mb-3 border-bottom pb-2 text-secondary">Detalle de los Ítems</h5>
                <ul class="list-group list-group-flush mb-3">
                    <?php foreach ($items as $it): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                            <div>
                                <strong><?= htmlspecialchars($it['tipo']) ?></strong> 
                                <span class="text-muted small ms-2">(x<?= $it['cantidad'] ?>)</span>
                            </div>
                            <span class="fw-bold text-dark">$<?= number_format($it['subtotal'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                
                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                    <h4 class="fw-bold">Total a Pagar:</h4>
                    <h3 class="text-success fw-bold">$<?= number_format($total, 2) ?></h3>
                </div>
            </div>
            
            <form method="POST">
                <div class="card p-3 shadow-sm bg-dark text-white mb-4">
                    <p class="small mb-0">⚠️ Al presionar el botón inferior, declaras la conformidad de los montos y cierras la transacción del catálogo.</p>
                </div>
                <div class="d-flex gap-3">
                    <a href="carrito.php" class="btn btn-outline-secondary w-50 btn-lg">Regresar al Carrito</a>
                    <button type="submit" name="confirmar_pago" class="btn btn-success w-50 btn-lg fw-bold shadow" onclick="return confirm('¿Deseas verificar y finalizar esta compra ahora mismo?')">✅ Confirmar y Finalizar Compra</button>
                </div>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
</body>
</html>