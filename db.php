<?php
/*
 * =========================================================
 * ESP-SWITCH5 REMOTE
 * db.php
 * =========================================================
 *
 * Database:
 *     esp_switch5
 *
 * TiDB Cloud
 *
 * Render Environment Variables:
 *
 *     DB_HOST
 *     DB_USER
 *     DB_PASSWORD
 *     DB_NAME
 *     DB_PORT
 *
 * IMPORTANT:
 *     Do not put the real database password in this file.
 * =========================================================
 */

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);


/* =========================================================
   READ RENDER ENVIRONMENT VARIABLES
========================================================= */

$host =
    getenv("DB_HOST");

$user =
    getenv("DB_USER");

$password =
    getenv("DB_PASSWORD");

$database =
    getenv("DB_NAME");

$port =
    getenv("DB_PORT");


/* =========================================================
   DEFAULT PORT
========================================================= */

if (
    !$port
)
{
    $port = 4000;
}


/* =========================================================
   CHECK REQUIRED VARIABLES
========================================================= */

$missing = [];


if (!$host)
{
    $missing[] = "DB_HOST";
}


if (!$user)
{
    $missing[] = "DB_USER";
}


if (!$password)
{
    $missing[] = "DB_PASSWORD";
}


if (!$database)
{
    $missing[] = "DB_NAME";
}


if (
    count($missing) > 0
)
{
    die(
        "Database environment variables missing: " .
        htmlspecialchars(
            implode(", ", $missing)
        )
    );
}


/* =========================================================
   CONNECT TO TiDB CLOUD
========================================================= */

try
{
    $conn =
        mysqli_init();


    /*
     * TiDB Cloud HTTPS/SSL connection.
     */

    mysqli_ssl_set(
        $conn,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL
    );


    mysqli_real_connect(
        $conn,
        $host,
        $user,
        $password,
        $database,
        (int)$port,
        NULL,
        MYSQLI_CLIENT_SSL
    );


    $conn->set_charset(
        "utf8mb4"
    );
}
catch (
    mysqli_sql_exception $e
)
{
    die(
        "Database connection failed: " .
        htmlspecialchars(
            $e->getMessage()
        )
    );
}

?>
