<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$user_id = $_SESSION['user_id'];

/* Get current patient information */
$query = mysqli_query($conn, "
    SELECT *
    FROM users
    WHERE id = '$user_id'
    AND role = 'patient'
    LIMIT 1
");

if (!$query || mysqli_num_rows($query) == 0) {
    die("Patient profile not found.");
}

$user = mysqli_fetch_assoc($query);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>My Profile | HealthSync</title>

    <!-- Main Dashboard CSS -->
    <link rel="stylesheet"
          href="../assets/css/dashboard.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

    <style>

        /* =========================
           PROFILE PAGE
        ========================== */

        .profile-page {
            max-width: 1100px;
            margin: 0 auto;
        }

        .profile-header {
            margin-bottom: 25px;
        }

        .profile-header h1 {
            margin-bottom: 8px;
        }

        .profile-header p {
            color: #777;
        }

        /* Profile Card */

        .profile-container {
            background: #ffffff;
            border-radius: 18px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        /* Top section */

        .profile-top {
            display: flex;
            align-items: center;
            gap: 25px;
            padding-bottom: 25px;
            margin-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .profile-image-wrapper {
            width: 110px;
            height: 110px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid #e8f5f2;
            flex-shrink: 0;
        }

        .profile-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-name h2 {
            margin: 0 0 7px;
            color: #222;
        }

        .profile-name p {
            margin: 0;
            color: #777;
        }

        /* Form */

        .profile-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 22px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group.full {
            grid-column: 1 / -1;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 8px;
            color: #333;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 13px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            font-size: 15px;
            outline: none;
            background: #fafafa;
            transition: 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #19a88b;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(25,168,139,0.1);
        }

        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }

        /* Email */

        .readonly-input {
            background: #eeeeee !important;
            cursor: not-allowed;
        }

        /* Gender */

        .gender-options {
            display: flex;
            gap: 25px;
            align-items: center;
            height: 45px;
        }

        .gender-option {
            display: flex;
            align-items: center;
            gap: 7px;
            font-weight: normal !important;
            cursor: pointer;
        }

        .gender-option input {
            width: auto;
        }

        /* Buttons */

        .profile-actions {
            grid-column: 1 / -1;
            display: flex;
            gap: 15px;
            margin-top: 10px;
            padding-top: 25px;
            border-top: 1px solid #eee;
        }

        .save-btn,
        .password-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 13px 22px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
        }

        .save-btn {
            border: none;
            background: #19a88b;
            color: white;
        }

        .save-btn:hover {
            background: #148c75;
            transform: translateY(-1px);
        }

        .password-btn {
            background: #f1f1f1;
            color: #333;
        }

        .password-btn:hover {
            background: #e4e4e4;
        }

        /* Success/Error */

        .message {
            padding: 13px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 14px;
        }

        .success {
            background: #e8f8f3;
            color: #168267;
        }

        .error {
            background: #ffecec;
            color: #c0392b;
        }

        /* Mobile */

        @media (max-width: 768px) {

            .profile-container {
                padding: 20px;
            }

            .profile-form {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: auto;
            }

            .profile-actions {
                grid-column: auto;
                flex-direction: column;
            }

            .profile-top {
                flex-direction: column;
                text-align: center;
            }

            .save-btn,
            .password-btn {
                width: 100%;
            }
        }

    </style>

</head>


<body>

<div class="dashboard">


    <!-- =========================
         PATIENT SIDEBAR
    ========================== -->

    <?php include("../includes/patient-sidebar.php"); ?>


    <!-- =========================
         MAIN CONTENT
    ========================== -->

    <main class="main-content">

        <div class="profile-page">


            <!-- Header -->

            <div class="profile-header">

                <h1>
                    My Profile
                </h1>

                <p>
                    Manage your personal information and account details.
                </p>

            </div>


            <!-- Profile Container -->

            <div class="profile-container">


                <!-- Profile Top -->

                <div class="profile-top">

                    <div class="profile-image-wrapper">

                        <?php

                        if (!empty($user['image'])) {

                            $image_path =
                                "../assets/uploads/profile/" .
                                $user['image'];

                        } else {

                            $image_path =
                                "../assets/images/default-profile.png";

                        }

                        ?>

                        <img
                            src="<?php echo htmlspecialchars($image_path); ?>"
                            alt="Profile Picture"
                        >

                    </div>


                    <div class="profile-name">

                        <h2>
                            <?php
                            echo htmlspecialchars($user['fullname']);
                            ?>
                        </h2>

                        <p>
                            <i class="fa-solid fa-user"></i>
                            Patient
                        </p>

                    </div>

                </div>


                <!-- Messages -->

                <?php if (isset($_GET['success'])): ?>

                    <div class="message success">

                        <i class="fa-solid fa-circle-check"></i>

                        Profile updated successfully.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET['error'])): ?>

                    <div class="message error">

                        <i class="fa-solid fa-circle-exclamation"></i>

                        Unable to update profile.

                    </div>

                <?php endif; ?>


                <!-- Profile Form -->

                <form
                    action="update-profile.php"
                    method="POST"
                    enctype="multipart/form-data"
                    class="profile-form"
                >


                    <!-- Full Name -->

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


                    <!-- Email -->

                    <div class="form-group">

                        <label>
                            Email
                        </label>

                        <input
                            type="email"
                            value="<?php echo htmlspecialchars($user['email']); ?>"
                            class="readonly-input"
                            readonly
                        >

                    </div>


                    <!-- Phone -->

                    <div class="form-group">

                        <label>
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                            placeholder="Enter phone number"
                        >

                    </div>


                    <!-- Gender -->

                    <div class="form-group">

                        <label>
                            Gender
                        </label>

                        <div class="gender-options">

                            <label class="gender-option">

                                <input
                                    type="radio"
                                    name="gender"
                                    value="Male"

                                    <?php
                                    if (($user['gender'] ?? '') == 'Male') {
                                        echo 'checked';
                                    }
                                    ?>
                                >

                                Male

                            </label>


                            <label class="gender-option">

                                <input
                                    type="radio"
                                    name="gender"
                                    value="Female"

                                    <?php
                                    if (($user['gender'] ?? '') == 'Female') {
                                        echo 'checked';
                                    }
                                    ?>
                                >

                                Female

                            </label>

                        </div>

                    </div>


                    <!-- Date of Birth -->

                    <div class="form-group">

                        <label>
                            Date of Birth
                        </label>

                        <input
                            type="date"
                            name="dob"
                            value="<?php echo htmlspecialchars($user['dob'] ?? ''); ?>"
                        >

                    </div>


                    <!-- Blood Group -->

                    <div class="form-group">

                        <label>
                            Blood Group
                        </label>

                        <select name="blood_group">

                            <option value="">
                                Select Blood Group
                            </option>

                            <?php

                            $blood_groups = [
                                "A+",
                                "A-",
                                "B+",
                                "B-",
                                "AB+",
                                "AB-",
                                "O+",
                                "O-"
                            ];

                            foreach ($blood_groups as $blood) {

                                $selected =
                                    (($user['blood_group'] ?? '') == $blood)
                                    ? "selected"
                                    : "";

                                echo "
                                    <option value=\"$blood\" $selected>
                                        $blood
                                    </option>
                                ";
                            }

                            ?>

                        </select>

                    </div>


                    <!-- Address -->

                    <div class="form-group full">

                        <label>
                            Address
                        </label>

                        <textarea
                            name="address"
                            placeholder="Enter your address"
                        ><?php
                            echo htmlspecialchars($user['address'] ?? '');
                        ?></textarea>

                    </div>


                    <!-- Profile Picture -->

                    <div class="form-group full">

                        <label>
                            Change Profile Picture
                        </label>

                        <input
                            type="file"
                            name="image"
                            accept="image/*"
                        >

                    </div>


                    <!-- Buttons -->

                    <div class="profile-actions">

                        <button
                            type="submit"
                            class="save-btn"
                        >

                            <i class="fa-solid fa-floppy-disk"></i>

                            Save Changes

                        </button>


                        <a
                            href="change-password.php"
                            class="password-btn"
                        >

                            <i class="fa-solid fa-key"></i>

                            Change Password

                        </a>

                    </div>


                </form>

            </div>

        </div>

    </main>

</div>

</body>

</html>