<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$user_id = $_SESSION['user_id'];

$fullname = trim($_POST['fullname'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$gender = $_POST['gender'] ?? '';
$dob = $_POST['dob'] ?? '';
$blood_group = $_POST['blood_group'] ?? '';
$address = trim($_POST['address'] ?? '');

if ($fullname == '') {
    header("Location: profile.php?error=1");
    exit();
}


/* =========================
   UPDATE BASIC INFORMATION
========================= */

$stmt = mysqli_prepare($conn, "

    UPDATE users

    SET
        fullname = ?,
        phone = ?,
        gender = ?,
        dob = NULLIF(?, ''),
        blood_group = ?,
        address = ?

    WHERE id = ?
    AND role = 'patient'

");

mysqli_stmt_bind_param(
    $stmt,
    "ssssssi",
    $fullname,
    $phone,
    $gender,
    $dob,
    $blood_group,
    $address,
    $user_id
);

if (!mysqli_stmt_execute($stmt)) {

    header("Location: profile.php?error=1");
    exit();

}

mysqli_stmt_close($stmt);


/* =========================
   PROFILE IMAGE
========================= */

if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] === UPLOAD_ERR_OK
) {

    $allowed = [
        'jpg',
        'jpeg',
        'png',
        'webp'
    ];

    $original_name =
        $_FILES['image']['name'];

    $extension =
        strtolower(
            pathinfo(
                $original_name,
                PATHINFO_EXTENSION
            )
        );

    if (!in_array($extension, $allowed)) {

        header("Location: profile.php?error=1");
        exit();

    }


    /* Upload folder */

    $upload_dir =
        "../assets/uploads/profile/";


    if (!is_dir($upload_dir)) {

        mkdir(
            $upload_dir,
            0777,
            true
        );

    }


    /* Generate unique filename */

    $new_name =
        "profile_" .
        $user_id .
        "_" .
        time() .
        "." .
        $extension;


    $destination =
        $upload_dir . $new_name;


    if (
        move_uploaded_file(
            $_FILES['image']['tmp_name'],
            $destination
        )
    ) {

        $image_stmt = mysqli_prepare(
            $conn,
            "
            UPDATE users
            SET image = ?
            WHERE id = ?
            AND role = 'patient'
            "
        );

        mysqli_stmt_bind_param(
            $image_stmt,
            "si",
            $new_name,
            $user_id
        );

        mysqli_stmt_execute($image_stmt);

        mysqli_stmt_close($image_stmt);

    }

}


/* =========================
   UPDATE SESSION NAME
========================= */

$_SESSION['fullname'] = $fullname;


/* =========================
   SUCCESS
========================= */

header("Location: profile.php?success=1");

exit();

?>