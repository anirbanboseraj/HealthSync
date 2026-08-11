<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== "patient") {
    header("Location: ../login.php");
    exit();
}

require_once("../config/database.php");

$user_id = intval($_SESSION['user_id']);

$message = "";
$error = "";


/* =========================================================
   GET CURRENT USER
========================================================= */

$sql = "
    SELECT id, fullname, email, image, role
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

if (!$user) {
    die("User account not found.");
}


/* =========================================================
   UPDATE PROFILE
========================================================= */

if (isset($_POST['update_profile'])) {

    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);

    if ($fullname === "" || $email === "") {

        $error = "Full name and email are required.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        /* Check duplicate email */

        $check_sql = "
            SELECT id
            FROM users
            WHERE email = ?
            AND id != ?
            LIMIT 1
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $email,
            $user_id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This email address is already being used.";

        } else {

            $update_sql = "
                UPDATE users
                SET fullname = ?, email = ?
                WHERE id = ?
            ";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );

            mysqli_stmt_bind_param(
                $update_stmt,
                "ssi",
                $fullname,
                $email,
                $user_id
            );

            if (mysqli_stmt_execute($update_stmt)) {

                $_SESSION['fullname'] = $fullname;
                $_SESSION['email'] = $email;

                $user['fullname'] = $fullname;
                $user['email'] = $email;

                $message = "Profile updated successfully.";

            } else {

                $error = "Unable to update profile.";
            }

            mysqli_stmt_close($update_stmt);
        }

        mysqli_stmt_close($check_stmt);
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

        $error = "Please fill in all password fields.";

    } elseif ($new_password !== $confirm_password) {

        $error = "New passwords do not match.";

    } elseif (strlen($new_password) < 6) {

        $error = "New password must contain at least 6 characters.";

    } else {

        /* Get current password */

        $password_sql = "
            SELECT password
            FROM users
            WHERE id = ?
            LIMIT 1
        ";

        $password_stmt = mysqli_prepare(
            $conn,
            $password_sql
        );

        mysqli_stmt_bind_param(
            $password_stmt,
            "i",
            $user_id
        );

        mysqli_stmt_execute($password_stmt);

        $password_result =
            mysqli_stmt_get_result($password_stmt);

        $password_data =
            mysqli_fetch_assoc($password_result);

        mysqli_stmt_close($password_stmt);


        if (
            !$password_data ||
            !password_verify(
                $current_password,
                $password_data['password']
            )
        ) {

            $error = "Current password is incorrect.";

        } else {

            $hashed_password =
                password_hash(
                    $new_password,
                    PASSWORD_DEFAULT
                );


            $update_password_sql = "
                UPDATE users
                SET password = ?
                WHERE id = ?
            ";

            $update_password_stmt =
                mysqli_prepare(
                    $conn,
                    $update_password_sql
                );

            mysqli_stmt_bind_param(
                $update_password_stmt,
                "si",
                $hashed_password,
                $user_id
            );


            if (
                mysqli_stmt_execute(
                    $update_password_stmt
                )
            ) {

                $message =
                    "Password changed successfully.";

            } else {

                $error =
                    "Unable to change password.";
            }


            mysqli_stmt_close(
                $update_password_stmt
            );
        }
    }
}


/* =========================================================
   REFRESH USER DATA
========================================================= */

$sql = "
    SELECT id, fullname, email, image, role
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param($stmt, "i", $user_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($result);

mysqli_stmt_close($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Settings | HealthSync</title>


<!-- FONT AWESOME -->

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

    position: fixed;

    left: 0;

    top: 0;

    bottom: 0;

    width: 250px;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    padding: 25px 15px;

    z-index: 1000;

}


/* LOGO */

.logo {

    text-align: center;

    margin-bottom: 30px;

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


/* LINKS */

.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    width: 100%;

    padding: 13px 15px;

    margin-bottom: 6px;

    border-radius: 8px;

    color: #94a3b8;

    text-decoration: none;

    font-size: 13px;

    transition: .2s;

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


/* LOGOUT */

.sidebar .logout {

    margin-top: 25px;

    color: #f87171;

}


.sidebar .logout:hover {

    background: #450a0a;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left: 250px;

    width: calc(100% - 250px);

    padding: 30px;

}


/* =========================================================
   TOPBAR
========================================================= */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.page-title h1 {

    color: white;

    font-size: 27px;

}


.page-title p {

    color: #64748b;

    font-size: 12px;

    margin-top: 6px;

}


/* PROFILE */

.profile-mini {

    display: flex;

    align-items: center;

    gap: 10px;

}


.profile-mini img,
.profile-mini .avatar {

    width: 42px;

    height: 42px;

    border-radius: 50%;

    object-fit: cover;

}


.profile-mini .avatar {

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

}


.profile-mini strong {

    display: block;

    color: white;

    font-size: 12px;

}


.profile-mini small {

    color: #64748b;

    font-size: 10px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding: 13px 16px;

    border-radius: 8px;

    margin-bottom: 20px;

    background: #052e16;

    border: 1px solid #166534;

    color: #86efac;

    font-size: 12px;

}


.error {

    padding: 13px 16px;

    border-radius: 8px;

    margin-bottom: 20px;

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #fca5a5;

    font-size: 12px;

}


/* =========================================================
   GRID
========================================================= */

.settings-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;

}


/* =========================================================
   CARD
========================================================= */

.card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    padding: 25px;

}


.card.full {

    grid-column: 1 / -1;

}


.card-header {

    display: flex;

    align-items: center;

    gap: 12px;

    padding-bottom: 18px;

    margin-bottom: 20px;

    border-bottom: 1px solid #1e293b;

}


.card-header .icon {

    width: 40px;

    height: 40px;

    border-radius: 9px;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

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
   PROFILE
========================================================= */

.profile-section {

    display: flex;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;

}


.profile-picture {

    width: 80px;

    height: 80px;

    border-radius: 50%;

    overflow: hidden;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 25px;

    flex-shrink: 0;

}


.profile-picture img {

    width: 100%;

    height: 100%;

    object-fit: cover;

}


.profile-info h3 {

    color: white;

    font-size: 15px;

}


.profile-info p {

    color: #64748b;

    font-size: 11px;

    margin-top: 5px;

}


/* =========================================================
   FORM
========================================================= */

.form-group {

    margin-bottom: 17px;

}


.form-group label {

    display: block;

    color: #94a3b8;

    font-size: 11px;

    margin-bottom: 7px;

}


.form-group input {

    width: 100%;

    padding: 11px 13px;

    border-radius: 8px;

    border: 1px solid #334155;

    outline: none;

    background: #020617;

    color: white;

    font-size: 12px;

}


.form-group input:focus {

    border-color: #3b82f6;

    box-shadow:
        0 0 0 2px
        rgba(59,130,246,.12);

}


.form-group input::placeholder {

    color: #475569;

}


/* =========================================================
   BUTTON
========================================================= */

.btn {

    border: none;

    border-radius: 8px;

    padding: 11px 17px;

    background: #2563eb;

    color: white;

    font-size: 11px;

    cursor: pointer;

    transition: .2s;

}


.btn:hover {

    background: #1d4ed8;

}


.btn i {

    margin-right: 6px;

}


/* =========================================================
   SYSTEM INFORMATION
========================================================= */

.info-list {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;

}


.info-item {

    padding: 17px;

    background: #020617;

    border: 1px solid #1e293b;

    border-radius: 9px;

}


.info-item span {

    display: block;

    color: #64748b;

    font-size: 9px;

    margin-bottom: 7px;

}


.info-item strong {

    color: white;

    font-size: 12px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 850px) {

    .settings-grid {

        grid-template-columns: 1fr;

    }

    .card.full {

        grid-column: auto;

    }

    .info-list {

        grid-template-columns: 1fr;

    }

}


@media(max-width: 650px) {

    .sidebar {

        position: relative;

        width: 100%;

        min-height: auto;

    }

    .dashboard {

        display: block;

    }

    .main {

        margin-left: 0;

        width: 100%;

    }

    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

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
            Patient Portal
        </p>

    </div>


    <a href="/HealthSync/patient/dashboard.php">

        <i class="fa-solid fa-house"></i>

        Dashboard

    </a>


    <a href="/HealthSync/patient/doctors.php">

        <i class="fa-solid fa-user-doctor"></i>

        Find Doctors

    </a>


    <a href="/HealthSync/patient/appointments.php">

        <i class="fa-solid fa-calendar-check"></i>

        My Appointments

    </a>


    <a href="/HealthSync/patient/prescriptions.php">

        <i class="fa-solid fa-file-prescription"></i>

        Prescriptions

    </a>


    <a href="/HealthSync/patient/profile.php">

        <i class="fa-solid fa-user"></i>

        My Profile

    </a>


    <a
        href="/HealthSync/patient/settings.php"
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


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="page-title">

            <h1>
                Settings
            </h1>

            <p>
                Manage your HealthSync account settings.
            </p>

        </div>


        <div class="profile-mini">


            <?php if (!empty($user['image'])): ?>

                <img
                    src="../assets/uploads/<?php echo htmlspecialchars($user['image']); ?>"
                    alt="Profile"
                >

            <?php else: ?>

                <div class="avatar">

                    <i class="fa-solid fa-user"></i>

                </div>

            <?php endif; ?>


            <div>

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $user['fullname']
                    );

                    ?>

                </strong>

                <small>
                    Patient
                </small>

            </div>


        </div>


    </div>


    <!-- =================================================
         MESSAGES
    ================================================== -->

    <?php if ($message !== ""): ?>

        <div class="message">

            <i class="fa-solid fa-circle-check"></i>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         SETTINGS GRID
    ================================================== -->

    <div class="settings-grid">


        <!-- =================================================
             PROFILE SETTINGS
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div class="icon">

                    <i class="fa-solid fa-user"></i>

                </div>


                <div>

                    <h2>
                        Account Information
                    </h2>

                    <p>
                        Update your personal information.
                    </p>

                </div>


            </div>


            <div class="profile-section">


                <div class="profile-picture">


                    <?php if (!empty($user['image'])): ?>

                        <img
                            src="../assets/uploads/<?php echo htmlspecialchars($user['image']); ?>"
                            alt="Profile"
                        >

                    <?php else: ?>

                        <i class="fa-solid fa-user"></i>

                    <?php endif; ?>


                </div>


                <div class="profile-info">

                    <h3>

                        <?php

                        echo htmlspecialchars(
                            $user['fullname']
                        );

                        ?>

                    </h3>

                    <p>
                        Patient Account
                    </p>

                </div>


            </div>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="fullname"
                        value="<?php echo htmlspecialchars($user['fullname']); ?>"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Email Address
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="update_profile"
                    class="btn"
                >

                    <i class="fa-solid fa-save"></i>

                    Update Profile

                </button>


            </form>


        </div>


        <!-- =================================================
             PASSWORD
        ================================================== -->

        <div class="card">


            <div class="card-header">


                <div class="icon">

                    <i class="fa-solid fa-lock"></i>

                </div>


                <div>

                    <h2>
                        Change Password
                    </h2>

                    <p>
                        Keep your account secure.
                    </p>

                </div>


            </div>


            <form method="POST">


                <div class="form-group">

                    <label>
                        Current Password
                    </label>

                    <input
                        type="password"
                        name="current_password"
                        placeholder="Enter current password"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        New Password
                    </label>

                    <input
                        type="password"
                        name="new_password"
                        placeholder="Minimum 6 characters"
                        required
                    >

                </div>


                <div class="form-group">

                    <label>
                        Confirm New Password
                    </label>

                    <input
                        type="password"
                        name="confirm_password"
                        placeholder="Repeat new password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    name="change_password"
                    class="btn"
                >

                    <i class="fa-solid fa-key"></i>

                    Change Password

                </button>


            </form>


        </div>


        <!-- =================================================
             SYSTEM INFORMATION
        ================================================== -->

        <div class="card full">


            <div class="card-header">


                <div class="icon">

                    <i class="fa-solid fa-circle-info"></i>

                </div>


                <div>

                    <h2>
                        Account Information
                    </h2>

                    <p>
                        Your HealthSync account details.
                    </p>

                </div>


            </div>


            <div class="info-list">


                <div class="info-item">

                    <span>
                        ACCOUNT ID
                    </span>

                    <strong>

                        #<?php echo intval($user['id']); ?>

                    </strong>

                </div>


                <div class="info-item">

                    <span>
                        ACCOUNT TYPE
                    </span>

                    <strong>
                        Patient
                    </strong>

                </div>


                <div class="info-item">

                    <span>
                        PLATFORM
                    </span>

                    <strong>
                        HealthSync
                    </strong>

                </div>


            </div>


        </div>


    </div>


</main>


</div>


</body>

</html>