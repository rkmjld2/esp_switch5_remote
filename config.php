<?php
/*
 * ============================================================
 * ESP-SWITCH5 REMOTE - config.php
 * ============================================================
 *
 * Remote:
 *   esp-switch5-remote.onrender.com
 *
 * Database:
 *   TiDB Cloud
 *
 * Passwords:
 *
 *   ADMIN_PASSWORD
 *       Used by index.php
 *
 *   TOKEN_PASSWORD
 *       Used by owner_token.php
 *
 * IMPORTANT:
 * Passwords and database credentials are stored
 * in Render Environment Variables.
 *
 * Do NOT put actual passwords in this file.
 * ============================================================
 */


/* =========================================================
   TIMEZONE
========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   APPLICATION
========================================================= */

define(
    "APP_NAME",
    "ESP-SWITCH5 REMOTE"
);


/* =========================================================
   DATABASE
========================================================= */

$db_host =
    getenv("DB_HOST") ?: "";

$db_user =
    getenv("DB_USER") ?: "";

$db_password =
    getenv("DB_PASSWORD") ?: "";

$db_name =
    getenv("DB_NAME") ?: "";

$db_port =
    getenv("DB_PORT") ?: "4000";


/* =========================================================
   ADMIN PASSWORD
========================================================= */

/*
 * Used by:
 *
 *     index.php
 *
 * Render Environment Variable:
 *
 *     ADMIN_PASSWORD
 */

$admin_password =
    getenv("ADMIN_PASSWORD") ?: "";


/* =========================================================
   TOKEN PASSWORD
========================================================= */

/*
 * Used by:
 *
 *     owner_token.php
 *
 * Render Environment Variable:
 *
 *     TOKEN_PASSWORD
 */

$token_password =
    getenv("TOKEN_PASSWORD") ?: "";


/* =========================================================
   CONTROLLER / API SETTINGS
========================================================= */

define(
    "API_TIMEOUT",
    10
);

define(
    "ESP_POLL_INTERVAL",
    3
);


/* =========================================================
   DEBUG
========================================================= */

define(
    "DEBUG_MODE",
    false
);


/* =========================================================
   BASIC DATABASE VALIDATION
========================================================= */

if (
    $db_host === "" ||
    $db_user === "" ||
    $db_name === ""
) {

    if (DEBUG_MODE) {

        die(
            "Database environment variables " .
            "are not configured."
        );
    }
}

?>
