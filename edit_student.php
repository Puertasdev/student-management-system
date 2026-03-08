<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$host = "localhost";
$dbUser = "root";
$dbPassword = "";
$database = "student_management";

$conn = new mysqli($host, $dbUser, $dbPassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {
    header("Location: students.php");
    exit();
}

$id = (int) $_GET["id"];
$message = "";

$stmt = $conn->prepare("SELECT id, student_id, name, email FROM students WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    $stmt->close();
    header("Location: students.php");
    exit();
}

$student = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $studentId = trim($_POST["student_id"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    if (!empty($studentId) && !empty($name) && !empty($email)) {
        $checkStmt = $conn->prepare("SELECT id, student_id, email FROM students WHERE (student_id = ? OR email = ?) AND id != ?");
        $checkStmt->bind_param("ssi", $studentId, $email, $id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $existing = $checkResult->fetch_assoc();

            if ($existing["student_id"] === $studentId) {
                $message = "This Student ID already exists.";
            } elseif ($existing["email"] === $email) {
                $message = "This student email already exists.";
            } else {
                $message = "Student ID or email already exists.";
            }
        } else {
            $updateStmt = $conn->prepare("UPDATE students SET student_id = ?, name = ?, email = ? WHERE id = ?");
            $updateStmt->bind_param("sssi", $studentId, $name, $email, $id);

            if ($updateStmt->execute()) {
                header("Location: students.php");
                exit();
            } else {
                $message = "Error updating student: " . $conn->error;
            }

            $updateStmt->close();
        }

        $checkStmt->close();
    } else {
        $message = "Please fill all fields.";
    }

    $refreshStmt = $conn->prepare("SELECT id, student_id, name, email FROM students WHERE id = ?");
    $refreshStmt->bind_param("i", $id);
    $refreshStmt->execute();
    $refreshResult = $refreshStmt->get_result();

    if ($refreshResult && $refreshResult->num_rows === 1) {
        $student = $refreshResult->fetch_assoc();
    }

    $refreshStmt->close();
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
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="student_id" value="<?php echo htmlspecialchars($student["student_id"]); ?>" required>
        <input type="text" name="name" value="<?php echo htmlspecialchars($student["name"]); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($student["email"]); ?>" required>
        <button type="submit">Save Changes</button>
    </form>

    <p class="small-text"><a href="students.php">Back to student list</a></p>
</div>

<footer class="footer">
    <p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>