<?php

session_start();

include("../config/database.php");

$id = intval($_GET['id']);

$patient = $_SESSION['user_id'];

$query = mysqli_query($conn,"
SELECT *
FROM medical_records
WHERE id='$id'
AND patient_id='$patient'
");

if(mysqli_num_rows($query)>0)
{

    $row = mysqli_fetch_assoc($query);

    $path = "../assets/uploads/medical/".$row['file_name'];

    if(file_exists($path))
    {
        unlink($path);
    }

    mysqli_query($conn,"
    DELETE FROM medical_records
    WHERE id='$id'
    ");

}

header("Location: medical-records.php");
exit();

?>