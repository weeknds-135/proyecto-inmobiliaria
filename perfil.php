<?php
require 'conexion.php'; if (!isset($_SESSION['user_id'])) { header("Location: login.php"); exit; }
$user_id = $_SESSION['user_id']; $success = ''; $error = '';
if (isset($_POST['act_nombre'])) {
    $n = trim($_POST['nombre']);
    if (empty($n)) { $error = "No puede estar vacío."; } else {
        $stmt = $conn->prepare("UPDATE usuarios SET nombre = ? WHERE id = ?"); $stmt->bind_param("si", $n, $user_id); $stmt->execute();
        $_SESSION['nombre'] = $n; $success = "Nombre actualizado.";
    }
}
if (isset($_POST['act_clave'])) {
    $act = $_POST['clave_actual']; $nva = $_POST['nueva_clave']; $conf = $_POST['confirmar_clave'];
    $stmt = $conn->prepare("SELECT clave FROM usuarios WHERE id = ?"); $stmt->bind_param("i", $user_id); $stmt->execute(); $u = $stmt->get_result()->fetch_assoc();
    if (!password_verify($act, $u['clave'])) { $error = "Contraseña actual incorrecta."; } 
    elseif (strlen($nva) < 8) { $error = "Mínimo 8 caracteres."; } 
    elseif ($nva !== $conf) { $error = "No coinciden."; } else {
        $hash = password_hash($nva, PASSWORD_BCRYPT);
        $stmt = $conn->prepare("UPDATE usuarios SET clave = ? WHERE id = ?"); $stmt->bind_param("si", $hash, $user_id); $stmt->execute();
        $success = "Contraseña cambiada.";
    }
}
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Perfil</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light"><?php registrar_navbar(""); ?><div class="container" style="max-width: 600px;"><h2>Mi Perfil</h2>
<?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?><?php if($error): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div class="card p-3 mb-3"><form method="POST"><div class="mb-3"><label>Nombre</label><input type="text" name="nombre" class="form-control" value="<?= htmlspecialchars($_SESSION['nombre']) ?>" required></div><button type="submit" name="act_nombre" class="btn btn-primary">Guardar</button></form></div>
<div class="card p-3"><form method="POST"><div class="mb-2"><label>Clave Actual</label><input type="password" name="clave_actual" class="form-control" required></div><div class="mb-2"><label>Nueva Clave</label><input type="password" name="nueva_clave" class="form-control" required></div><div class="mb-2"><label>Confirmar Clave</label><input type="password" name="confirmar_clave" class="form-control" required></div><button type="submit" name="act_clave" class="btn btn-danger">Cambiar Clave</button></form></div></div></body></html>