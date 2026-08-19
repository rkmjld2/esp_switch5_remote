<?php
/*
 * ============================================================
 * ESP-SWITCH5 REMOTE - db.php
 * ============================================================
 * TiDB Cloud database connection
 *
 * Configuration is loaded from config.php
 * Database credentials come from Render Environment Variables.
 * ============================================================
 */

require_once __DIR__ . "/config.php";


// ------------------------------------------------------------
// Create MySQLi connection
// ------------------------------------------------------------

$conn = mysqli_init();

if (!$conn) {
    die("Database initialization failed.");
}


// ------------------------------------------------------------
// Connect to TiDB Cloud
// ------------------------------------------------------------

mysqli_ssl_set($conn, NULL, NULL, NULL, NULL, NULL);

$connected = mysqli_real_connect(
    $conn,
    $db_host,
    $db_user,
    $db_password,
    $db_name,
    (int)$db_port,
    NULL,
    MYSQLI_CLIENT_SSL
);


// ------------------------------------------------------------
// Connection error
// ------------------------------------------------------------

if (!$connected) {

    if (DEBUG_MODE) {
        die(
            "Database connection failed: " .
            mysqli_connect_error()
        );
    }

    die("Database connection failed.");
}


// ------------------------------------------------------------
// Character set
// ------------------------------------------------------------

if (!$conn->set_charset("utf8mb4")) {

    if (DEBUG_MODE) {
        die(
            "Unable to set database character set: " .
            $conn->error
        );
    }

    die("Database configuration error.");
}


// ------------------------------------------------------------
// Connection successful
// ------------------------------------------------------------

?>