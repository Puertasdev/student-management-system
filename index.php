<?php
session_start();

$host = "localhost";
$user = "root";
$password = "";
$database = "student_management";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $userPassword = trim($_POST["password"]);

    if (!empty($username) && !empty($email) && !empty($userPassword)) {
        $checkSql = "SELECT id FROM users WHERE email = '$email'";
        $checkResult = $conn->query($checkSql);

        if ($checkResult->num_rows > 0) {
            $message = "This email is already registered.";
        } else {
            $hashedPassword = password_hash($userPassword, PASSWORD_DEFAULT);

            $sql = "INSERT INTO users (username, email, password)
                    VALUES ('$username', '$email', '$hashedPassword')";

            if ($conn->query($sql) === TRUE) {
                $message = "User registered successfully.";
            } else {
                $message = "Error: " . $conn->error;
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
<title>Register</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>Register</h1>

    <?php if (!empty($message)) { ?>
        <p class="message"><?php echo $message; ?></p>
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