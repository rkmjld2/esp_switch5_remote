<?php
/*
============================================================
 ESP-SWITCH5 REMOTE - api.php
 REMOTE VERSION
============================================================

Server:
    esp_switch5_remote.onrender.com

Database:
    TiDB Cloud

Tables:

    controllers
        id
        controller_id
        device_token
        customer_name
        active
        last_seen

    esp_control
        id
        controller_id
        D1
        D2
        D3
        D4
        D5
        D6
        D7
        D8

============================================================
*/


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once __DIR__ . "/db.php";


/* =========================================================
   JSON RESPONSE
   ========================================================= */

header("Content-Type: application/json; charset=UTF-8");


/* =========================================================
   GET PARAMETERS
   ========================================================= */

$action = trim($_GET["action"] ?? "");

$controller_id = trim(
    $_GET["controller_id"] ?? ""
);

$device_token = trim(
    $_GET["device_token"] ?? ""
);


/* =========================================================
   VALIDATE CONTROLLER ID
   ========================================================= */

if ($controller_id === "") {

    echo json_encode([
        "status" => "error",
        "message" => "controller_id missing"
    ]);

    exit;
}


/* =========================================================
   VALIDATE DEVICE TOKEN
   ========================================================= */

if ($device_token === "") {

    echo json_encode([
        "status" => "error",
        "message" => "device_token missing"
    ]);

    exit;
}


/* =========================================================
   VERIFY CONTROLLER ID + DEVICE TOKEN
   ========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        controller_id,
        device_token,
        customer_name,
        active
    FROM controllers
    WHERE controller_id = ?
      AND device_token = ?
    LIMIT 1
");


if (!$stmt) {

    echo json_encode([
        "status" => "error",
        "message" => "Controller prepare failed"
    ]);

    exit;
}


$stmt->bind_param(
    "ss",
    $controller_id,
    $device_token
);


if (!$stmt->execute()) {

    echo json_encode([
        "status" => "error",
        "message" => "Controller query failed"
    ]);

    $stmt->close();

    exit;
}


$result = $stmt->get_result();


/* =========================================================
   CONTROLLER NOT FOUND
   ========================================================= */

if ($result->num_rows === 0) {

    echo json_encode([
        "status" => "error",
        "message" =>
            "Invalid controller_id or device_token"
    ]);

    $stmt->close();

    exit;
}


$controller = $result->fetch_assoc();

$stmt->close();


/* =========================================================
   CHECK ACTIVE
   ========================================================= */

if ((int)$controller["active"] !== 1) {

    echo json_encode([
        "status" => "error",
        "message" => "Controller is inactive"
    ]);

    exit;
}


/* =========================================================
   INDIA STANDARD TIME
   =========================================================

   Render normally uses UTC.

   We explicitly generate IST here.

   IST = UTC + 5 hours 30 minutes.

   Database field:
       controllers.last_seen
       DATETIME
========================================================= */

$utcTimestamp = time();

$indiaTimestamp =
    $utcTimestamp + (5 * 60 * 60) + (30 * 60);

$last_seen = gmdate(
    "Y-m-d H:i:s",
    $indiaTimestamp
);


/* =========================================================
   UPDATE LAST_SEEN
   =========================================================

   Only the authenticated controller is updated.
========================================================= */

$stmt = $conn->prepare("
    UPDATE controllers
    SET last_seen = ?
    WHERE controller_id = ?
      AND device_token = ?
");


if (!$stmt) {

    echo json_encode([
        "status" => "error",
        "message" => "last_seen prepare failed"
    ]);

    exit;
}


$stmt->bind_param(
    "sss",
    $last_seen,
    $controller_id,
    $device_token
);


if (!$stmt->execute()) {

    echo json_encode([
        "status" => "error",
        "message" => "Could not update last_seen"
    ]);

    $stmt->close();

    exit;
}


$stmt->close();


/* =========================================================
   ACTION = GET
   =========================================================

   ESP8266 uses this action to read D1-D8.
========================================================= */

if ($action === "get") {


    /* -----------------------------------------------------
       READ D1-D8 FOR THIS CONTROLLER
       ----------------------------------------------------- */

    $stmt = $conn->prepare("
        SELECT
            D1,
            D2,
            D3,
            D4,
            D5,
            D6,
            D7,
            D8
        FROM esp_control
        WHERE controller_id = ?
        LIMIT 1
    ");


    if (!$stmt) {

        echo json_encode([
            "status" => "error",
            "message" => "esp_control prepare failed"
        ]);

        exit;
    }


    $stmt->bind_param(
        "s",
        $controller_id
    );


    if (!$stmt->execute()) {

        echo json_encode([
            "status" => "error",
            "message" => "esp_control query failed"
        ]);

        $stmt->close();

        exit;
    }


    $result = $stmt->get_result();


    /* -----------------------------------------------------
       NO PIN RECORD
       ----------------------------------------------------- */

    if ($result->num_rows === 0) {

        echo json_encode([
            "status" => "error",
            "message" =>
                "No esp_control record found"
        ]);

        $stmt->close();

        exit;
    }


    $row = $result->fetch_assoc();

    $stmt->close();


    /* -----------------------------------------------------
       RETURN D1-D8
       ----------------------------------------------------- */

    echo json_encode([

        "status" => "ok",

        "controller_id" =>
            $controller_id,

        "D1" => (int)$row["D1"],
        "D2" => (int)$row["D2"],
        "D3" => (int)$row["D3"],
        "D4" => (int)$row["D4"],
        "D5" => (int)$row["D5"],
        "D6" => (int)$row["D6"],
        "D7" => (int)$row["D7"],
        "D8" => (int)$row["D8"],

        "last_seen" =>
            $last_seen

    ]);

    exit;
}


/* =========================================================
   ACTION = SET
   =========================================================

   Example:

   action=set
   controller_id=ESP0001
   device_token=ESP0001-TOKEN
   pin=D1
   value=1
========================================================= */

if ($action === "set") {


    $pin = strtoupper(
        trim($_GET["pin"] ?? "")
    );


    $value = isset($_GET["value"])
        ? (int)$_GET["value"]
        : -1;


    /* -----------------------------------------------------
       VALIDATE PIN
       ----------------------------------------------------- */

    if (!preg_match(
        '/^D[1-8]$/',
        $pin
    )) {

        echo json_encode([
            "status" => "error",
            "message" => "Invalid pin"
        ]);

        exit;
    }


    /* -----------------------------------------------------
       VALIDATE VALUE
       ----------------------------------------------------- */

    if (
        $value !== 0 &&
        $value !== 1
    ) {

        echo json_encode([
            "status" => "error",
            "message" => "Invalid value"
        ]);

        exit;
    }


    /* -----------------------------------------------------
       UPDATE SELECTED PIN
       -----------------------------------------------------

       $pin has already been strictly validated as D1-D8,
       so it is safe to use as a column name.
    ----------------------------------------------------- */

    $sql = "
        UPDATE esp_control
        SET `$pin` = ?
        WHERE controller_id = ?
    ";


    $stmt = $conn->prepare($sql);


    if (!$stmt) {

        echo json_encode([
            "status" => "error",
            "message" =>
                "Pin update prepare failed"
        ]);

        exit;
    }


    $stmt->bind_param(
        "is",
        $value,
        $controller_id
    );


    if (!$stmt->execute()) {

        echo json_encode([
            "status" => "error",
            "message" =>
                "Pin update failed"
        ]);

        $stmt->close();

        exit;
    }


    $stmt->close();


    /* -----------------------------------------------------
       SUCCESS
       ----------------------------------------------------- */

    echo json_encode([

        "status" => "ok",

        "controller_id" =>
            $controller_id,

        "pin" => $pin,

        "value" => $value,

        "last_seen" =>
            $last_seen

    ]);

    exit;
}


/* =========================================================
   UNKNOWN ACTION
   ========================================================= */

echo json_encode([

    "status" => "error",

    "message" =>
        "Unknown action. Use action=get or action=set"

]);

exit;

?>