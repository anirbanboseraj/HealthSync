<?php
session_start();

if(!isset($_SESSION['user_id']))
{
    header("Location: ../login.php");
    exit();
}

include("../config/database.php");

$patient_id = $_SESSION['user_id'];

$title = mysqli_real_escape_string($conn,$_POST['title']);
$type  = mysqli_real_escape_string($conn,$_POST['type']);

$file = $_FILES['record'];

if($file['error'] != 0)
{
    die("No file selected.");
}

$allowed = [
    "pdf",
    "jpg",
    "jpeg",
    "png"
];

$extension = strtolower(pathinfo($file['name'],PATHINFO_EXTENSION));

if(!in_array($extension,$allowed))
{
    die("Only PDF, JPG, JPEG and PNG files are allowed.");
}

if($file['size'] > 5*1024*1024)
{
    die("Maximum file size is 5MB.");
}

$newName = time()."_".rand(1000,9999).".".$extension;

move_uploaded_file(
    $file['tmp_name'],
    "../assets/uploads/medical/".$newName
);

mysqli_query($conn,"
INSERT INTO medical_records
(
patient_id,
title,
record_type,
file_name
)
VALUES
(
'$patient_id',
'$title',
'$type',
'$newName'
)
");

header("Location: medical-records.php");
exit();
?>