<?php
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

try {
    if (!file_exists($carpetaRuta)) {
        mkdir($carpetaRuta, 0755, true);
        $mensaje = "Carpeta '$carpetaNombre' creada con éxito.";
    } else {
        $mensaje = "La carpeta '$carpetaNombre' ya existe.";
    } 

///////////////////agrega parametros para subir solo cierto tipos de archivos
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_FILES['archivo'])) {
            $archivo = $_FILES['archivo'];
    
            // Verificar el tipo de archivo
            $tiposPermitidos = ['image/jpeg', 'image/png', 'application/pdf']; // son los Tipos permitidos
            if (!in_array($archivo['type'], $tiposPermitidos)) {
                $mensaje = "Error: Solo se permiten archivos JPEG, PNG y PDF.";
            }
            //tamaño del archivo 
            elseif ($archivo['size'] > 5 * 1024 * 1024) {
                $mensaje = "Error: El archivo no puede ser mayor a 5MB.";
            }
            // entonces subir archivo
            else {
                if (move_uploaded_file($archivo['tmp_name'], $carpetaRuta . '/' . $archivo['name'])) {
                    $mensaje = "Archivo subido con éxito.";
                } else {
                    $mensaje = "Error al subir el archivo.";
                }
            }
        }
    }

    if (isset($_POST['eliminarArchivo'])) {
        $archivoAEliminar = $_POST['eliminarArchivo'];
        $archivoRutaAEliminar = $carpetaRuta . '/' . $archivoAEliminar;

        if (file_exists($archivoRutaAEliminar)) {
            if (unlink($archivoRutaAEliminar)) {
                $mensaje = "Archivo '$archivoAEliminar' eliminado con éxito.";
            } else {
                throw new Exception("Error al eliminar el archivo.");
            }
        } else {
            throw new Exception("El archivo '$archivoAEliminar' no existe.");
        }
    }
} catch (Exception $e) {
    $mensaje = "Error: " . htmlspecialchars($e->getMessage());
}

///////////////////////// Eliminar archivos antiguos (por ejemplo, mayores a 1 hora)
$archivos = scandir($carpetaRuta);
foreach ($archivos as $archivo) {
    if ($archivo !== '.' && $archivo !== '..') {
        $rutaArchivo = $carpetaRuta . '/' . $archivo;
        if (time() - filemtime($rutaArchivo) > 3600) { // 3600 segundos = 1 hora
            unlink($rutaArchivo);
        }
    }
}
?>

<?php
session_start();
$mensaje = "";
///////////////////////// INICIO DE SESION
if (!isset($_SESSION['usuario'])) {
    // Si no está autenticado, mostrar el formulario de inicio de sesión
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $usuario = $_POST['usuario'];
        $contrasena = $_POST['contrasena'];

        // Verificar credenciales (esto es un ejemplo básico)
        if ($usuario === 'admin' && $contrasena === '1234') {
            $_SESSION['usuario'] = $usuario;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            $mensajeError = "Usuario o contraseña incorrectos.";
        }
    }

    // formulario de inicio de sesión
    echo '
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Iniciar Sesión</title>
        <link rel="stylesheet" href="estilo.css">
    </head>
    <body>
        <div class="contentIS">
            <h1>Iniciar Sesión</h1>
            <form method="POST">
                <label for="usuario">Usuario:</label>
                <input type="text" name="usuario" required><br><br>
                <label for="contrasena">Contraseña:</label>
                <input type="password" name="contrasena" required><br><br>
                <button type="submit" class="Btn">Iniciar Sesión</button>
            </form>
            ' . (isset($mensajeError) ? "<p style='color:red;'>$mensajeError</p>" : "") . '
        </div>
    </body>
    </html>
    ';
    exit;
}


/////////////logica mensaje de error al no cumplir los archivos los requisitos
$carpetaNombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$carpetaRuta = "./descarga/" . $carpetaNombre;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['archivo'])) {
        $archivo = $_FILES['archivo'];

        // Verificación del tipo de archivo
        $tiposPermitidos = ['image/jpeg', 'image/png', 'application/pdf'];
        if (!in_array($archivo['type'], $tiposPermitidos)) {
            $mensaje = "Error: Solo se permiten archivos JPEG, PNG y PDF.";
        }
        // Verificación del tamaño del archivo
        elseif ($archivo['size'] > 5 * 1024 * 1024) { // 5MB
            $mensaje = "Error: El archivo no puede ser mayor a 5MB.";
        }
    }
}

////verifica si el usuario esta autenticado
if (isset($_SESSION['usuario'])) {
    // contenido principal 
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Compartir archivos</title>
    <script src="parametro.js"></script>
    <link rel="stylesheet" href="estilo.css"> 
</head>

<body>
    <h1>Compartir archivos <sup class="beta">BETA</sup></h1>
    <div class="content">
        <h3>Sube tus archivos y comparte este enlace temporal: <span>ibu.pe/?nombre=<?php echo $carpetaNombre;?></span>
        </h3>

        <?php if (!empty($mensaje)): ?>
        <p style="color: red;"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        
        <div class="container">
            <div class="drop-area" id="drop-area">
                <form action="" id="form" method="POST" enctype="multipart/form-data">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" style="fill:#0730c5;transform: ;msFilter:;">
                     <path d="M13 19v-4h3l-4-5-4 5h3v4z"></path>
                        <path d="M7 19h2v-2H7c-1.654 0-3-1.346-3-3 0-1.404 1.199-2.756 2.673-3.015l.581-.102.192-.558C8.149 8.274 9.895 7 12 7c2.757 0 5 2.243 5 5v1h1c1.103 0 2 .897 2 2s-.897 2-2 2h-3v2h3c2.206 0 4-1.794 4-4a4.01 4.01 0 0 0-3.056-3.888C18.507 7.67 15.56 5 12 5 9.244 5 6.85 6.611 5.757 9.15 3.609 9.792 2 11.82 2 14c0 2.757 2.243 5 5 5z"></path>
                   </svg> <br>
                  <input type="file" class="file-input" name="archivo" id="archivo" onchange="document.getElementById('form').submit()">
                   <label> Arrastra tus archivos aquí<br>o</label>
                    <p><b>Abre el explorador</b></p> 
             </form>
         </div>

            <div class="container2">
               

                <div id="file-list" class="pila">
                    <?php
                    $targetDir = $carpetaRuta;

                    $files = scandir($targetDir);
                    $files = array_diff($files, array('.', '..'));

                    if (count($files) > 0) {
                        echo " <h3 style='margin-bottom:10px;'>Archivos Subidos:</h3>";

                        foreach ($files as $file) {
                            echo "<div class='archivos_subidos'>
                            <div><a href='$carpetaRuta/$file' download class='boton-descargar'>$file</a></div>
                            <div>
                            <form action='' method='POST' style='display:inline;'>
                                <input type='hidden' name='eliminarArchivo' value='$file'>
                                <button type='submit' class='btn_delete'>
                                    <svg xmlns='http://www.w3.org/2000/svg' class='icon icon-tabler icon-tabler-trash' width='24' height='24' viewBox='0 0 24 24' stroke-width='2' stroke='currentColor' fill='none' stroke-linecap='round' stroke-linejoin='round'>
                                        <path stroke='none' d='M0 0h24v24H0z' fill='none'/>
                                        <path d='M4 7l16 0' />
                                        <path d='M10 11l0 6' />
                                        <path d='M14 11l0 6' />
                                        <path d='M5 7l1 12a2 2 0 0 0 2 2h8a2 2 0 0 0 2 -2l1 -12' />
                                        <path d='M9 7v-3a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v3' />
                                    </svg>
                                </button>
                            </form>
                             <!-- Botón de ojo para previsualizar -->
                            <button class='btn_preview' onclick='previewFile(\"$file\")'>
                                <svg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='currentColor' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'>
                                    <path d='M12 4.5C6.75 4.5 3 12 3 12s3.75 7.5 9 7.5 9-7.5 9-7.5-3.75-7.5-9-7.5z' />
                                    <circle cx='12' cy='12' r='3' />
                                </svg>
                            </button> 
                        </div>
                        </div>";
                        }
                    } else {
                        echo "No se han subido archivos.";
                    }
                    ?>
                </div>
            </div>
                 <!-- Nuevo recuadro de previsualización -->
        <div id="preview-area" class="preview-area">
            <h3>Previsualización:</h3>
         <div id="preview-content">
                <!-- Aquí se mostrará la previsualización del archivo -->
            </div>
        </div>


        </div>
    </div>
    
    <?php
            if (isset($_SESSION['usuario'])) {
             echo '<form method="POST" action="logout.php">
                        <button type="submit" class="Btn">Cerrar Sesión</button>
                   </form>';
            }
        ?><br><br>
    <!-- <script src="parametro.js"></script> -->

</body>
<script src="preview.js"></script>
</html>
<?php
}
