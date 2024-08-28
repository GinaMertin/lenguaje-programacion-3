<?php
include 'db.php';

$limit = 50; // Puedes cambiar este número según la cantidad de Pokémon que desees obtener

try {
    for ($id = 1; $id <= $limit; $id++) {
        $response = file_get_contents("https://pokeapi.co/api/v2/pokemon/$id");
        $pokemonData = json_decode($response, true);

        $nombre = $pokemonData['name'];
        $naturaleza = ''; // Asumimos que necesitas otro API o lógica para obtener la naturaleza

        // Guardar en la base de datos
        $stmt = $pdo->prepare("INSERT INTO pokemones (id, nombre, naturaleza) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE nombre = VALUES(nombre), naturaleza = VALUES(naturaleza)");
        $stmt->execute([$id, $nombre, $naturaleza]);
    }

    echo json_encode(['success' => true, 'message' => 'Pokémon obtenidos y guardados exitosamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener los Pokémon: ' . $e->getMessage()]);
}
?>