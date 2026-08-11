<?php
session_start();

if(!isset($_SESSION['doctor_id']))
{
    header("Location: login.php");
    exit();
}

include("../config/database.php");

if(!isset($_GET['appointment']))
{
    die("Invalid Appointment.");
}

$appointment_id = intval($_GET['appointment']);

$query = mysqli_query($conn,"
SELECT
appointments.*,
users.fullname
FROM appointments
INNER JOIN users
ON appointments.patient_id = users.id
WHERE appointments.id='$appointment_id'
");

if(mysqli_num_rows($query)==0)
{
    die("Appointment not found.");
}

$data = mysqli_fetch_assoc($query);

$message="";

if(isset($_POST['save']))
{

    $diagnosis=mysqli_real_escape_string($conn,$_POST['diagnosis']);

    $medicines=mysqli_real_escape_string($conn,$_POST['medicines']);

    $advice=mysqli_real_escape_string($conn,$_POST['advice']);

$check=mysqli_query($conn,"
SELECT *
FROM prescriptions
WHERE appointment_id='$appointment_id'
");

if(mysqli_num_rows($check)>0)
{

    $message="Prescription already exists for this appointment.";

}
else
{

    mysqli_query($conn,"
    INSERT INTO prescriptions
    (
    appointment_id,
    doctor_id,
    patient_id,
    diagnosis,
    medicines,
    advice
    )
    VALUES
    (
    '$appointment_id',
    '{$_SESSION['doctor_id']}',
    '{$data['patient_id']}',
    '$diagnosis',
    '$medicines',
    '$advice'
    )
    ");

    $message="Prescription Saved Successfully.";

}

    $message="Prescription Saved Successfully.";

}
?>

<!DOCTYPE html>

<html>

<head>

<meta charset="UTF-8">

<title>Write Prescription</title>

<link rel="stylesheet"
href="../assets/css/dashboard.css">

</head>

<body>

<div class="profile-card">

<h1>Write Prescription</h1>

<?php

if($message!="")
{

echo "<p style='color:green;font-weight:bold;'>$message</p>";

}

?>

<h3>

Patient:

<?php echo $data['fullname']; ?>

</h3>

<form method="POST">

<label>Diagnosis</label>

<textarea
name="diagnosis"
rows="5"
required></textarea>

<label>Medicines</label>

<textarea
name="medicines"
rows="6"
placeholder="One medicine per line"
required></textarea>

<label>Advice</label>

<textarea
name="advice"
rows="5"
required></textarea>

<br><br>

<button
class="register-btn"
name="save">

Save Prescription

</button>

</form>

</div>

</body>

</html>