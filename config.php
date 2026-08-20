<?php
/*
 * ============================================================
 * ESP-SWITCH5 REMOTE - config.php
 * ============================================================
 * Central configuration file
 *
 * Render:
 *   esp-switch5-remote.onrender.com
 *
 * Database:
 *   TiDB Cloud
 *
 * IMPORTANT:
 *   Database credentials and OWNER_PASSWORD are read
 *   from Render Environment Variables.
 *
 *   DO NOT put actual passwords in this file.
 * ============================================================
 */


// ------------------------------------------------------------
// TIMEZONE
// ------------------------------------------------------------

date_default_timezone_set("Asia/Kolkata");


// ------------------------------------------------------------
// APPLICATION
// ------------------------------------------------------------

define("APP_NAME", "ESP-SWITCH5 REMOTE");


// ------------------------------------------------------------
// DATABASE
// ------------------------------------------------------------
//
// Render Environment Variables:
//
// DB_HOST
// DB_USER
// DB_PASSWORD
// DB_NAME
// DB_PORT
//

$db_host = getenv("DB_HOST") ?: "";
$db_user = getenv("DB_USER") ?: "";
$db_password = getenv("DB_PASSWORD") ?: "";
$db_name = getenv("DB_NAME") ?: "";
$db_port = getenv("DB_PORT") ?: "4000";


// ------------------------------------------------------------
// OWNER ACCESS
// ------------------------------------------------------------
//
// Owner password is supplied by Render:
//
// OWNER_PASSWORD = EspSwitchOwner@2026
//
// DO NOT put the actual password in this file.
//

$owner_password = getenv("OWNER_PASSWORD") ?: "";


// ------------------------------------------------------------
// CONTROLLER / API SETTINGS
// ------------------------------------------------------------

// API request timeout in seconds
define("API_TIMEOUT", 10);

// ESP polling interval in seconds
define("ESP_POLL_INTERVAL", 3);


// ------------------------------------------------------------
// DEBUG
// ------------------------------------------------------------
//
// Keep false on the live Render server.
//

define("DEBUG_MODE", false);


// ------------------------------------------------------------
// BASIC DATABASE VALIDATION
// ------------------------------------------------------------

if (
    $db_host === "" ||
    $db_user === "" ||
    $db_name === ""
) {

    if (DEBUG_MODE) {

        die(
            "Database environment variables are not configured."
        );
    }
}


// ------------------------------------------------------------
// BASIC OWNER PASSWORD VALIDATION
// ------------------------------------------------------------

if ($owner_password === "") {

    if (DEBUG_MODE) {

        die(
            "OWNER_PASSWORD environment variable is not configured."
        );
    }
}

?>
