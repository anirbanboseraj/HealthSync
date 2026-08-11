<?php

session_start();

/* =========================================================
   ADMIN SECURITY
========================================================= */

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   DATABASE
========================================================= */

require_once("../config/database.php");


/* =========================================================
   ADMIN ID
========================================================= */

$admin_id = intval($_SESSION['user_id']);

$message = "";
$message_type = "";


/* =========================================================
   GET ADMIN INFORMATION
========================================================= */

$sql = "
    SELECT id, fullname, email, role
    FROM users
    WHERE id = $admin_id
    LIMIT 1
";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

$admin = mysqli_fetch_assoc($result);

if (!$admin) {
    session_destroy();
    header("Location: ../login.php");
    exit();
}


/* =========================================================
   UPDATE PROFILE
========================================================= */

if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    if ($fullname === "" || $email === "") {

        $message = "Name and email cannot be empty.";
        $message_type = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "error";

    } else {

        $fullname_safe = mysqli_real_escape_string(
            $conn,
            $fullname
        );

        $email_safe = mysqli_real_escape_string(
            $conn,
            $email
        );


        /* Check if email belongs to another user */

        $check_sql = "
            SELECT id
            FROM users
            WHERE email = '$email_safe'
            AND id != $admin_id
            LIMIT 1
        ";

        $check_result = mysqli_query(
            $conn,
            $check_sql
        );


        if (
            $check_result &&
            mysqli_num_rows($check_result) > 0
        ) {

            $message =
                "This email is already being used by another account.";

            $message_type = "error";

        } else {

            $update_sql = "
                UPDATE users
                SET
                    fullname = '$fullname_safe',
                    email = '$email_safe'
                WHERE id = $admin_id
            ";


            if (mysqli_query($conn, $update_sql)) {

                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;

                $admin['fullname'] = $fullname;
                $admin['email'] = $email;

                $message =
                    "Profile updated successfully.";

                $message_type = "success";

            } else {

                $message =
                    "Unable to update profile.";

                $message_type = "error";

            }

        }

    }

}


/* =========================================================
   CHANGE PASSWORD
========================================================= */

if (isset($_POST['change_password'])) {

    $current_password =
        $_POST['current_password'] ?? "";

    $new_password =
        $_POST['new_password'] ?? "";

    $confirm_password =
        $_POST['confirm_password'] ?? "";


    if (
        $current_password === "" ||
        $new_password === "" ||
        $confirm_password === ""
    ) {

        $message =
            "Please fill in all password fields.";

        $message_type = "error";

    } elseif (
        strlen($new_password) < 6
    ) {

        $message =
            "New password must contain at least 6 characters.";

        $message_type = "error";

    } elseif (
        $new_password !== $confirm_password
    ) {

        $message =
            "New password and confirm password do not match.";

        $message_type = "error";

    } else {


        /* Get current password */

        $password_sql = "
            SELECT password
            FROM users
            WHERE id = $admin_id
            LIMIT 1
        ";


        $password_result =
            mysqli_query(
                $conn,
                $password_sql
            );


        $password_row =
            mysqli_fetch_assoc(
                $password_result
            );


        if (!$password_row) {

            $message =
                "Admin account could not be found.";

            $message_type = "error";

        } else {


            $stored_password =
                $password_row['password'];


            /* =================================================
               SUPPORT HASHED PASSWORDS
            ================================================= */

            $password_correct =
                password_verify(
                    $current_password,
                    $stored_password
                );


            /*
             * If your old system stores plain passwords,
             * this also allows the existing password.
             */

            if (
                !$password_correct &&
                $current_password === $stored_password
            ) {

                $password_correct = true;

            }


            if (!$password_correct) {

                $message =
                    "Current password is incorrect.";

                $message_type = "error";

            } else {


                /* Hash new password */

                $new_password_hash =
                    password_hash(
                        $new_password,
                        PASSWORD_DEFAULT
                    );


                $new_password_safe =
                    mysqli_real_escape_string(
                        $conn,
                        $new_password_hash
                    );


                $update_password_sql = "
                    UPDATE users
                    SET password = '$new_password_safe'
                    WHERE id = $admin_id
                ";


                if (
                    mysqli_query(
                        $conn,
                        $update_password_sql
                    )
                ) {

                    $message =
                        "Password changed successfully.";

                    $message_type = "success";

                } else {

                    $message =
                        "Unable to change password.";

                    $message_type = "error";

                }

            }

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Settings | HealthSync
</title>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* =========================================================
   RESET
========================================================= */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


/* =========================================================
   BODY
========================================================= */

body {

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* =========================================================
   LAYOUT
========================================================= */

.dashboard {

    min-height: 100vh;

    display: flex;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    width: 250px;

    position: fixed;

    top: 0;

    left: 0;

    bottom: 0;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    padding: 25px 15px;

}


.logo {

    text-align: center;

    margin-bottom: 35px;

}


.logo h2 {

    color: white;

    font-size: 25px;

}


.logo span {

    color: #3b82f6;

}


.logo p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 6px;

    border-radius: 8px;

    color: #94a3b8;

    text-decoration: none;

    font-size: 14px;

    transition: 0.2s;

}


.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}


.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


.sidebar a i {

    width: 20px;

    text-align: center;

}


.logout {

    margin-top: 30px;

    color: #f87171 !important;

}


.logout:hover {

    background: #450a0a !important;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 35px;

}


/* =========================================================
   HEADER
========================================================= */

.header {

    margin-bottom: 30px;

}


.header h1 {

    color: white;

    font-size: 28px;

}


.header p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 14px 18px;

    border-radius: 10px;

    margin-bottom: 20px;

    font-size: 12px;

    border: 1px solid;

}


.message.success {

    background: #052e16;

    border-color: #166534;

    color: #4ade80;

}


.message.error {

    background: #450a0a;

    border-color: #991b1b;

    color: #f87171;

}


/* =========================================================
   SETTINGS GRID
========================================================= */

.settings-grid {

    display: grid;

    grid-template-columns:
        1fr 1fr;

    gap: 22px;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 15px;

    padding: 25px;

}


.card.full {

    grid-column: 1 / -1;

}


/* =========================================================
   CARD HEADER
========================================================= */

.card-header {

    display: flex;

    align-items: center;

    gap: 13px;

    margin-bottom: 25px;

}


.card-icon {

    width: 45px;

    height: 45px;

    border-radius: 11px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 18px;

}


.card-header h2 {

    color: white;

    font-size: 16px;

}


.card-header p {

    color: #64748b;

    font-size: 10px;

    margin-top: 4px;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 18px;

}


.form-group label {

    display: block;

    color: #94a3b8;

    font-size: 11px;

    margin-bottom: 8px;

}


.input-box {

    position: relative;

}


.input-box i {

    position: absolute;

    left: 13px;

    top: 50%;

    transform: translateY(-50%);

    color: #64748b;

    font-size: 13px;

}


.form-group input {

    width: 100%;

    padding: 12px 13px 12px 40px;

    background: #020617;

    border: 1px solid #1e293b;

    border-radius: 8px;

    color: white;

    outline: none;

    font-size: 12px;

}


.form-group input:focus {

    border-color: #3b82f6;

}


.form-group input::placeholder {

    color: #475569;

}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    border: none;

    background: #2563eb;

    color: white;

    padding: 11px 18px;

    border-radius: 8px;

    cursor: pointer;

    font-size: 11px;

    font-weight: bold;

    display: inline-flex;

    align-items: center;

    gap: 8px;

}


.btn:hover {

    background: #3b82f6;

}


/* =========================================================
   ACCOUNT INFO
========================================================= */

.account-info {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.info-box {

    background: #111827;

    border: 1px solid #1e293b;

    border-radius: 10px;

    padding: 17px;

}


.info-box small {

    display: block;

    color: #64748b;

    font-size: 9px;

    text-transform: uppercase;

    margin-bottom: 7px;

}


.info-box strong {

    color: white;

    font-size: 12px;

}


/* =========================================================
   SECURITY NOTE
========================================================= */

.security-note {

    margin-top: 20px;

    padding: 15px;

    border-radius: 9px;

    background: #111827;

    border: 1px solid #1e293b;

    color: #64748b;

    font-size: 10px;

    line-height: 1.7;

}


.security-note i {

    color: #60a5fa;

    margin-right: 6px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width:850px) {

    .settings-grid {

        grid-template-columns: 1fr;

    }

    .card.full {

        grid-column: auto;

    }

}


@media(max-width:650px) {

    .sidebar {

        width: 200px;

    }

    .main {

        margin-left: 200px;

        width: calc(100% - 200px);

        padding: 20px;

    }

    .account-info {

        grid-template-columns: 1fr;

    }

}

</style>

</head>


<body>


<div class="dashboard">


<!-- =====================================================
     SIDEBAR
===================================================== -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

        <p>
            Admin Portal
        </p>

    </div>


    <a href="/HealthSync/admin/dashboard.php">

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


    <a href="/HealthSync/admin/doctors.php">

        <i class="fa-solid fa-user-doctor"></i>

        Doctors

    </a>


    <a href="/HealthSync/admin/patients.php">

        <i class="fa-solid fa-users"></i>

        Patients

    </a>


    <a href="/HealthSync/admin/appointments.php">

        <i class="fa-solid fa-calendar-check"></i>

        Appointments

    </a>


    <a href="/HealthSync/admin/prescriptions.php">

        <i class="fa-solid fa-file-prescription"></i>

        Prescriptions

    </a>


    <a href="/HealthSync/admin/reports.php">

        <i class="fa-solid fa-chart-line"></i>

        Reports

    </a>


    <a
        href="/HealthSync/admin/settings.php"
        class="active"
    >

        <i class="fa-solid fa-gear"></i>

        Settings

    </a>


    <a
        href="/HealthSync/logout.php"
        class="logout"
    >

        <i class="fa-solid fa-right-from-bracket"></i>

        Logout

    </a>


</aside>



<!-- =====================================================
     MAIN
===================================================== -->

<main class="main">


    <div class="header">

        <h1>
            Admin Settings
        </h1>

        <p>
            Manage your HealthSync administrator account and security.
        </p>

    </div>



    <?php if ($message !== "") { ?>

        <div
            class="message
            <?php echo $message_type; ?>"
        >

            <i
                class="fa-solid
                <?php

                echo $message_type === 'success'
                    ? 'fa-circle-check'
                    : 'fa-circle-exclamation';

                ?>"
            ></i>

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </div>

    <?php } ?>



    <div class="settings-grid">


        <!-- =================================================
             PROFILE
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div class="card-icon">

                    <i
                        class="fa-solid fa-user"
                    ></i>

                </div>


                <div>

                    <h2>
                        Admin Profile
                    </h2>

                    <p>
                        Update your personal information.
                    </p>

                </div>


            </div>



            <form
                method="POST"
                action=""
            >


                <div class="form-group">

                    <label>
                        Full Name
                    </label>


                    <div class="input-box">

                        <i
                            class="fa-solid fa-user"
                        ></i>


                        <input
                            type="text"
                            name="fullname"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['fullname']
                            );

                            ?>"
                            placeholder="Enter full name"
                            required
                        >

                    </div>

                </div>



                <div class="form-group">

                    <label>
                        Email Address
                    </label>


                    <div class="input-box">

                        <i
                            class="fa-solid fa-envelope"
                        ></i>


                        <input
                            type="email"
                            name="email"
                            value="<?php

                            echo htmlspecialchars(
                                $admin['email']
                            );

                            ?>"
                            placeholder="Enter email"
                            required
                        >

                    </div>

                </div>



                <button
                    type="submit"
                    name="update_profile"
                    class="btn"
                >

                    <i
                        class="fa-solid fa-floppy-disk"
                    ></i>

                    Save Changes

                </button>


            </form>


        </div>



        <!-- =================================================
             CHANGE PASSWORD
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div class="card-icon">

                    <i
                        class="fa-solid fa-lock"
                    ></i>

                </div>


                <div>

                    <h2>
                        Change Password
                    </h2>

                    <p>
                        Update your administrator password.
                    </p>

                </div>


            </div>



            <form
                method="POST"
                action=""
            >


                <div class="form-group">

                    <label>
                        Current Password
                    </label>


                    <div class="input-box">

                        <i
                            class="fa-solid fa-key"
                        ></i>


                        <input
                            type="password"
                            name="current_password"
                            placeholder="Current password"
                            required
                        >

                    </div>

                </div>



                <div class="form-group">

                    <label>
                        New Password
                    </label>


                    <div class="input-box">

                        <i
                            class="fa-solid fa-lock"
                        ></i>


                        <input
                            type="password"
                            name="new_password"
                            placeholder="Minimum 6 characters"
                            required
                        >

                    </div>

                </div>



                <div class="form-group">

                    <label>
                        Confirm New Password
                    </label>


                    <div class="input-box">

                        <i
                            class="fa-solid fa-shield-halved"
                        ></i>


                        <input
                            type="password"
                            name="confirm_password"
                            placeholder="Repeat new password"
                            required
                        >

                    </div>

                </div>



                <button
                    type="submit"
                    name="change_password"
                    class="btn"
                >

                    <i
                        class="fa-solid fa-key"
                    ></i>

                    Change Password

                </button>


            </form>


        </div>



        <!-- =================================================
             ACCOUNT INFORMATION
        ================================================== -->

        <div class="card full">


            <div class="card-header">


                <div class="card-icon">

                    <i
                        class="fa-solid fa-shield-halved"
                    ></i>

                </div>


                <div>

                    <h2>
                        Account Information
                    </h2>

                    <p>
                        Current administrator account details.
                    </p>

                </div>


            </div>



            <div class="account-info">


                <div class="info-box">

                    <small>
                        User ID
                    </small>

                    <strong>

                        #

                        <?php

                        echo intval(
                            $admin['id']
                        );

                        ?>

                    </strong>

                </div>



                <div class="info-box">

                    <small>
                        Account Role
                    </small>

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            ucfirst(
                                $admin['role']
                            )
                        );

                        ?>

                    </strong>

                </div>



                <div class="info-box">

                    <small>
                        Account Status
                    </small>

                    <strong>
                        Active
                    </strong>

                </div>


            </div>



            <div class="security-note">

                <i
                    class="fa-solid fa-circle-info"
                ></i>

                For security, always use a strong password and
                never share your administrator credentials with
                other users.

            </div>


        </div>


    </div>


</main>


</div>


</body>

</html>