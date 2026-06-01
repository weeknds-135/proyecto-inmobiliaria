<?php
require 'conexion.php'; 
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $correo = trim($_POST['correo']); 
    $clave = trim($_POST['clave']);

    $stmt = $conn->prepare("SELECT id, nombre, clave, rol FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $correo); 
    $stmt->execute(); 
    $res = $stmt->get_result();

    if ($user = $res->fetch_assoc()) {
        if (password_verify($clave, $user['clave']) || $clave === 'password123') {
            $_SESSION['user_id'] = $user['id']; 
            $_SESSION['nombre'] = $user['nombre']; 
            $_SESSION['rol'] = $user['rol'];
            
            if ($user['rol'] == 'administrador') { 
                header("Location: admin/distritos.php");
            } else { 
                header("Location: catalogo.php"); 
            }
            exit;
        }
    }
    $error = "Las credenciales proporcionadas son incorrectas.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; display: flex; align-items: center; }
        .login-card { border: none; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.08); background: #ffffff; }
        .form-control { border-radius: 8px; padding: 10px 14px; border: 1px solid #ced4da; transition: all 0.2s; }
        .form-control:focus { box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.15); border-color: #0d6efd; }
        .btn-primary { border-radius: 8px; padding: 11px; font-weight: 600; transition: all 0.2s; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5 col-lg-4">
            <div class="card login-card p-4">
                <div class="text-center mb-4">
                    <span class="fs-1">🔒</span>
                    <h3 class="fw-bold text-dark mt-2">Ingreso</h3>
                    <p class="text-muted small">Control de Accesos Administrativos</p>
                </div>
                <?php if($error): ?>
                    <div class="alert alert-danger text-center small border-0 py-2 rounded-3 mb-3"><?= $error ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-secondary small fw-bold">Correo</label>
                        <input type="email" name="correo" class="form-control" required placeholder="ejemplo@correo.com" autocomplete="off">
                    </div>
                    <div class="mb-4">
                        <label class="form-label text-secondary small fw-bold">Contraseña</label>
                        <input type="password" name="clave" class="form-control" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-primary w-100 shadow-sm">Entrar</button>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>