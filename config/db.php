<?php
// Détection de l'environnement (Render vs Local)
$host = getenv('DB_HOST') ? getenv('DB_HOST') : 'localhost';
$dbname = getenv('DB_NAME') ? getenv('DB_NAME') : 'lms_db';
$user = getenv('DB_USER') ? getenv('DB_USER') : 'root';
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : ''; 
$port = getenv('DB_PORT') ? getenv('DB_PORT') : '3306';

try {
    // Si nous sommes en local sur XAMPP Ubuntu, on spécifie le socket Unix exact de XAMPP
    if ($host === 'localhost' && !getenv('DB_HOST')) {
        $dsn = "mysql:dbname=$dbname;unix_socket=/opt/lampp/var/mysql/mysql.sock;charset=utf8";
    } else {
        // Configuration standard pour le déploiement Cloud (Render)
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8";
    }

    $pdo = new PDO($dsn, $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Erreur critique de connexion : " . $e->getMessage());
}
?>