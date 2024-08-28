<?php
// Habilitar la visualización de errores para depuración (puedes quitar esto en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';

// Variables para mensajes de éxito/error
$mensaje = '';
$error = '';

// Crear Pokémon manualmente
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'crear') {
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];

    try {
        $stmt = $pdo->prepare("INSERT INTO pokemones (nombre, naturaleza) VALUES (?, ?)");
        $stmt->execute([$nombre, $naturaleza]);
        $mensaje = 'Pokémon creado exitosamente.';
    } catch (PDOException $e) {
        $error = 'Error al crear el Pokémon: ' . $e->getMessage();
    }
}

// Actualizar Pokémon
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $naturaleza = $_POST['naturaleza'];

    try {
        $stmt = $pdo->prepare("UPDATE pokemones SET nombre = ?, naturaleza = ? WHERE id = ?");
        $stmt->execute([$nombre, $naturaleza, $id]);
        $mensaje = 'Pokémon actualizado exitosamente.';
    } catch (PDOException $e) {
        $error = 'Error al actualizar el Pokémon: ' . $e->getMessage();
    }
}

// Eliminar Pokémon
if (isset($_GET['accion']) && $_GET['accion'] == 'eliminar') {
    $id = $_GET['id'];

    try {
        $stmt = $pdo->prepare("DELETE FROM pokemones WHERE id = ?");
        $stmt->execute([$id]);
        $mensaje = 'Pokémon eliminado exitosamente.';
    } catch (PDOException $e) {
        $error = 'Error al eliminar el Pokémon: ' . $e->getMessage();
    }
}

// Obtener todos los Pokémon de la base de datos sin filtrar
$stmt = $pdo->query("SELECT * FROM pokemones");
$pokemones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pokémon</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function obtenerPokemones() {
            if (confirm('¿Estás seguro de que quieres obtener todos los Pokémon de PokeAPI? Esto puede tardar un momento.')) {
                fetch('obtener_pokemones.php')
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        if (data.success) {
                            location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Ocurrió un error al obtener los Pokémon.');
                    });
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>Gestión de Pokémon</h1>
        
        <!-- Botón para obtener Pokémon -->
        <button id="obtener-pokemones" onclick="obtenerPokemones()">Obtener todos los Pokémon</button>

        <!-- Mensajes -->
        <?php if ($mensaje): ?>
            <p class="message"><?= htmlspecialchars($mensaje) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="error"><?= htmlspecialchars($error) ?></p>
        <?php endif; ?>

        <!-- Listado de Pokémon -->
        <div id="pokemon-list">
            <?php if (!empty($pokemones)): ?>
                <?php foreach ($pokemones as $pokemon): ?>
                    <div class="pokemon">
                        <img src="https://raw.githubusercontent.com/PokeAPI/sprites/master/sprites/pokemon/<?= htmlspecialchars($pokemon['id']) ?>.png" alt="<?= htmlspecialchars($pokemon['nombre']) ?>">
                        <div>
                            <h2><?= htmlspecialchars(ucfirst($pokemon['nombre'])) ?></h2>
                            <p>ID: <?= htmlspecialchars($pokemon['id']) ?></p>
                            <p>Naturaleza: <?= htmlspecialchars($pokemon['naturaleza']) ?></p>
                        </div>
                        <div class="actions">
                            <a href="index.php?accion=editar&id=<?= htmlspecialchars($pokemon['id']) ?>">Editar</a> | 
                            <a href="index.php?accion=eliminar&id=<?= htmlspecialchars($pokemon['id']) ?>" onclick="return confirm('¿Estás seguro de que quieres eliminar este Pokémon?');">Eliminar</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay Pokémon en la base de datos.</p>
            <?php endif; ?>
        </div>

        <!-- Formulario de Crear/Editar Pokémon -->
        <?php
        $esEditar = isset($_GET['accion']) && $_GET['accion'] == 'editar';
        if ($esEditar) {
            $id = $_GET['id'];
            $stmt = $pdo->prepare("SELECT * FROM pokemones WHERE id = ?");
            $stmt->execute([$id]);
            $pokemon = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($pokemon) {
                $nombre = $pokemon['nombre'];
                $naturaleza = $pokemon['naturaleza'];
            } else {
                $error = 'Pokémon no encontrado.';
                $nombre = '';
                $naturaleza = '';
            }
        } else {
            $nombre = '';
            $naturaleza = '';
        }
        ?>
        <form action="index.php" method="post">
            <input type="hidden" name="accion" value="<?= $esEditar ? 'editar' : 'crear' ?>">
            <?php if ($esEditar && isset($pokemon)): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($pokemon['id']) ?>">
            <?php endif; ?>
            <label for="nombre">Nombre:</label>
            <input type="text" name="nombre" id="nombre" value="<?= htmlspecialchars($nombre) ?>" required>
            <label for="naturaleza">Naturaleza:</label>
            <input type="text" name="naturaleza" id="naturaleza" value="<?= htmlspecialchars($naturaleza) ?>" required>
            <input type="submit" value="<?= $esEditar ? 'Actualizar Pokémon' : 'Crear Pokémon' ?>">
        </form>
    </div>
</body>
</html>