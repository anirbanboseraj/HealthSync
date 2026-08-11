<?php

session_start();

if (
    !isset($_SESSION['user_id']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] != 'admin'
) {
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");


// =====================================================
// GET DOCTOR ID
// =====================================================

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: doctors.php");
    exit();
}

$doctor_id = intval($_GET['id']);


// =====================================================
// FETCH DOCTOR
// =====================================================

$query = mysqli_query(
    $conn,
    "SELECT * FROM doctors WHERE id='$doctor_id'"
);

if (mysqli_num_rows($query) != 1) {
    header("Location: doctors.php");
    exit();
}

$doctor = mysqli_fetch_assoc($query);


// =====================================================
// UPDATE DOCTOR
// =====================================================

$error = "";
$success = "";

if (isset($_POST['update_doctor'])) {

    $fullname = mysqli_real_escape_string(
        $conn,
        $_POST['fullname']
    );

    $specialization = mysqli_real_escape_string(
        $conn,
        $_POST['specialization']
    );

    $email = mysqli_real_escape_string(
        $conn,
        $_POST['email']
    );

    $phone = mysqli_real_escape_string(
        $conn,
        $_POST['phone']
    );

    $experience = mysqli_real_escape_string(
        $conn,
        $_POST['experience']
    );

    $status = mysqli_real_escape_string(
        $conn,
        $_POST['status']
    );

    $qualification = mysqli_real_escape_string(
        $conn,
        $_POST['qualification']
    );

    $about = mysqli_real_escape_string(
        $conn,
        $_POST['about']
    );


    // =================================================
    // UPDATE IMAGE
    // =================================================

    $imageName = $doctor['image'];


    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] == 0
    ) {

        $uploadDirectory = "../assets/uploads/";

        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0777, true);
        }


        $originalName = $_FILES['image']['name'];

        $extension = strtolower(
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


        if (!in_array($extension, $allowedExtensions)) {

            $error = "Only JPG, JPEG, PNG and WEBP images are allowed.";

        } else {

            $imageName =
                "doctor_"
                . $doctor_id
                . "_"
                . time()
                . "."
                . $extension;


            $targetFile =
                $uploadDirectory
                . $imageName;


            if (
                !move_uploaded_file(
                    $_FILES['image']['tmp_name'],
                    $targetFile
                )
            ) {

                $error = "Unable to upload image.";

            }

        }

    }


    // =================================================
    // UPDATE DATABASE
    // =================================================

    if ($error == "") {

        $update = mysqli_query(
            $conn,
            "
            UPDATE doctors
            SET
                fullname='$fullname',
                specialization='$specialization',
                email='$email',
                phone='$phone',
                experience='$experience',
                status='$status',
                qualification='$qualification',
                about='$about',
                image='$imageName'
            WHERE id='$doctor_id'
            "
        );


        if ($update) {

            header(
                "Location: doctors.php?updated=1"
            );

            exit();

        } else {

            $error =
                "Database Error: "
                . mysqli_error($conn);

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
    Edit Doctor | HealthSync
</title>


<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
>


<style>

/* =====================================================
   RESET
===================================================== */

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}


body {

    font-family: Arial, sans-serif;

    background: #020617;

    color: #e2e8f0;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    position: fixed;

    left: 0;

    top: 0;

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

    font-size: 24px;

}


.logo span {

    color: #3b82f6;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    margin-bottom: 6px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    font-size: 14px;

    transition: 0.2s;

}


.sidebar a i {

    width: 20px;

    text-align: center;

}


.sidebar a:hover {

    background: #172554;

    color: #60a5fa;

}


.sidebar a.active {

    background: #1e3a8a;

    color: white;

}


.sidebar .logout {

    margin-top: 30px;

    color: #f87171;

}


.sidebar .logout:hover {

    background: #450a0a;

}


/* =====================================================
   MAIN
===================================================== */

.main {

    margin-left: 250px;

    min-height: 100vh;

    padding: 35px;

}


/* =====================================================
   HEADER
===================================================== */

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}


.header h1 {

    color: white;

    font-size: 27px;

}


.header p {

    color: #64748b;

    font-size: 13px;

    margin-top: 7px;

}


.back-btn {

    text-decoration: none;

    color: #94a3b8;

    background: #0f172a;

    border: 1px solid #1e293b;

    padding: 10px 15px;

    border-radius: 8px;

    font-size: 12px;

}


.back-btn:hover {

    color: white;

    background: #172554;

}


/* =====================================================
   ERROR
===================================================== */

.error {

    background: #450a0a;

    border: 1px solid #991b1b;

    color: #fca5a5;

    padding: 13px;

    border-radius: 8px;

    margin-bottom: 20px;

    font-size: 13px;

}


/* =====================================================
   FORM CONTAINER
===================================================== */

.form-container {

    max-width: 900px;

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    padding: 30px;

}


/* =====================================================
   PROFILE SECTION
===================================================== */

.profile-section {

    display: flex;

    align-items: center;

    gap: 20px;

    padding-bottom: 25px;

    margin-bottom: 25px;

    border-bottom: 1px solid #1e293b;

}


.profile-image {

    width: 85px;

    height: 85px;

    border-radius: 50%;

    object-fit: cover;

    border: 3px solid #1e3a8a;

}


.profile-placeholder {

    width: 85px;

    height: 85px;

    border-radius: 50%;

    background: #172554;

    color: #60a5fa;

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 30px;

}


.profile-section h3 {

    color: white;

    font-size: 18px;

}


.profile-section p {

    color: #64748b;

    font-size: 12px;

    margin-top: 5px;

}


/* =====================================================
   FORM GRID
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 20px;

}


.form-group {

    display: flex;

    flex-direction: column;

}


.form-group.full {

    grid-column: 1 / -1;

}


.form-group label {

    color: #94a3b8;

    font-size: 12px;

    margin-bottom: 8px;

}


.form-group input,
.form-group select,
.form-group textarea {

    width: 100%;

    background: #020617;

    color: #e2e8f0;

    border: 1px solid #334155;

    border-radius: 8px;

    padding: 12px;

    outline: none;

    font-size: 13px;

    font-family: Arial, sans-serif;

}


.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {

    border-color: #3b82f6;

    box-shadow: 0 0 0 2px rgba(59,130,246,0.12);

}


.form-group textarea {

    min-height: 110px;

    resize: vertical;

}


.form-group input[type="file"] {

    padding: 9px;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-buttons {

    display: flex;

    justify-content: flex-end;

    gap: 10px;

    margin-top: 30px;

    padding-top: 20px;

    border-top: 1px solid #1e293b;

}


.cancel-btn {

    text-decoration: none;

    padding: 12px 20px;

    border-radius: 8px;

    background: #1e293b;

    color: #cbd5e1;

    font-size: 13px;

}


.cancel-btn:hover {

    background: #334155;

}


.update-btn {

    border: none;

    padding: 12px 20px;

    border-radius: 8px;

    background: #2563eb;

    color: white;

    font-size: 13px;

    cursor: pointer;

}


.update-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:750px) {

    .sidebar {

        width: 210px;

    }

    .main {

        margin-left: 210px;

    }

    .form-grid {

        grid-template-columns: 1fr;

    }

    .form-group.full {

        grid-column: auto;

    }

}


@media(max-width:600px) {

    .sidebar {

        position: relative;

        width: 100%;

        height: auto;

    }

    .main {

        margin-left: 0;

        padding: 20px;

    }

    .header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }

}

</style>

</head>


<body>


<!-- =================================================
     SIDEBAR
================================================== -->

<aside class="sidebar">


    <div class="logo">

        <h2>
            Health<span>Sync</span>
        </h2>

    </div>


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


    <a href="#">

        <i class="fa-solid fa-chart-line"></i>

        Reports

    </a>


    <a href="#">

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


</aside>



<!-- =================================================
     MAIN
===================================================== -->

<main class="main">


    <div class="header">


        <div>

            <h1>
                Edit Doctor
            </h1>

            <p>
                Update doctor information and profile details.
            </p>

        </div>


        <a
            href="doctors.php"
            class="back-btn"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Back to Doctors

        </a>


    </div>



    <?php if ($error != "") { ?>

        <div class="error">

            <i class="fa-solid fa-circle-exclamation"></i>

            <?php echo $error; ?>

        </div>

    <?php } ?>



    <div class="form-container">


        <!-- PROFILE -->

        <div class="profile-section">


            <?php

            if (
                !empty($doctor['image']) &&
                file_exists(
                    "../assets/uploads/"
                    . $doctor['image']
                )
            ) {

            ?>

                <img
                    src="../assets/uploads/<?php echo htmlspecialchars($doctor['image']); ?>"
                    class="profile-image"
                    alt="Doctor"
                >

            <?php

            } else {

            ?>

                <div class="profile-placeholder">

                    <i class="fa-solid fa-user-doctor"></i>

                </div>

            <?php

            }

            ?>


            <div>

                <h3>

                    <?php

                    echo htmlspecialchars(
                        $doctor['fullname']
                    );

                    ?>

                </h3>


                <p>

                    Doctor ID:

                    #<?php echo $doctor['id']; ?>

                </p>

            </div>


        </div>



        <!-- FORM -->

        <form
            method="POST"
            enctype="multipart/form-data"
        >


            <div class="form-grid">


                <!-- NAME -->

                <div class="form-group">

                    <label>
                        Full Name
                    </label>

                    <input
                        type="text"
                        name="fullname"
                        value="<?php echo htmlspecialchars($doctor['fullname']); ?>"
                        required
                    >

                </div>



                <!-- SPECIALIZATION -->

                <div class="form-group">

                    <label>
                        Specialization
                    </label>

                    <input
                        type="text"
                        name="specialization"
                        value="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                        required
                    >

                </div>



                <!-- EMAIL -->

                <div class="form-group">

                    <label>
                        Email
                    </label>

                    <input
                        type="email"
                        name="email"
                        value="<?php echo htmlspecialchars($doctor['email']); ?>"
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
                        value="<?php echo htmlspecialchars($doctor['phone']); ?>"
                    >

                </div>



                <!-- EXPERIENCE -->

                <div class="form-group">

                    <label>
                        Experience (Years)
                    </label>

                    <input
                        type="number"
                        name="experience"
                        min="0"
                        value="<?php echo htmlspecialchars($doctor['experience']); ?>"
                    >

                </div>



                <!-- STATUS -->

                <div class="form-group">

                    <label>
                        Status
                    </label>

                    <select name="status">

                        <option
                            value="Available"
                            <?php
                            if ($doctor['status'] == "Available") {
                                echo "selected";
                            }
                            ?>
                        >
                            Available
                        </option>


                        <option
                            value="Unavailable"
                            <?php
                            if ($doctor['status'] == "Unavailable") {
                                echo "selected";
                            }
                            ?>
                        >
                            Unavailable
                        </option>

                    </select>

                </div>



                <!-- QUALIFICATION -->

                <div class="form-group full">

                    <label>
                        Qualification
                    </label>

                    <input
                        type="text"
                        name="qualification"
                        value="<?php echo htmlspecialchars($doctor['qualification']); ?>"
                        placeholder="Example: MBBS, FCPS"
                    >

                </div>



                <!-- ABOUT -->

                <div class="form-group full">

                    <label>
                        About Doctor
                    </label>

                    <textarea
                        name="about"
                        placeholder="Write a short description about the doctor..."
                    ><?php echo htmlspecialchars($doctor['about']); ?></textarea>

                </div>



                <!-- IMAGE -->

                <div class="form-group full">

                    <label>
                        Change Profile Picture
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


            </div>



            <!-- BUTTONS -->

            <div class="form-buttons">


                <a
                    href="doctors.php"
                    class="cancel-btn"
                >

                    Cancel

                </a>


                <button
                    type="submit"
                    name="update_doctor"
                    class="update-btn"
                >

                    <i class="fa-solid fa-save"></i>

                    Update Doctor

                </button>


            </div>


        </form>


    </div>


</main>


</body>

</html>