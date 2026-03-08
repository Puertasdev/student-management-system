<?php
session_start();

$host = "localhost";
$dbUser = "root";
$dbPassword = "";
$database = "student_management";

$conn = new mysqli($host, $dbUser, $dbPassword, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $userPassword = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($userPassword)) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $checkStmt->bind_param("s", $email);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

            $insertStmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $insertStmt->bind_param("sss", $username, $email, $hashedPassword);

            if ($insertStmt->execute()) {
                $message = "User registered successfully.";
            } else {
                $message = "Error: " . $conn->error;
            }

            $insertStmt->close();
        }

        $checkStmt->close();
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
<title>Register</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Register</h1>

    <?php if (!empty($message)) { ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit">Register</button>
    </form>

    <p class="small-text">Already have an account? <a href="login.php">Login here</a></p>
</div>

<footer class="footer">
    <p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>