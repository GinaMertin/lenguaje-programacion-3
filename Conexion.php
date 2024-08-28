<?php
set_time_limit(300); // Aumenta el tiempo máximo de ejecución a 300 segundos

$host = 'localhost';
$dbname = 'poketest';
$user = 'root'; // Cambia 'root' por tu usuario de MySQL
$pass = ''; // Cambia '' por tu contraseña de MySQL

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error en la conexión: " . $e->getMessage());
}

function obtenerDatosPokeApi($url) {
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_URL, $url);
    $response = curl_exec($curl);
    curl_close($curl);
    return json_decode($response, true);
}

$inicio = isset($_GET['inicio']) ? intval($_GET['inicio']) : 0;
$limite = 100; // Número de Pokémon a procesar por lote

$pokemones = obtenerDatosPokeApi("https://pokeapi.co/api/v2/pokemon?limit=$limite&offset=$inicio");

if ($pokemones) {
    $pokemones = $pokemones['results'];

    foreach ($pokemones as $pokemon) {
        $pokemonDetalle = obtenerDatosPokeApi($pokemon['url']);
        $id = $pokemonDetalle['id'];
        $nombre = ucfirst($pokemonDetalle['name']);

        $habilidades = array_map(function($habilidad) {
            return $habilidad['ability']['name'];
        }, $pokemonDetalle['abilities']);

        $generos = obtenerDatosPokeApi($pokemonDetalle['species']['url']);
        $genero_masculino = $generos['gender_rate'] < 8 ? 'Masculino' : 'No Disponible';
        $genero_femenino = $generos['gender_rate'] > 0 ? 'Femenino' : 'No Disponible';

        $nature_name = obtenerDatosPokeApi("https://pokeapi.co/api/v2/nature/")['results'][0]['name'];

        $stmt = $pdo->prepare("INSERT INTO pokemones (id, nombre, naturaleza) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE nombre=VALUES(nombre), naturaleza=VALUES(naturaleza)");
        $stmt->execute([$id, $nombre, $nature_name]);

        foreach ($habilidades as $habilidad) {
            $stmt = $pdo->prepare("INSERT INTO habilidades (pokemon_id, habilidad) VALUES (?, ?)");
            $stmt->execute([$id, $habilidad]);
        }

        $stmt = $pdo->prepare("INSERT INTO generos (pokemon_id, genero) VALUES (?, ?)");
        $stmt->execute([$id, $genero_masculino]);
        $stmt->execute([$id, $genero_femenino]);
    }

    echo "Lote desde $inicio procesado correctamente.";
    echo '<br><a href="insertar_pokemones.php?inicio=' . ($inicio + $limite) . '">Procesar siguiente lote</a>';
} else {
    echo "No se pudieron obtener los Pokémon.";
}
?>