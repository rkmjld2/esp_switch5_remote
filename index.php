<?php
/*
============================================================
 ESP-SWITCH5 REMOTE - index.php
 REMOTE CONTROL PANEL
============================================================

Remote server:
    esp-switch5-remote.onrender.com

Database:
    TiDB Cloud

Database:
    esp_switch5

Tables:
    controllers
    esp_control

Timezone:
    Asia/Kolkata

============================================================
*/


/* =========================================================
   TIMEZONE
========================================================= */

date_default_timezone_set("Asia/Kolkata");


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/db.php";

session_start();


/* =========================================================
   ADMIN PASSWORD
========================================================= */

$admin_password = getenv("ADMIN_PASSWORD") ?: "";


/* =========================================================
   LOGOUT
========================================================= */

if (isset($_GET["logout"])) {

    $_SESSION = [];

    session_destroy();

    header("Location: index.php");

    exit;
}


/* =========================================================
   LOGIN
========================================================= */

$login_error = "";

if (isset($_POST["login"])) {

    $password = $_POST["password"] ?? "";

    if (
        $admin_password !== "" &&
        hash_equals($admin_password, $password)
    ) {

        $_SESSION["esp_admin"] = true;

        header("Location: index.php");

        exit;

    } else {

        $login_error = "Invalid password.";
    }
}


/* =========================================================
   LOGIN PAGE
========================================================= */

if (
    !isset($_SESSION["esp_admin"]) ||
    $_SESSION["esp_admin"] !== true
) {
?>
<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>ESP-SWITCH5 REMOTE - Login</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    padding: 20px;
    font-family: Arial, Helvetica, sans-serif;
    background: #f2f2f2;
}

.login-box {
    max-width: 420px;
    margin: 80px auto;
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.15);
    text-align: center;
}

h1 {
    margin-top: 0;
    color: #333;
}

input[type="password"] {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 1px solid #aaa;
    border-radius: 6px;
    margin: 15px 0;
}

button {
    width: 100%;
    padding: 12px;
    border: none;
    border-radius: 6px;
    background: #007bff;
    color: white;
    font-size: 16px;
    cursor: pointer;
}

button:hover {
    opacity: 0.85;
}

.error {
    color: #dc3545;
    margin-bottom: 10px;
    font-weight: bold;
}

.small {
    margin-top: 15px;
    color: #777;
    font-size: 13px;
}

</style>

</head>

<body>

<div class="login-box">

<h1>ESP-SWITCH5 REMOTE</h1>

<p>Administrator Login</p>

<?php

if ($login_error !== "") {

    echo '<div class="error">' .
         htmlspecialchars($login_error, ENT_QUOTES, 'UTF-8') .
         '</div>';
}

?>

<form method="post">

<input
    type="password"
    name="password"
    placeholder="Enter administrator password"
    required
    autofocus
>

<button
    type="submit"
    name="login"
>
LOGIN
</button>

</form>

<div class="small">
Remote ESP8266 Control System
</div>

</div>

</body>

</html>

<?php

exit;

}


/* =========================================================
   CONTROLLER SELECTION
========================================================= */

$selected_controller =
    trim($_GET["controller_id"] ?? "");


/* =========================================================
   MESSAGE
========================================================= */

$message = "";

$message_type = "";


/* =========================================================
   SAVE START TIME
========================================================= */

if (isset($_POST["save_start"])) {

    $controller_id =
        trim($_POST["controller_id"] ?? "");

    $start_time =
        trim($_POST["start_time"] ?? "");


    if ($controller_id === "") {

        $message = "Controller ID missing.";
        $message_type = "error";

    }
    elseif ($start_time === "") {

        $message = "Start date and time missing.";
        $message_type = "error";

    }
    else {

        /*
         * HTML datetime-local gives:
         *
         * YYYY-MM-DDTHH:MM
         *
         * Convert to:
         *
         * YYYY-MM-DD HH:MM:00
         */

        $start_datetime =
            str_replace("T", " ", $start_time);

        if (strlen($start_datetime) === 16) {
            $start_datetime .= ":00";
        }


        $stmt = $conn->prepare("
            UPDATE controllers
            SET start_time = ?
            WHERE controller_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $start_datetime,
                $controller_id
            );

            if ($stmt->execute()) {

                $message =
                    "Start time saved successfully.";

                $message_type =
                    "success";

            } else {

                $message =
                    "Could not save start time.";

                $message_type =
                    "error";
            }

            $stmt->close();

        } else {

            $message =
                "Start time preparation failed.";

            $message_type =
                "error";
        }
    }

    $selected_controller = $controller_id;
}


/* =========================================================
   SAVE END TIME
========================================================= */

if (isset($_POST["save_end"])) {

    $controller_id =
        trim($_POST["controller_id"] ?? "");

    $end_time =
        trim($_POST["end_time"] ?? "");


    if ($controller_id === "") {

        $message = "Controller ID missing.";
        $message_type = "error";

    }
    elseif ($end_time === "") {

        $message = "End date and time missing.";
        $message_type = "error";

    }
    else {

        $end_datetime =
            str_replace("T", " ", $end_time);

        if (strlen($end_datetime) === 16) {
            $end_datetime .= ":00";
        }


        $stmt = $conn->prepare("
            UPDATE controllers
            SET end_time = ?
            WHERE controller_id = ?
        ");

        if ($stmt) {

            $stmt->bind_param(
                "ss",
                $end_datetime,
                $controller_id
            );

            if ($stmt->execute()) {

                $message =
                    "End time saved successfully.";

                $message_type =
                    "success";

            } else {

                $message =
                    "Could not save end time.";

                $message_type =
                    "error";
            }

            $stmt->close();

        } else {

            $message =
                "End time preparation failed.";

            $message_type =
                "error";
        }
    }

    $selected_controller = $controller_id;
}


/* =========================================================
   SET PIN
========================================================= */

if (isset($_POST["set_pin"])) {

    $controller_id =
        trim($_POST["controller_id"] ?? "");

    $pin =
        strtoupper(
            trim($_POST["pin"] ?? "")
        );

    $value =
        isset($_POST["value"])
            ? (int)$_POST["value"]
            : -1;


    if ($controller_id === "") {

        $message = "Controller ID missing.";
        $message_type = "error";

    }
    elseif (!preg_match('/^D[1-8]$/', $pin)) {

        $message = "Invalid pin.";
        $message_type = "error";

    }
    elseif ($value !== 0 && $value !== 1) {

        $message = "Invalid value.";
        $message_type = "error";

    }
    else {

        $stmt = $conn->prepare("
            SELECT active
            FROM controllers
            WHERE controller_id = ?
            LIMIT 1
        ");

        if ($stmt) {

            $stmt->bind_param(
                "s",
                $controller_id
            );

            $stmt->execute();

            $result =
                $stmt->get_result();

            if ($result->num_rows === 0) {

                $message =
                    "Controller not found.";

                $message_type =
                    "error";

            } else {

                $controller =
                    $result->fetch_assoc();

                if ((int)$controller["active"] !== 1) {

                    $message =
                        "Controller is inactive.";

                    $message_type =
                        "error";

                } else {

                    /*
                     * PIN has already been validated
                     * as D1-D8.
                     */

                    $sql = "
                        UPDATE esp_control
                        SET `$pin` = ?
                        WHERE controller_id = ?
                    ";

                    $update =
                        $conn->prepare($sql);

                    if (!$update) {

                        $message =
                            "Pin update preparation failed.";

                        $message_type =
                            "error";

                    } else {

                        $update->bind_param(
                            "is",
                            $value,
                            $controller_id
                        );

                        if ($update->execute()) {

                            $message =
                                $pin .
                                " changed to " .
                                ($value ? "ON" : "OFF");

                            $message_type =
                                "success";

                        } else {

                            $message =
                                "Pin update failed.";

                            $message_type =
                                "error";
                        }

                        $update->close();
                    }
                }
            }

            $stmt->close();

        } else {

            $message =
                "Controller query failed.";

            $message_type =
                "error";
        }
    }

    $selected_controller = $controller_id;
}


/* =========================================================
   READ ACTIVE CONTROLLERS
========================================================= */

$controllers = [];

$result = $conn->query("
    SELECT
        controller_id,
        customer_name,
        active
    FROM controllers
    WHERE active = 1
    ORDER BY controller_id
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $controllers[] = $row;
    }
}


/* =========================================================
   CONTROLLER INFORMATION
========================================================= */

$selected_customer = "";

$selected_active = 0;

$selected_last_seen = "";

$selected_start_time = "";

$selected_end_time = "";


if ($selected_controller !== "") {

    $stmt = $conn->prepare("
        SELECT
            controller_id,
            customer_name,
            active,
            last_seen,
            start_time,
            end_time
        FROM controllers
        WHERE controller_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param(
            "s",
            $selected_controller
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        if ($result->num_rows > 0) {

            $row =
                $result->fetch_assoc();


            $selected_customer =
                $row["customer_name"] ?? "";

            $selected_active =
                (int)($row["active"] ?? 0);

            /*
             * IMPORTANT:
             * NULL last_seen is converted to
             * "Not yet seen".
             */

            if (
                isset($row["last_seen"]) &&
                $row["last_seen"] !== null &&
                $row["last_seen"] !== ""
            ) {

                $selected_last_seen =
                    $row["last_seen"];

            } else {

                $selected_last_seen =
                    "Not yet seen";
            }


            /*
             * Start and End time may be NULL.
             */

            $selected_start_time =
                $row["start_time"] ?? "";

            $selected_end_time =
                $row["end_time"] ?? "";
        }

        $stmt->close();
    }
}


/* =========================================================
   FORMAT DATETIME FOR HTML datetime-local
========================================================= */

$start_input_value = "";

$end_input_value = "";


if (
    $selected_start_time !== "" &&
    $selected_start_time !== null
) {

    $timestamp =
        strtotime($selected_start_time);

    if ($timestamp !== false) {

        $start_input_value =
            date("Y-m-d\TH:i", $timestamp);
    }
}


if (
    $selected_end_time !== "" &&
    $selected_end_time !== null
) {

    $timestamp =
        strtotime($selected_end_time);

    if ($timestamp !== false) {

        $end_input_value =
            date("Y-m-d\TH:i", $timestamp);
    }
}


/* =========================================================
   READ D1-D8
========================================================= */

$pin_values = [

    "D1" => 0,
    "D2" => 0,
    "D3" => 0,
    "D4" => 0,
    "D5" => 0,
    "D6" => 0,
    "D7" => 0,
    "D8" => 0
];


if ($selected_controller !== "") {

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

    if ($stmt) {

        $stmt->bind_param(
            "s",
            $selected_controller
        );

        $stmt->execute();

        $result =
            $stmt->get_result();

        if ($result->num_rows > 0) {

            $row =
                $result->fetch_assoc();

            for (
                $i = 1;
                $i <= 8;
                $i++
            ) {

                $pin =
                    "D" . $i;

                $pin_values[$pin] =
                    (int)($row[$pin] ?? 0);
            }
        }

        $stmt->close();
    }
}

?>
<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>ESP-SWITCH5 REMOTE</title>


<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    padding: 20px;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f2f2f2;

    color: #222;
}

.container {

    max-width: 950px;

    margin: auto;

    background: white;

    padding: 25px;

    border-radius: 12px;

    box-shadow:
        0 3px 15px
        rgba(0,0,0,0.15);
}

.header {

    position: relative;

    text-align: center;

    margin-bottom: 25px;
}

h1 {

    margin: 0 0 5px 0;

    color: #333;
}

.subtitle {

    color: #666;
}

.logout {

    position: absolute;

    right: 0;

    top: 0;

    text-decoration: none;

    background: #6c757d;

    color: white;

    padding: 8px 12px;

    border-radius: 5px;

    font-size: 13px;
}

.logout:hover {

    opacity: 0.85;
}


/* =========================================================
   CONTROLLER SELECTION
========================================================= */

.controller-box {

    background: #f7f7f7;

    border: 1px solid #ddd;

    border-radius: 10px;

    padding: 20px;

    margin-bottom: 20px;
}

.controller-box label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;
}

.controller-box select {

    width: 100%;

    padding: 12px;

    font-size: 16px;

    border: 1px solid #aaa;

    border-radius: 6px;
}


/* =========================================================
   CALENDAR TIME CONTROL
========================================================= */

.time-control {

    background: #eef6ff;

    border: 1px solid #b8d8f5;

    border-radius: 10px;

    padding: 20px;

    margin-bottom: 25px;

    text-align: center;
}

.time-control h2 {

    margin-top: 0;

    margin-bottom: 5px;

    color: #333;
}

.timezone {

    color: #555;

    font-size: 14px;

    margin-bottom: 20px;
}

.time-row {

    display: grid;

    grid-template-columns:
        repeat(
            2,
            1fr
        );

    gap: 20px;

    margin-bottom: 15px;
}

.time-box {

    background: white;

    border: 1px solid #ccc;

    border-radius: 8px;

    padding: 15px;
}

.time-box label {

    display: block;

    font-weight: bold;

    margin-bottom: 8px;
}

.time-box input {

    width: 100%;

    padding: 10px;

    border: 1px solid #aaa;

    border-radius: 6px;

    font-size: 15px;
}

.save-button {

    margin-top: 10px;

    width: 100%;

    background: #007bff;

    color: white;

    border: none;

    border-radius: 6px;

    padding: 10px;

    font-size: 15px;

    cursor: pointer;
}

.save-button:hover {

    opacity: 0.85;
}


/* =========================================================
   CURRENT TIME
========================================================= */

.current-time-box {

    margin-top: 18px;

    background: #fff;

    border: 2px solid #28a745;

    border-radius: 8px;

    padding: 15px;
}

.current-time-title {

    font-size: 14px;

    color: #555;

    margin-bottom: 5px;
}

.current-time {

    font-size: 22px;

    font-weight: bold;

    color: #155724;
}


/* =========================================================
   INFORMATION
========================================================= */

.info {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(170px, 1fr)
        );

    gap: 12px;

    margin-bottom: 25px;
}

.info-card {

    background: #fafafa;

    border: 1px solid #ddd;

    border-radius: 8px;

    padding: 12px;

    text-align: center;
}

.info-title {

    font-size: 13px;

    color: #666;

    margin-bottom: 5px;
}

.info-value {

    font-weight: bold;

    font-size: 16px;
}


/* =========================================================
   D1-D8 GRID
========================================================= */

.pin-grid {

    display: grid;

    grid-template-columns:
        repeat(
            auto-fit,
            minmax(180px, 1fr)
        );

    gap: 15px;
}

.pin-card {

    border: 1px solid #ccc;

    border-radius: 10px;

    padding: 18px;

    text-align: center;

    background: #fafafa;
}

.pin-name {

    font-size: 20px;

    font-weight: bold;

    margin-bottom: 10px;
}

.state {

    font-size: 18px;

    font-weight: bold;

    margin-bottom: 12px;
}

.state-on {

    color: green;
}

.state-off {

    color: red;
}

.pin-form {

    display: inline-block;

    margin: 0;
}

button {

    border: none;

    border-radius: 6px;

    padding: 10px 16px;

    margin: 4px;

    font-size: 15px;

    cursor: pointer;
}

.on-btn {

    background: #28a745;

    color: white;
}

.off-btn {

    background: #dc3545;

    color: white;
}

button:hover {

    opacity: 0.85;
}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    text-align: center;

    margin: 20px 0;

    padding: 10px;

    border-radius: 6px;

    font-weight: bold;
}

.success {

    color: #155724;

    background: #d4edda;
}

.error {

    color: #721c24;

    background: #f8d7da;
}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 600px) {

    body {
        padding: 10px;
    }

    .container {
        padding: 15px;
    }

    .logout {

        position: static;

        display: inline-block;

        margin-top: 10px;
    }

    .pin-grid {

        grid-template-columns:
            1fr 1fr;
    }

    .time-row {

        grid-template-columns:
            1fr;
    }
}

</style>

</head>


<body>


<div class="container">


<div class="header">

<h1>
ESP-SWITCH5 REMOTE
</h1>

<div class="subtitle">
Remote ESP8266 Control Panel
</div>

<a
    class="logout"
    href="index.php?logout=1"
>
Logout
</a>

</div>


<!-- ======================================================
     CONTROLLER SELECTION
======================================================= -->

<div class="controller-box">

<label for="controller">
Select Controller
</label>

<select
    id="controller"
    onchange="selectController(this.value)"
>

<option value="">
-- Select Controller --
</option>

<?php

foreach ($controllers as $controller) {

?>

<option
    value="<?php
        echo htmlspecialchars(
            $controller["controller_id"],
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
    <?php

    if (
        $selected_controller ===
        $controller["controller_id"]
    ) {

        echo "selected";
    }

    ?>
>

<?php

echo htmlspecialchars(
    $controller["controller_id"],
    ENT_QUOTES,
    "UTF-8"
);

if (
    !empty($controller["customer_name"])
) {

    echo " - ";

    echo htmlspecialchars(
        $controller["customer_name"],
        ENT_QUOTES,
        "UTF-8"
    );
}

?>

</option>

<?php

}

?>

</select>

</div>


<?php

if ($message !== "") {

?>

<div
    class="message
    <?php
        echo $message_type === "success"
            ? "success"
            : "error";
    ?>"
>

<?php

echo htmlspecialchars(
    $message,
    ENT_QUOTES,
    "UTF-8"
);

?>

</div>

<?php

}


if ($selected_controller !== "") {

?>


<!-- ======================================================
     CALENDAR TIME CONTROL
======================================================= -->

<div class="time-control">

<h2>
Calendar Time Control
</h2>

<div class="timezone">
Calendar: Asia/Kolkata (India Standard Time)
</div>


<div class="time-row">


<!-- START TIME -->

<div class="time-box">

<form method="post">

<input
    type="hidden"
    name="controller_id"
    value="<?php
        echo htmlspecialchars(
            $selected_controller,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
>

<label for="start_time">
START TIME
</label>

<input
    type="datetime-local"
    id="start_time"
    name="start_time"
    value="<?php
        echo htmlspecialchars(
            $start_input_value,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
    required
>

<button
    type="submit"
    name="save_start"
    class="save-button"
>
SAVE START
</button>

</form>

</div>


<!-- END TIME -->

<div class="time-box">

<form method="post">

<input
    type="hidden"
    name="controller_id"
    value="<?php
        echo htmlspecialchars(
            $selected_controller,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
>

<label for="end_time">
END TIME
</label>

<input
    type="datetime-local"
    id="end_time"
    name="end_time"
    value="<?php
        echo htmlspecialchars(
            $end_input_value,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
    required
>

<button
    type="submit"
    name="save_end"
    class="save-button"
>
SAVE END
</button>

</form>

</div>

</div>


<!-- ======================================================
     CURRENT TIME
======================================================= -->

<div class="current-time-box">

<div class="current-time-title">
CURRENT TIME
</div>

<div
    class="current-time"
    id="currentTime"
>
Loading current time...
</div>

</div>

</div>


<!-- ======================================================
     CONTROLLER INFORMATION
======================================================= -->

<div class="info">


<div class="info-card">

<div class="info-title">
Controller ID
</div>

<div class="info-value">

<?php

echo htmlspecialchars(
    $selected_controller,
    ENT_QUOTES,
    "UTF-8"
);

?>

</div>

</div>


<div class="info-card">

<div class="info-title">
Customer
</div>

<div class="info-value">

<?php

echo htmlspecialchars(
    $selected_customer,
    ENT_QUOTES,
    "UTF-8"
);

?>

</div>

</div>


<div class="info-card">

<div class="info-title">
Active
</div>

<div
    class="info-value"
    id="activeStatus"
>

<?php

echo $selected_active
    ? "YES"
    : "NO";

?>

</div>

</div>


<div class="info-card">

<div class="info-title">
Last Seen
</div>

<div
    class="info-value"
    id="lastSeen"
>

<?php

echo htmlspecialchars(
    $selected_last_seen,
    ENT_QUOTES,
    "UTF-8"
);

?>

</div>

</div>


</div>


<!-- ======================================================
     D1-D8
======================================================= -->

<div class="pin-grid">


<?php

for (
    $i = 1;
    $i <= 8;
    $i++
) {

    $pin =
        "D" . $i;

    $value =
        $pin_values[$pin];

?>

<div class="pin-card">

<div class="pin-name">

<?php

echo $pin;

?>

</div>


<div
    class="state
    <?php
        echo $value
            ? "state-on"
            : "state-off";
    ?>"
>

<?php

echo $value
    ? "ON"
    : "OFF";

?>

</div>


<form
    method="post"
    class="pin-form"
>

<input
    type="hidden"
    name="controller_id"
    value="<?php
        echo htmlspecialchars(
            $selected_controller,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
>

<input
    type="hidden"
    name="pin"
    value="<?php
        echo $pin;
    ?>"
>

<input
    type="hidden"
    name="value"
    value="1"
>

<button
    type="submit"
    name="set_pin"
    class="on-btn"
>
ON
</button>

</form>


<form
    method="post"
    class="pin-form"
>

<input
    type="hidden"
    name="controller_id"
    value="<?php
        echo htmlspecialchars(
            $selected_controller,
            ENT_QUOTES,
            "UTF-8"
        );
    ?>"
>

<input
    type="hidden"
    name="pin"
    value="<?php
        echo $pin;
    ?>"
>

<input
    type="hidden"
    name="value"
    value="0"
>

<button
    type="submit"
    name="set_pin"
    class="off-btn"
>
OFF
</button>

</form>


</div>

<?php

}

?>

</div>


<?php

}
else {

?>

<div class="message error">
Please select a controller.
</div>

<?php

}

?>


</div>


<script>

/* =========================================================
   SELECT CONTROLLER
========================================================= */

function selectController(id)
{

    if (id === "")
    {

        window.location.href =
            "index.php";

        return;
    }

    window.location.href =
        "index.php?controller_id=" +
        encodeURIComponent(id);
}


/* =========================================================
   CURRENT TIME
   Asia/Kolkata
========================================================= */

function updateCurrentTime()
{

    const now =
        new Date();

    const options = {

        timeZone:
            "Asia/Kolkata",

        year:
            "numeric",

        month:
            "2-digit",

        day:
            "2-digit",

        hour:
            "2-digit",

        minute:
            "2-digit",

        second:
            "2-digit",

        hour12:
            false
    };

    const parts =
        new Intl.DateTimeFormat(
            "en-GB",
            options
        ).formatToParts(now);


    let data = {};

    parts.forEach(
        function(part)
        {
            if (part.type !== "literal")
            {
                data[part.type] =
                    part.value;
            }
        }
    );


    const formatted =
        data.year +
        "-" +
        data.month +
        "-" +
        data.day +
        " " +
        data.hour +
        ":" +
        data.minute +
        ":" +
        data.second;


    const currentTime =
        document.getElementById(
            "currentTime"
        );


    if (currentTime)
    {

        currentTime.textContent =
            formatted +
            " IST";
    }
}


/* Start immediately */

updateCurrentTime();


/* Update every second */

setInterval(
    updateCurrentTime,
    1000
);


/* =========================================================
   AUTO REFRESH
========================================================= */

setInterval(
    function()
    {

        <?php

        if ($selected_controller !== "")
        {

        ?>

        window.location.reload();

        <?php

        }

        ?>

    },
    3000
);

</script>


</body>

</html>
