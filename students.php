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

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_student"])) {
    $studentId = trim($_POST["student_id"]);
    $name = trim($_POST["name"]);
    $email = trim($_POST["email"]);

    if (!empty($studentId) && !empty($name) && !empty($email)) {
        $checkStmt = $conn->prepare("SELECT id, student_id, email FROM students WHERE student_id = ? OR email = ?");
        $checkStmt->bind_param("ss", $studentId, $email);
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
            $insertStmt = $conn->prepare("INSERT INTO students (student_id, name, email) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $studentId, $name, $email);

            if ($insertStmt->execute()) {
                $message = "Student added successfully.";
            } else {
                $message = "Error: " . $conn->error;
            }

            $insertStmt->close();
        }

        $checkStmt->close();
    } else {
        $message = "Please fill all student fields.";
    }
}

if (isset($_GET["delete"])) {
    $id = (int) $_GET["delete"];

    $deleteStmt = $conn->prepare("DELETE FROM students WHERE id = ?");
    $deleteStmt->bind_param("i", $id);

    if ($deleteStmt->execute()) {
        $message = "Student deleted successfully.";
    } else {
        $message = "Error deleting student.";
    }

    $deleteStmt->close();
}

$result = $conn->query("SELECT * FROM students ORDER BY id ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Students</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container large">
    <div class="top-bar">
        <div>
            <h1>Student Management</h1>
            <p class="welcome-text">Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?></p>
        </div>

        <div class="top-links">
            <a class="nav-link" href="profile.php">My Profile</a>
            <a class="logout-link" href="logout.php">Logout</a>
        </div>
    </div>

    <?php if (!empty($message)) { ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <h2>Add Student</h2>

    <form method="POST">
        <input type="text" name="student_id" placeholder="Student ID" required>
        <input type="text" name="name" placeholder="Student Name" required>
        <input type="email" name="email" placeholder="Student Email" required>
        <button type="submit" name="add_student">Add Student</button>
    </form>

    <h2>Student List</h2>

    <table>
        <tr>
            <th>ID</th>
            <th>Student ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Action</th>
        </tr>

        <?php if ($result && $result->num_rows > 0) { ?>
            <?php while ($row = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo htmlspecialchars($row["id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["student_id"]); ?></td>
                    <td><?php echo htmlspecialchars($row["name"]); ?></td>
                    <td><?php echo htmlspecialchars($row["email"]); ?></td>
                    <td>
                        <a class="edit-link" href="edit_student.php?id=<?php echo $row["id"]; ?>">Edit</a>
                        <a class="delete-link" href="students.php?delete=<?php echo $row["id"]; ?>" onclick="return confirm('Delete this student?')">Delete</a>
                    </td>
                </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="5">No students found.</td>
            </tr>
        <?php } ?>
    </table>
</div>

<footer class="footer">
    <p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>