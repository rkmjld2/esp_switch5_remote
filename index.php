<?php
/*
============================================================
 ESP-SWITCH5 REMOTE - index.php
 REMOTE CONTROL PANEL
============================================================

Remote server:
    Render.com

Database:
    TiDB Cloud

ESP communication:
    api.php

IMPORTANT:
    The ESP device_token is NOT exposed to the browser.

Web control:
    PHP -> TiDB Cloud

ESP control:
    ESP8266 -> api.php -> TiDB Cloud

============================================================
*/


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/db.php";

session_start();


/* =========================================================
   WEB ADMIN PASSWORD
=========================================================

   Set this in Render Environment Variables:

       ADMIN_PASSWORD

   Do NOT put the actual password in this file.
========================================================= */

$admin_password =
    getenv("ADMIN_PASSWORD") ?: "";


/* =========================================================
   LOGOUT
========================================================= */

if (
    isset($_GET["logout"])
)
{
    $_SESSION = [];

    session_destroy();

    header(
        "Location: index.php"
    );

    exit;
}


/* =========================================================
   LOGIN
========================================================= */

$login_error = "";

if (
    isset($_POST["login"])
)
{

    $password =
        $_POST["password"] ?? "";


    if (
        $admin_password !== "" &&
        hash_equals(
            $admin_password,
            $password
        )
    )
    {

        $_SESSION["esp_admin"] =
            true;

        header(
            "Location: index.php"
        );

        exit;
    }
    else
    {

        $login_error =
            "Invalid password.";
    }
}


/* =========================================================
   LOGIN PAGE
========================================================= */

if (
    !isset(
        $_SESSION["esp_admin"]
    ) ||
    $_SESSION["esp_admin"] !== true
)
{
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

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #f2f2f2;
}

.login-box {

    max-width: 420px;

    margin:
        80px auto;

    background: white;

    padding: 30px;

    border-radius: 12px;

    box-shadow:
        0 3px 15px
        rgba(0,0,0,0.15);

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

    margin:
        15px 0;
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

<h1>
ESP-SWITCH5 REMOTE
</h1>

<p>
Administrator Login
</p>

<?php

if (
    $login_error !== ""
)
{
    echo
        '<div class="error">' .
        htmlspecialchars(
            $login_error
        ) .
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
    trim(
        $_GET["controller_id"] ?? ""
    );


/* =========================================================
   MESSAGE
========================================================= */

$message = "";

$message_type = "";


/* =========================================================
   SET PIN
========================================================= */

if (
    isset($_POST["set_pin"])
)
{

    $controller_id =
        trim(
            $_POST["controller_id"] ?? ""
        );

    $pin =
        strtoupper(
            trim(
                $_POST["pin"] ?? ""
            )
        );

    $value =
        isset($_POST["value"])
            ? (int)$_POST["value"]
            : -1;


    /* -----------------------------------------------------
       VALIDATE CONTROLLER
    ----------------------------------------------------- */

    if (
        $controller_id === ""
    )
    {

        $message =
            "Controller ID missing.";

        $message_type =
            "error";
    }


    /* -----------------------------------------------------
       VALIDATE PIN
    ----------------------------------------------------- */

    elseif (
        !preg_match(
            '/^D[1-8]$/',
            $pin
        )
    )
    {

        $message =
            "Invalid pin.";

        $message_type =
            "error";
    }


    /* -----------------------------------------------------
       VALIDATE VALUE
    ----------------------------------------------------- */

    elseif (
        $value !== 0 &&
        $value !== 1
    )
    {

        $message =
            "Invalid value.";

        $message_type =
            "error";
    }


    else
    {

        /*
         * Confirm that the controller exists
         * and is active.
         */

        $stmt =
            $conn->prepare("
                SELECT active
                FROM controllers
                WHERE controller_id = ?
                LIMIT 1
            ");


        if ($stmt)
        {

            $stmt->bind_param(
                "s",
                $controller_id
            );


            $stmt->execute();


            $result =
                $stmt->get_result();


            if (
                $result->num_rows === 0
            )
            {

                $message =
                    "Controller not found.";

                $message_type =
                    "error";
            }
            else
            {

                $controller =
                    $result->fetch_assoc();


                if (
                    (int)$controller["active"]
                    !== 1
                )
                {

                    $message =
                        "Controller is inactive.";

                    $message_type =
                        "error";
                }
                else
                {

                    /*
                     * PIN has already been strictly
                     * validated as D1-D8.
                     */

                    $sql = "
                        UPDATE esp_control
                        SET `$pin` = ?
                        WHERE controller_id = ?
                    ";


                    $update =
                        $conn->prepare(
                            $sql
                        );


                    if (!$update)
                    {

                        $message =
                            "Pin update preparation failed.";

                        $message_type =
                            "error";
                    }
                    else
                    {

                        $update->bind_param(
                            "is",
                            $value,
                            $controller_id
                        );


                        if (
                            $update->execute()
                        )
                        {

                            $message =
                                $pin .
                                " changed to " .
                                (
                                    $value
                                    ? "ON"
                                    : "OFF"
                                );

                            $message_type =
                                "success";
                        }
                        else
                        {

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
        }
        else
        {

            $message =
                "Controller query failed.";

            $message_type =
                "error";
        }


        $selected_controller =
            $controller_id;
    }
}


/* =========================================================
   READ ACTIVE CONTROLLERS
========================================================= */

$controllers = [];


$result =
    $conn->query("
        SELECT
            controller_id,
            customer_name,
            active
        FROM controllers
        WHERE active = 1
        ORDER BY controller_id
    ");


if ($result)
{

    while (
        $row =
            $result->fetch_assoc()
    )
    {

        $controllers[] =
            $row;
    }
}


/* =========================================================
   CONTROLLER INFORMATION
========================================================= */

$selected_customer = "";

$selected_active = 0;

/*
 * Use NULL initially because the database may contain
 * NULL for a newly created controller.
 */
$selected_last_seen = null;


if (
    $selected_controller !== ""
)
{

    $stmt =
        $conn->prepare("
            SELECT
                controller_id,
                customer_name,
                active,
                last_seen
            FROM controllers
            WHERE controller_id = ?
            LIMIT 1
        ");


    if ($stmt)
    {

        $stmt->bind_param(
            "s",
            $selected_controller
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result->num_rows > 0
        )
        {

            $row =
                $result->fetch_assoc();


            /*
             * NULL is allowed here.
             */
            $selected_customer =
                $row["customer_name"] ?? "";

            $selected_active =
                (int)$row["active"];

            $selected_last_seen =
                $row["last_seen"];
        }


        $stmt->close();
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


if (
    $selected_controller !== ""
)
{

    $stmt =
        $conn->prepare("
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


    if ($stmt)
    {

        $stmt->bind_param(
            "s",
            $selected_controller
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        if (
            $result->num_rows > 0
        )
        {

            $row =
                $result->fetch_assoc();


            for (
                $i = 1;
                $i <= 8;
                $i++
            )
            {

                $pin =
                    "D" . $i;


                $pin_values[$pin] =
                    (int)$row[$pin];
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

<title>
ESP-SWITCH5 REMOTE
</title>


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

    margin:
        0 0 5px 0;

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

@media (
    max-width: 600px
)
{

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

foreach (
    $controllers as $controller
)
{

?>

<option
    value="<?php
        echo htmlspecialchars(
            $controller["controller_id"],
            ENT_QUOTES
        );
    ?>"
    <?php

    if (
        $selected_controller ===
        $controller["controller_id"]
    )
    {
        echo "selected";
    }

    ?>
>

<?php

echo htmlspecialchars(
    $controller["controller_id"]
);

if (
    $controller["customer_name"] !== ""
)
{

    echo " - ";

    echo htmlspecialchars(
        $controller["customer_name"]
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

if (
    $message !== ""
)
{

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
    $message
);

?>

</div>

<?php

}


if (
    $selected_controller !== ""
)
{

?>


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
    $selected_controller
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
    $selected_customer
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

/*
 * FIX:
 * last_seen may be NULL for a newly created controller.
 *
 * Do not pass NULL to htmlspecialchars().
 */

if (
    !empty($selected_last_seen)
)
{

    echo htmlspecialchars(
        $selected_last_seen
    );

}
else
{

    echo "Not yet seen";

}

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
)
{

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
            ENT_QUOTES
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
            ENT_QUOTES
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
else
{

?>

<div
    class="message error"
>
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

    if (
        id === ""
    )
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
   AUTO REFRESH
========================================================= */

setInterval(
    function()
    {

        /*
         * Refresh the selected controller page every
         * 3 seconds so that D1-D8 and last_seen reflect
         * the latest database values.
         */

        <?php

        if (
            $selected_controller !== ""
        )
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
