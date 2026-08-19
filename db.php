<?php

mysqli_report(
    MYSQLI_REPORT_ERROR |
    MYSQLI_REPORT_STRICT
);

$db_host =
    getenv("DB_HOST");

$db_user =
    getenv("DB_USER");

$db_password =
    getenv("DB_PASSWORD");

$db_name =
    getenv("DB_NAME");

$db_port =
    getenv("DB_PORT");


if (!$db_port) {
    $db_port = 4000;
}


if (
    !$db_host ||
    !$db_user ||
    !$db_password ||
    !$db_name
) {
    die(
        "Database environment variables are missing."
    );
}


try {

    $conn =
        mysqli_init();


    mysqli_ssl_set(
        $conn,
        NULL,
        NULL,
        NULL,
        NULL,
        NULL
    );


    $connected =
        mysqli_real_connect(
            $conn,
            $db_host,
            $db_user,
            $db_password,
            $db_name,
            (int)$db_port,
            NULL,
            MYSQLI_CLIENT_SSL
        );


    if (!$connected) {

        die(
            "Database connection failed."
        );
    }


    $conn->set_charset(
        "utf8mb4"
    );

}
catch (
    mysqli_sql_exception $e
) {

    die(
        "Database connection failed: " .
        htmlspecialchars(
            $e->getMessage()
        )
    );
}

?>
