<?php

session_start();

require_once "../config/database.php";

/* =====================================================
   ADMIN CHECK
===================================================== */
if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'admin'
) {
    header("Location: ../login.php");
    exit();
}


/* =====================================================
   ADMIN NAME
===================================================== */

$admin_name = $_SESSION['fullname'] ?? 'Administrator';


/* =====================================================
   VARIABLES
===================================================== */

$message = "";
$messageType = "";


/* =====================================================
   FORM SUBMISSION
===================================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $specialization = trim($_POST['specialization'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $status = trim($_POST['status'] ?? 'Available');
    $password = $_POST['password'] ?? '';


    /* =================================================
       VALIDATION
    ================================================= */

    if (
        $fullname === "" ||
        $email === "" ||
        $specialization === "" ||
        $password === ""
    ) {

        $message = "Please fill in all required fields.";
        $messageType = "error";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    }

    else {


        /* =============================================
           CHECK EXISTING EMAIL
        ============================================= */

        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM doctors WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "s",
            $email
        );

        mysqli_stmt_execute($check);

        $result = mysqli_stmt_get_result($check);


        if (mysqli_num_rows($result) > 0) {

            $message = "A doctor with this email already exists.";
            $messageType = "error";

        }

        else {


            /* =========================================
               HASH PASSWORD
            ========================================= */

            $hashedPassword = password_hash(
                $password,
                PASSWORD_DEFAULT
            );


            /* =========================================
               IMAGE UPLOAD
            ========================================= */

            $imageName = "";


            if (
                isset($_FILES['image']) &&
                $_FILES['image']['error'] === UPLOAD_ERR_OK
            ) {


                $uploadDirectory =
                    "../assets/images/doctors/";


                /* Create folder if missing */

                if (!is_dir($uploadDirectory)) {

                    mkdir(
                        $uploadDirectory,
                        0777,
                        true
                    );

                }


                $originalName =
                    $_FILES['image']['name'];


                $tmpName =
                    $_FILES['image']['tmp_name'];


                $fileSize =
                    $_FILES['image']['size'];


                $extension =
                    strtolower(
                        pathinfo(
                            $originalName,
                            PATHINFO_EXTENSION
                        )
                    );


                $allowedExtensions = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (
                    !in_array(
                        $extension,
                        $allowedExtensions
                    )
                ) {

                    $message =
                        "Only JPG, JPEG, PNG and WEBP images are allowed.";

                    $messageType = "error";

                }

                elseif ($fileSize > 5 * 1024 * 1024) {

                    $message =
                        "Image size must be less than 5MB.";

                    $messageType = "error";

                }

                else {


                    $imageName =
                        "doctor_" .
                        time() .
                        "_" .
                        bin2hex(
                            random_bytes(4)
                        ) .
                        "." .
                        $extension;


                    $destination =
                        $uploadDirectory .
                        $imageName;


                    if (
                        !move_uploaded_file(
                            $tmpName,
                            $destination
                        )
                    ) {

                        $message =
                            "Failed to upload doctor image.";

                        $messageType = "error";

                        $imageName = "";

                    }

                }

            }


            /* =========================================
               INSERT DOCTOR
            ========================================= */

            if ($message === "") {


                $sql = "
                    INSERT INTO doctors
                    (
                        fullname,
                        email,
                        password,
                        phone,
                        specialization,
                        experience,
                        status,
                        image
                    )
                    VALUES
                    (?, ?, ?, ?, ?, ?, ?, ?)
                ";


                $stmt = mysqli_prepare(
                    $conn,
                    $sql
                );


                if ($stmt) {


                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssssss",
                        $fullname,
                        $email,
                        $hashedPassword,
                        $phone,
                        $specialization,
                        $experience,
                        $status,
                        $imageName
                    );


                    if (
                        mysqli_stmt_execute($stmt)
                    ) {

                        $message =
                            "Doctor added successfully.";

                        $messageType =
                            "success";


                        /* Clear form */

                        $fullname = "";
                        $email = "";
                        $phone = "";
                        $specialization = "";
                        $experience = "";

                    }

                    else {

                        $message =
                            "Failed to add doctor: " .
                            mysqli_error($conn);

                        $messageType =
                            "error";

                    }


                    mysqli_stmt_close($stmt);

                }

                else {

                    $message =
                        "Database query could not be prepared.";

                    $messageType =
                        "error";

                }

            }

        }


        mysqli_stmt_close($check);

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
    Add Doctor - HealthSync
</title>


<!-- Font Awesome -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css"
>


<!-- Existing Admin CSS -->

<link
    rel="stylesheet"
    href="../assets/css/admin.css"
>


<style>

/* =====================================================
   PAGE
===================================================== */

.add-doctor-page {

    width: 100%;

}


/* =====================================================
   TOPBAR
===================================================== */

.topbar {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.page-title h1 {

    margin: 0;

    color: #ffffff;

}


.page-title p {

    margin-top: 7px;

    color: #94a3b8;

}


/* =====================================================
   PROFILE
===================================================== */

.admin-profile {

    display: flex;

    align-items: center;

    gap: 12px;

}


.admin-icon {

    width: 45px;

    height: 45px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #1d4ed8;

    color: white;

}


.admin-profile strong {

    display: block;

    color: white;

}


.admin-profile small {

    color: #94a3b8;

}


/* =====================================================
   MESSAGE
===================================================== */

.message {

    padding: 14px 18px;

    border-radius: 10px;

    margin-bottom: 20px;

    display: flex;

    align-items: center;

    gap: 10px;

}


.message.success {

    background: rgba(34,197,94,.12);

    border: 1px solid rgba(34,197,94,.25);

    color: #4ade80;

}


.message.error {

    background: rgba(239,68,68,.12);

    border: 1px solid rgba(239,68,68,.25);

    color: #f87171;

}


/* =====================================================
   FORM CARD
===================================================== */

.form-card {

    background: #111a2d;

    border: 1px solid #263553;

    border-radius: 15px;

    padding: 30px;

    max-width: 1000px;

}


.form-header {

    margin-bottom: 28px;

}


.form-header h2 {

    color: #ffffff;

    margin-bottom: 7px;

}


.form-header p {

    color: #94a3b8;

}


/* =====================================================
   GRID
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 20px;

}


.form-group {

    display: flex;

    flex-direction: column;

    gap: 8px;

}


.form-group.full {

    grid-column: 1 / -1;

}


.form-group label {

    color: #cbd5e1;

    font-size: 14px;

    font-weight: 600;

}


.form-group label span {

    color: #f87171;

}


/* =====================================================
   INPUTS
===================================================== */

.form-group input,
.form-group select {

    width: 100%;

    padding: 12px 14px;

    border-radius: 9px;

    border: 1px solid #263553;

    background: #0b1325;

    color: #ffffff;

    outline: none;

    font-size: 14px;

}


.form-group input:focus,
.form-group select:focus {

    border-color: #3b82f6;

    box-shadow:
        0 0 0 3px
        rgba(59,130,246,.10);

}


.form-group select option {

    background: #0b1325;

    color: white;

}


/* =====================================================
   FILE
===================================================== */

.form-group input[type="file"] {

    padding: 10px;

    cursor: pointer;

}


.file-help {

    font-size: 12px;

    color: #64748b;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-actions {

    display: flex;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 30px;

    padding-top: 20px;

    border-top: 1px solid #263553;

}


.cancel-btn,
.save-btn {

    padding: 12px 20px;

    border-radius: 9px;

    text-decoration: none;

    border: none;

    cursor: pointer;

    font-weight: 600;

    display: inline-flex;

    align-items: center;

    gap: 8px;

}


.cancel-btn {

    background: #1e293b;

    color: #cbd5e1;

}


.cancel-btn:hover {

    background: #334155;

}


.save-btn {

    background: #2563eb;

    color: white;

}


.save-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media (max-width: 700px) {

    .topbar {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .form-grid {

        grid-template-columns: 1fr;

    }


    .form-group.full {

        grid-column: auto;

    }


    .form-card {

        padding: 20px;

    }


    .form-actions {

        flex-direction: column;

    }


    .cancel-btn,
    .save-btn {

        justify-content: center;

    }

}

</style>

</head>


<body>


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


    <nav>


        <a href="dashboard.php">

            <i class="fa-solid fa-house"></i>

            Dashboard

        </a>


        <a
            href="doctors.php"
            class="active"
        >

            <i class="fa-solid fa-user-doctor"></i>

            Doctors

        </a>


        <a href="patients.php">

            <i class="fa-solid fa-users"></i>

            Patients

        </a>


        <a href="appointments.php">

            <i class="fa-solid fa-calendar-check"></i>

            Appointments

        </a>


        <a href="prescriptions.php">

            <i class="fa-solid fa-file-prescription"></i>

            Prescriptions

        </a>


        <a href="reports.php">

            <i class="fa-solid fa-chart-line"></i>

            Reports

        </a>


        <a href="settings.php">

            <i class="fa-solid fa-gear"></i>

            Settings

        </a>


        <a
            href="../logout.php"
            class="logout"
        >

            <i class="fa-solid fa-right-from-bracket"></i>

            Logout

        </a>


    </nav>

</aside>



<!-- =====================================================
     MAIN CONTENT
===================================================== -->

<main class="main-content">


<div class="add-doctor-page">


    <!-- TOPBAR -->

    <div class="topbar">


        <div class="page-title">

            <h1>
                Add Doctor
            </h1>

            <p>
                Register a new doctor in HealthSync.
            </p>

        </div>


        <div class="admin-profile">


            <div class="admin-icon">

                <i class="fa-solid fa-user-shield"></i>

            </div>


            <div>

                <strong>

                    <?php
                    echo htmlspecialchars(
                        $admin_name
                    );
                    ?>

                </strong>

                <small>
                    Administrator
                </small>

            </div>


        </div>


    </div>



    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div
            class="message
            <?php echo htmlspecialchars($messageType); ?>"
        >

            <?php if ($messageType === "success"): ?>

                <i class="fa-solid fa-circle-check"></i>

            <?php else: ?>

                <i class="fa-solid fa-circle-exclamation"></i>

            <?php endif; ?>


            <span>

                <?php
                echo htmlspecialchars($message);
                ?>

            </span>

        </div>

    <?php endif; ?>



    <!-- FORM -->

    <div class="form-card">


        <div class="form-header">

            <h2>

                <i class="fa-solid fa-user-doctor"></i>

                Doctor Information

            </h2>

            <p>
                Enter the doctor's account and professional details.
            </p>

        </div>



        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label>

                        Full Name
                        <span>*</span>

                    </label>

                    <input
                        type="text"
                        name="fullname"
                        value="<?php echo htmlspecialchars($fullname ?? ''); ?>"
                        placeholder="Dr. John Doe"
                        required
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label>

                        Email
                        <span>*</span>

                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($email ?? ''); ?>"
                        placeholder="doctor@example.com"
                        required
                    >

                </div>



                <!-- PHONE -->

                <div class="form-group">

                    <label>
                        Phone
                    </label>

                    <input
                        type="text"
                        name="phone"
                        value="<?php echo htmlspecialchars($phone ?? ''); ?>"
                        placeholder="01XXXXXXXXX"
                    >

                </div>



                <!-- SPECIALIZATION -->

                <div class="form-group">

                    <label>

                        Specialization
                        <span>*</span>

                    </label>

                    <input
                        type="text"
                        name="specialization"
                        value="<?php echo htmlspecialchars($specialization ?? ''); ?>"
                        placeholder="Cardiologist"
                        required
                    >

                </div>



                <!-- EXPERIENCE -->

                <div class="form-group">

                    <label>
                        Experience
                    </label>

                    <input
                        type="number"
                        name="experience"
                        min="0"
                        max="60"
                        value="<?php echo htmlspecialchars($experience ?? ''); ?>"
                        placeholder="5"
                    >

                </div>



                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option value="Available">
                            Available
                        </option>

                        <option value="Unavailable">
                            Unavailable
                        </option>

                    </select>

                </div>



                <!-- PASSWORD -->

                <div class="form-group">

                    <label>

                        Password
                        <span>*</span>

                    </label>

                    <input
                        type="password"
                        name="password"
                        placeholder="Create doctor password"
                        required
                    >

                </div>



                <!-- IMAGE -->

                <div class="form-group">

                    <label>
                        Doctor Photo
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                    <small class="file-help">
                        JPG, JPEG, PNG or WEBP. Maximum 5MB.
                    </small>

                </div>


            </div>



            <!-- ACTIONS -->

            <div class="form-actions">


                <a
                    href="doctors.php"
                    class="cancel-btn"
                >

                    <i class="fa-solid fa-arrow-left"></i>

                    Cancel

                </a>


                <button
                    type="submit"
                    class="save-btn"
                >

                    <i class="fa-solid fa-user-plus"></i>

                    Add Doctor

                </button>


            </div>


        </form>


    </div>


</div>


</main>


</body>

</html>