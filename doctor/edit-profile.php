<?php

session_start();

include("../config/database.php");


// =====================================================
// CHECK DOCTOR LOGIN
// =====================================================

if(
    !isset($_SESSION['user_id']) ||
    $_SESSION['role'] != 'doctor'
)
{
    header("Location: ../login.php");
    exit();
}


// =====================================================
// GET LOGGED-IN USER ID
// =====================================================

$user_id = $_SESSION['user_id'];


// =====================================================
// GET DOCTOR PROFILE
// IMPORTANT: USE user_id
// =====================================================

$query = mysqli_query(
    $conn,
    "
    SELECT *
    FROM doctors
    WHERE user_id='$user_id'
    LIMIT 1
    "
);


// =====================================================
// CHECK DOCTOR PROFILE
// =====================================================

if(mysqli_num_rows($query) == 0)
{
    die("
        <div style='
            font-family:Arial;
            padding:40px;
            text-align:center;
        '>
            <h2>Doctor profile not found.</h2>

            <p>
                Your user account is not connected
                to a doctor profile.
            </p>
        </div>
    ");
}


$doctor = mysqli_fetch_assoc($query);


// Doctor table ID

$doctor_id = $doctor['id'];


// Message variable

$message = "";


// =====================================================
// UPDATE PROFILE
// =====================================================

if(isset($_POST['update_profile']))
{

    // =================================================
    // GET FORM DATA
    // =================================================

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


    $qualification = mysqli_real_escape_string(
        $conn,
        $_POST['qualification']
    );


    $status = mysqli_real_escape_string(
        $conn,
        $_POST['status']
    );


    $about = mysqli_real_escape_string(
        $conn,
        $_POST['about']
    );


    // =================================================
    // KEEP OLD IMAGE
    // =================================================

    $imageName = $doctor['image'];


    // =================================================
    // PROFILE IMAGE UPLOAD
    // =================================================

    if(
        isset($_FILES['profile_image']) &&
        $_FILES['profile_image']['error'] == 0
    )
    {

        // ---------------------------------------------
        // Allowed image types
        // ---------------------------------------------

        $allowedTypes = array(
            'image/jpeg',
            'image/jpg',
            'image/png',
            'image/webp'
        );


        // ---------------------------------------------
        // Get file information
        // ---------------------------------------------

        $fileType = $_FILES['profile_image']['type'];

        $fileSize = $_FILES['profile_image']['size'];


        // ---------------------------------------------
        // Check image type
        // ---------------------------------------------

        if(!in_array($fileType, $allowedTypes))
        {

            $message = "
                <div class='error'>
                    <i class='fa-solid fa-circle-xmark'></i>

                    Only JPG, PNG and WEBP images
                    are allowed.
                </div>
            ";

        }


        // ---------------------------------------------
        // Check image size
        // ---------------------------------------------

        elseif($fileSize > 2 * 1024 * 1024)
        {

            $message = "
                <div class='error'>
                    <i class='fa-solid fa-circle-xmark'></i>

                    Image size must be less than 2MB.
                </div>
            ";

        }


        else
        {

            // -----------------------------------------
            // Get extension
            // -----------------------------------------

            $extension = strtolower(
                pathinfo(
                    $_FILES['profile_image']['name'],
                    PATHINFO_EXTENSION
                )
            );


            // -----------------------------------------
            // Create unique filename
            // -----------------------------------------

            $newFileName =
                'doctor_' .
                $doctor_id .
                '_' .
                time() .
                '.' .
                $extension;


            // -----------------------------------------
            // Upload destination
            // -----------------------------------------

            $uploadPath =
                "../assets/images/doctors/" .
                $newFileName;


            // -----------------------------------------
            // Move uploaded file
            // -----------------------------------------

            if(
                move_uploaded_file(
                    $_FILES['profile_image']['tmp_name'],
                    $uploadPath
                )
            )
            {

                // -------------------------------------
                // Delete old image
                // -------------------------------------

                if(
                    !empty($doctor['image']) &&
                    file_exists(
                        "../assets/images/doctors/" .
                        $doctor['image']
                    )
                )
                {

                    unlink(
                        "../assets/images/doctors/" .
                        $doctor['image']
                    );

                }


                // -------------------------------------
                // Use new image
                // -------------------------------------

                $imageName = $newFileName;

            }
            else
            {

                $message = "
                    <div class='error'>
                        <i class='fa-solid fa-circle-xmark'></i>

                        Failed to upload image.
                    </div>
                ";

            }

        }

    }


    // =================================================
    // IF NO IMAGE ERROR
    // =================================================

    if($message == "")
    {

        // =================================================
        // UPDATE DOCTORS TABLE
        // =================================================

        $updateDoctor = mysqli_query(
            $conn,
            "
            UPDATE doctors

            SET

                fullname='$fullname',

                specialization='$specialization',

                email='$email',

                phone='$phone',

                experience='$experience',

                qualification='$qualification',

                status='$status',

                about='$about',

                image='$imageName'

            WHERE id='$doctor_id'
            "
        );


        // =================================================
        // UPDATE USERS TABLE
        // =================================================

        $updateUser = mysqli_query(
            $conn,
            "
            UPDATE users

            SET

                fullname='$fullname',

                email='$email'

            WHERE id='$user_id'
            "
        );


        // =================================================
        // CHECK UPDATE
        // =================================================

        if(
            $updateDoctor &&
            $updateUser
        )
        {

            // Update session name/email

            $_SESSION['fullname'] = $fullname;

            $_SESSION['email'] = $email;


            $message = "
                <div class='success'>

                    <i class='fa-solid fa-circle-check'></i>

                    Profile updated successfully!

                </div>
            ";


            // Refresh doctor data

            $query = mysqli_query(
                $conn,
                "
                SELECT *
                FROM doctors
                WHERE user_id='$user_id'
                LIMIT 1
                "
            );


            $doctor = mysqli_fetch_assoc($query);

        }
        else
        {

            $message = "
                <div class='error'>

                    <i class='fa-solid fa-circle-xmark'></i>

                    Something went wrong while
                    updating your profile.

                </div>
            ";

        }

    }

}


// =====================================================
// HEADER
// =====================================================

include("../includes/header.php");


// =====================================================
// NAVBAR
// =====================================================

include("../includes/navbar.php");

?>



<!-- =====================================================
     PAGE
===================================================== -->

<div class="profile-page">


    <!-- =================================================
         SIDEBAR
    ================================================== -->

    <aside class="sidebar">

        <div class="sidebar-logo">

            <h2>
                Health<span>Sync</span>
            </h2>

        </div>


        <ul>

            <li>

                <a href="dashboard.php">

                    <i class="fa-solid fa-house"></i>

                    Dashboard

                </a>

            </li>


            <li>

                <a href="appointments.php">

                    <i class="fa-solid fa-calendar-check"></i>

                    Appointments

                </a>

            </li>


            <li>

                <a href="prescriptions.php">

                    <i class="fa-solid fa-file-prescription"></i>

                    Prescriptions

                </a>

            </li>


            <li class="active">

                <a href="profile.php">

                    <i class="fa-solid fa-user-doctor"></i>

                    Profile

                </a>

            </li>


            <li>

                <a href="logout.php">

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            </li>

        </ul>

    </aside>



    <!-- =================================================
         MAIN CONTENT
    ================================================== -->

    <main class="profile-content">


        <div class="page-header">

            <div>

                <h1>Edit Profile</h1>

                <p>
                    Manage your HealthSync doctor profile.
                </p>

            </div>


            <a
                href="profile.php"
                class="back-btn"
            >

                <i class="fa-solid fa-arrow-left"></i>

                Back to Profile

            </a>

        </div>



        <!-- =================================================
             MESSAGE
        ================================================== -->

        <?php echo $message; ?>



        <!-- =================================================
             PROFILE CARD
        ================================================== -->

        <div class="edit-card">


            <form
                method="POST"
                enctype="multipart/form-data"
            >


                <!-- =========================================
                     PROFILE PICTURE
                ========================================== -->

                <div class="profile-picture-section">


                    <div class="current-picture">


                        <?php if(!empty($doctor['image'])) { ?>


                            <img
                                src="../assets/images/doctors/<?php echo htmlspecialchars($doctor['image']); ?>"
                                alt="Doctor Profile"
                                id="profilePreview"
                            >


                        <?php } else { ?>


                            <div
                                class="default-profile"
                                id="profilePreview"
                            >

                                <i class="fa-solid fa-user-doctor"></i>

                            </div>


                        <?php } ?>


                    </div>



                    <div class="picture-info">


                        <h3>
                            Profile Picture
                        </h3>


                        <p>
                            Upload a professional picture
                            for your doctor profile.
                        </p>


                        <label
                            for="profile_image"
                            class="upload-btn"
                        >

                            <i class="fa-solid fa-camera"></i>

                            Choose New Picture

                        </label>


                        <input
                            type="file"
                            name="profile_image"
                            id="profile_image"
                            accept="image/jpeg,image/png,image/jpg,image/webp"
                            hidden
                        >


                        <small>

                            JPG, PNG or WEBP
                            • Maximum 2MB

                        </small>


                    </div>


                </div>



                <!-- =========================================
                     FORM GRID
                ========================================== -->

                <div class="form-grid">


                    <!-- Full Name -->

                    <div class="input-group">

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



                    <!-- Specialization -->

                    <div class="input-group">

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



                    <!-- Email -->

                    <div class="input-group">

                        <label>
                            Email Address
                        </label>

                        <input
                            type="email"
                            name="email"
                            value="<?php echo htmlspecialchars($doctor['email']); ?>"
                            required
                        >

                    </div>



                    <!-- Phone -->

                    <div class="input-group">

                        <label>
                            Phone Number
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php echo htmlspecialchars($doctor['phone']); ?>"
                        >

                    </div>



                    <!-- Qualification -->

                    <div class="input-group">

                        <label>
                            Qualification
                        </label>

                        <input
                            type="text"
                            name="qualification"
                            value="<?php echo htmlspecialchars($doctor['qualification']); ?>"
                        >

                    </div>



                    <!-- Experience -->

                    <div class="input-group">

                        <label>
                            Experience
                        </label>

                        <input
                            type="number"
                            name="experience"
                            value="<?php echo htmlspecialchars($doctor['experience']); ?>"
                            min="0"
                        >

                    </div>



                    <!-- Status -->

                    <div class="input-group">

                        <label>
                            Availability
                        </label>


                        <select name="status">


                            <option
                                value="Available"
                                <?php
                                if($doctor['status'] == 'Available')
                                {
                                    echo 'selected';
                                }
                                ?>
                            >

                                Available

                            </option>


                            <option
                                value="Unavailable"
                                <?php
                                if($doctor['status'] == 'Unavailable')
                                {
                                    echo 'selected';
                                }
                                ?>
                            >

                                Unavailable

                            </option>


                        </select>

                    </div>


                </div>



                <!-- =========================================
                     ABOUT
                ========================================== -->

                <div class="input-group about-group">

                    <label>
                        About Doctor
                    </label>


                    <textarea
                        name="about"
                        rows="6"
                        placeholder="Write something about yourself..."
                    ><?php echo htmlspecialchars($doctor['about']); ?></textarea>

                </div>



                <!-- =========================================
                     BUTTONS
                ========================================== -->

                <div class="form-buttons">


                    <a
                        href="profile.php"
                        class="cancel-btn"
                    >

                        Cancel

                    </a>


                    <button
                        type="submit"
                        name="update_profile"
                        class="save-btn"
                    >

                        <i class="fa-solid fa-floppy-disk"></i>

                        Save Changes

                    </button>


                </div>


            </form>


        </div>


    </main>

</div>



<!-- =====================================================
     IMAGE PREVIEW JAVASCRIPT
===================================================== -->

<script>

const imageInput =
    document.getElementById("profile_image");


imageInput.addEventListener(
    "change",
    function(event)
    {

        const file =
            event.target.files[0];


        if(!file)
        {
            return;
        }


        // Check size

        if(
            file.size >
            2 * 1024 * 1024
        )
        {

            alert(
                "Image must be less than 2MB."
            );


            imageInput.value = "";


            return;

        }


        // Check type

        const allowedTypes = [

            "image/jpeg",

            "image/jpg",

            "image/png",

            "image/webp"

        ];


        if(
            !allowedTypes.includes(
                file.type
            )
        )
        {

            alert(
                "Only JPG, PNG and WEBP images are allowed."
            );


            imageInput.value = "";


            return;

        }


        // Read image

        const reader =
            new FileReader();


        reader.onload =
            function(e)
            {

                const oldPreview =
                    document.getElementById(
                        "profilePreview"
                    );


                const newImage =
                    document.createElement(
                        "img"
                    );


                newImage.src =
                    e.target.result;


                newImage.id =
                    "profilePreview";


                newImage.alt =
                    "Doctor Profile";


                oldPreview.replaceWith(
                    newImage
                );

            };


        reader.readAsDataURL(file);

    }

);

</script>



<!-- =====================================================
     PAGE CSS
===================================================== -->

<style>

/* =====================================================
   PAGE
===================================================== */

.profile-page {

    display: flex;

    min-height: 100vh;

    background: #020617;

    color: #e2e8f0;

}


/* =====================================================
   SIDEBAR
===================================================== */

.sidebar {

    width: 250px;

    min-height: 100vh;

    background: #0f172a;

    border-right: 1px solid #1e293b;

    padding: 25px 15px;

}


.sidebar-logo {

    text-align: center;

    margin-bottom: 35px;

}


.sidebar-logo h2 {

    color: white;

    font-size: 25px;

}


.sidebar-logo span {

    color: #3b82f6;

}


.sidebar ul {

    list-style: none;

    padding: 0;

    margin: 0;

}


.sidebar li {

    margin-bottom: 8px;

}


.sidebar a {

    display: flex;

    align-items: center;

    gap: 13px;

    padding: 13px 15px;

    color: #94a3b8;

    text-decoration: none;

    border-radius: 8px;

    transition: .3s;

}


.sidebar a:hover,
.sidebar li.active a {

    background: #1e3a8a;

    color: white;

}


.sidebar i {

    width: 20px;

}


/* =====================================================
   MAIN CONTENT
===================================================== */

.profile-content {

    flex: 1;

    padding: 40px;

    max-width: 1200px;

}


/* =====================================================
   HEADER
===================================================== */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 30px;

}


.page-header h1 {

    margin: 0;

    color: white;

    font-size: 30px;

}


.page-header p {

    margin-top: 8px;

    color: #94a3b8;

}


.back-btn {

    text-decoration: none;

    color: #93c5fd;

    border: 1px solid #334155;

    padding: 10px 16px;

    border-radius: 7px;

}


.back-btn:hover {

    background: #1e3a8a;

    color: white;

}


/* =====================================================
   MESSAGES
===================================================== */

.success,
.error {

    padding: 14px 18px;

    margin-bottom: 20px;

    border-radius: 8px;

}


.success {

    background: #064e3b;

    color: #6ee7b7;

    border: 1px solid #047857;

}


.error {

    background: #450a0a;

    color: #fca5a5;

    border: 1px solid #991b1b;

}


/* =====================================================
   EDIT CARD
===================================================== */

.edit-card {

    background: #0f172a;

    border: 1px solid #1e293b;

    border-radius: 14px;

    padding: 30px;

}


/* =====================================================
   PROFILE PICTURE
===================================================== */

.profile-picture-section {

    display: flex;

    align-items: center;

    gap: 25px;

    padding: 22px;

    margin-bottom: 30px;

    background: #111827;

    border: 1px solid #334155;

    border-radius: 12px;

}


.current-picture {

    width: 110px;

    height: 110px;

    flex-shrink: 0;

}


.current-picture img {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    object-fit: cover;

    border: 4px solid #3b82f6;

}


.default-profile {

    width: 110px;

    height: 110px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background: #172554;

    border: 4px solid #3b82f6;

    color: #60a5fa;

    font-size: 42px;

}


.picture-info h3 {

    margin: 0 0 8px;

    color: white;

}


.picture-info p {

    margin: 0 0 15px;

    color: #94a3b8;

    font-size: 14px;

}


.upload-btn {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 10px 16px;

    background: #2563eb;

    color: white;

    border-radius: 7px;

    cursor: pointer;

    transition: .3s;

}


.upload-btn:hover {

    background: #1d4ed8;

}


.picture-info small {

    display: block;

    margin-top: 8px;

    color: #64748b;

}


/* =====================================================
   FORM GRID
===================================================== */

.form-grid {

    display: grid;

    grid-template-columns: repeat(2, 1fr);

    gap: 20px;

}


.input-group {

    display: flex;

    flex-direction: column;

    gap: 8px;

}


.input-group label {

    color: #cbd5e1;

    font-size: 14px;

    font-weight: 600;

}


.input-group input,
.input-group select,
.input-group textarea {

    width: 100%;

    padding: 12px 14px;

    background: #020617;

    border: 1px solid #334155;

    border-radius: 7px;

    color: white;

    outline: none;

    font-size: 14px;

}


.input-group input:focus,
.input-group select:focus,
.input-group textarea:focus {

    border-color: #3b82f6;

    box-shadow: 0 0 0 2px
        rgba(59,130,246,.15);

}


.about-group {

    margin-top: 20px;

}


.input-group textarea {

    resize: vertical;

}


/* =====================================================
   BUTTONS
===================================================== */

.form-buttons {

    display: flex;

    justify-content: flex-end;

    gap: 12px;

    margin-top: 30px;

}


.cancel-btn {

    padding: 12px 20px;

    border-radius: 7px;

    border: 1px solid #475569;

    color: #cbd5e1;

    text-decoration: none;

}


.cancel-btn:hover {

    background: #1e293b;

}


.save-btn {

    border: none;

    padding: 12px 20px;

    border-radius: 7px;

    background: #2563eb;

    color: white;

    cursor: pointer;

    font-size: 14px;

}


.save-btn:hover {

    background: #1d4ed8;

}


/* =====================================================
   RESPONSIVE
===================================================== */

@media(max-width:800px)
{

    .sidebar {

        width: 200px;

    }


    .profile-content {

        padding: 25px;

    }


    .form-grid {

        grid-template-columns: 1fr;

    }

}


@media(max-width:600px)
{

    .profile-page {

        flex-direction: column;

    }


    .sidebar {

        width: 100%;

        min-height: auto;

    }


    .sidebar ul {

        display: flex;

        flex-wrap: wrap;

        gap: 5px;

    }


    .sidebar li {

        margin: 0;

    }


    .profile-content {

        padding: 20px;

    }


    .page-header {

        flex-direction: column;

        align-items: flex-start;

        gap: 15px;

    }


    .profile-picture-section {

        flex-direction: column;

        text-align: center;

    }


    .form-buttons {

        flex-direction: column;

    }


    .cancel-btn,
    .save-btn {

        text-align: center;

        width: 100%;

    }

}

</style>



<?php

include("../includes/footer.php");

?>