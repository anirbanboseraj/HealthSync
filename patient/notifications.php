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


/* =========================================================
   MARK ONE NOTIFICATION AS READ
========================================================= */

if (isset($_GET['read'])) {

    $notification_id = intval($_GET['read']);

    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE id = ?
        AND user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $notification_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: notifications.php");
    exit();
}


/* =========================================================
   MARK ALL AS READ
========================================================= */

if (isset($_POST['mark_all_read'])) {

    $sql = "
        UPDATE notifications
        SET is_read = 1
        WHERE user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    $message = "All notifications marked as read.";
}


/* =========================================================
   DELETE ONE NOTIFICATION
========================================================= */

if (isset($_GET['delete'])) {

    $notification_id = intval($_GET['delete']);

    $sql = "
        DELETE FROM notifications
        WHERE id = ?
        AND user_id = ?
    ";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $notification_id,
        $user_id
    );

    mysqli_stmt_execute($stmt);

    mysqli_stmt_close($stmt);

    header("Location: notifications.php");
    exit();
}


/* =========================================================
   GET USER
========================================================= */

$sql = "
    SELECT fullname, email, image
    FROM users
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$user_result = mysqli_stmt_get_result($stmt);

$user = mysqli_fetch_assoc($user_result);

mysqli_stmt_close($stmt);


/* =========================================================
   GET NOTIFICATIONS
========================================================= */

$sql = "
    SELECT
        id,
        title,
        message,
        type,
        related_id,
        is_read,
        created_at
    FROM notifications
    WHERE user_id = ?
    ORDER BY created_at DESC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);


/* =========================================================
   UNREAD COUNT
========================================================= */

$count_sql = "
    SELECT COUNT(*) AS unread
    FROM notifications
    WHERE user_id = ?
    AND is_read = 0
";

$count_stmt = mysqli_prepare(
    $conn,
    $count_sql
);

mysqli_stmt_bind_param(
    $count_stmt,
    "i",
    $user_id
);

mysqli_stmt_execute($count_stmt);

$count_result =
    mysqli_stmt_get_result($count_stmt);

$count_data =
    mysqli_fetch_assoc($count_result);

$unread_count =
    intval($count_data['unread']);

mysqli_stmt_close($count_stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport"
      content="width=device-width, initial-scale=1.0">

<title>Notifications | HealthSync</title>


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
   ACTION BAR
========================================================= */

.action-bar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 20px;

}


.notification-count {

    color: #94a3b8;

    font-size: 12px;

}


.notification-count strong {

    color: #60a5fa;

}


/* BUTTON */

.btn {

    border: none;

    border-radius: 8px;

    padding: 10px 15px;

    background: #2563eb;

    color: white;

    font-size: 11px;

    cursor: pointer;

}


.btn:hover {

    background: #1d4ed8;

}


/* =========================================================
   NOTIFICATION LIST
========================================================= */

.notification-list {

    display: flex;

    flex-direction: column;

    gap: 12px;

}


/* CARD */

.notification-card {

    display: flex;

    align-items: flex-start;

    gap: 15px;

    padding: 18px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 12px;

    transition: .2s;

}


.notification-card:hover {

    border-color: #334155;

}


/* UNREAD */

.notification-card.unread {

    border-left: 3px solid #3b82f6;

    background: #0c1830;

}


/* ICON */

.notification-icon {

    width: 44px;

    height: 44px;

    border-radius: 10px;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    color: #60a5fa;

    flex-shrink: 0;

    font-size: 17px;

}


/* CONTENT */

.notification-content {

    flex: 1;

}


.notification-content h3 {

    color: white;

    font-size: 14px;

    margin-bottom: 6px;

}


.notification-content p {

    color: #94a3b8;

    font-size: 12px;

    line-height: 1.6;

}


.notification-time {

    color: #475569;

    font-size: 10px;

    margin-top: 8px;

}


/* ACTIONS */

.notification-actions {

    display: flex;

    gap: 8px;

    align-items: center;

}


.notification-actions a {

    width: 32px;

    height: 32px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 7px;

    text-decoration: none;

    color: #94a3b8;

    background: #020617;

}


.notification-actions a:hover {

    color: #60a5fa;

    background: #172554;

}


.notification-actions .delete:hover {

    color: #f87171;

    background: #450a0a;

}


/* =========================================================
   EMPTY
========================================================= */

.empty {

    padding: 70px 20px;

    text-align: center;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

}


.empty i {

    font-size: 40px;

    color: #334155;

    margin-bottom: 15px;

}


.empty h3 {

    color: white;

    font-size: 16px;

    margin-bottom: 7px;

}


.empty p {

    color: #64748b;

    font-size: 11px;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    margin-bottom: 20px;

    padding: 12px 15px;

    border-radius: 8px;

    background: #052e16;

    border: 1px solid #166534;

    color: #86efac;

    font-size: 11px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media(max-width: 800px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

        width: calc(100% - 210px);

    }

}


@media(max-width: 650px) {

    .dashboard {

        display: block;

    }

    .sidebar {

        position: relative;

        width: 100%;

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

    .notification-card {

        flex-direction: column;

    }

    .notification-actions {

        align-self: flex-end;

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


    <a
        href="/HealthSync/patient/notifications.php"
        class="active"
    >

        <i class="fa-solid fa-bell"></i>

        Notifications

        <?php if ($unread_count > 0): ?>

            <span style="
                margin-left:auto;
                background:#ef4444;
                color:white;
                border-radius:20px;
                padding:2px 7px;
                font-size:9px;
            ">

                <?php echo $unread_count; ?>

            </span>

        <?php endif; ?>

    </a>


    <a href="/HealthSync/patient/profile.php">

        <i class="fa-solid fa-user"></i>

        My Profile

    </a>


    <a href="/HealthSync/patient/settings.php">

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
                Notifications
            </h1>

            <p>
                Stay updated with your HealthSync activity.
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


    <?php if ($message !== ""): ?>

        <div class="message">

            <i class="fa-solid fa-circle-check"></i>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ACTION BAR -->

    <div class="action-bar">


        <div class="notification-count">

            You have

            <strong>
                <?php echo $unread_count; ?>
            </strong>

            unread notification<?php echo $unread_count == 1 ? "" : "s"; ?>.

        </div>


        <?php if ($unread_count > 0): ?>

            <form method="POST">

                <button
                    type="submit"
                    name="mark_all_read"
                    class="btn"
                >

                    <i class="fa-solid fa-check-double"></i>

                    Mark All as Read

                </button>

            </form>

        <?php endif; ?>


    </div>


    <!-- =================================================
         NOTIFICATIONS
    ================================================== -->

    <div class="notification-list">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <?php while (
                $notification =
                mysqli_fetch_assoc($result)
            ): ?>


                <?php

                $type =
                    strtolower(
                        $notification['type']
                    );


                if ($type === "appointment") {

                    $icon =
                        "fa-calendar-check";

                } elseif ($type === "prescription") {

                    $icon =
                        "fa-file-prescription";

                } elseif ($type === "doctor") {

                    $icon =
                        "fa-user-doctor";

                } else {

                    $icon =
                        "fa-bell";

                }

                ?>


                <div class="notification-card
                    <?php
                    echo
                        $notification['is_read']
                        == 0
                        ? 'unread'
                        : '';
                    ?>"
                >


                    <!-- ICON -->

                    <div class="notification-icon">

                        <i class="fa-solid <?php echo $icon; ?>"></i>

                    </div>


                    <!-- CONTENT -->

                    <div class="notification-content">


                        <h3>

                            <?php

                            echo htmlspecialchars(
                                $notification['title']
                            );

                            ?>

                        </h3>


                        <p>

                            <?php

                            echo nl2br(
                                htmlspecialchars(
                                    $notification['message']
                                )
                            );

                            ?>

                        </p>


                        <div class="notification-time">

                            <i class="fa-regular fa-clock"></i>

                            <?php

                            echo date(
                                "d M Y, h:i A",
                                strtotime(
                                    $notification['created_at']
                                )
                            );

                            ?>


                            <?php if ($notification['is_read'] == 0): ?>

                                • Unread

                            <?php endif; ?>


                        </div>


                    </div>


                    <!-- ACTIONS -->

                    <div class="notification-actions">


                        <?php if ($notification['is_read'] == 0): ?>

                            <a
                                href="notifications.php?read=<?php echo intval($notification['id']); ?>"
                                title="Mark as read"
                            >

                                <i class="fa-solid fa-check"></i>

                            </a>

                        <?php endif; ?>


                        <a
                            href="notifications.php?delete=<?php echo intval($notification['id']); ?>"
                            class="delete"
                            title="Delete"
                            onclick="return confirm('Delete this notification?');"
                        >

                            <i class="fa-solid fa-trash"></i>

                        </a>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="empty">

                <i class="fa-regular fa-bell-slash"></i>

                <h3>
                    No Notifications
                </h3>

                <p>
                    You don't have any notifications yet.
                </p>

            </div>


        <?php endif; ?>


    </div>


</main>


</div>


</body>

</html>