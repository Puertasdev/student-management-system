<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$user = "root";
$password = "";
$database = "student_management";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET["id"])) {
    header("Location: students.php");
    exit();
}

$id = $_GET["id"];
$message = "";

$studentSql = "SELECT * FROM students WHERE id='$id'";
$studentResult = $conn->query($studentSql);

if ($studentResult->num_rows != 1) {
    header("Location: students.php");
    exit();
}

$student = $studentResult->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    if (!empty($name) && !empty($email)) {
        $checkSql = "SELECT id FROM students WHERE email='$email' AND id != '$id'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $message = "This email is already used by another student.";
        } else {
            $updateSql = "UPDATE students SET name='$name', email='$email' WHERE id='$id'";

            if ($conn->query($updateSql) === TRUE) {
                header("Location: students.php");
                exit();
            } else {
                $message = "Error updating student.";
            }
        }
    } else {
        $message = "Please fill all fields.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Student</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Edit Student</h1>

    <?php if (!empty($message)) { ?>
        <p class="message"><?php echo $message; ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" value="<?php echo $student["student_id"]; ?>" disabled>
        <input type="text" name="name" value="<?php echo $student["name"]; ?>" required>
        <input type="email" name="email" value="<?php echo $student["email"]; ?>" required>
        <button type="submit">Save Changes</button>
    </form>

    <p class="small-text"><a href="students.php">Back to student list</a></p>
</div>

<footer class="footer">
    <p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>