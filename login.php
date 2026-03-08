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

$email = trim($_POST["email"]);
$userPassword = trim($_POST["password"]);

$sql = "SELECT * FROM users WHERE email='$email'";
$result = $conn->query($sql);

if ($result->num_rows == 1) {

$userData = $result->fetch_assoc();

if (password_verify($userPassword, $userData["password"])) {

$_SESSION["user_id"] = $userData["id"];
$_SESSION["username"] = $userData["username"];

header("Location: students.php");
exit();

} else {
$message = "Incorrect password.";
}

} else {
$message = "User not found.";
}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

<h1>Login</h1>

<?php if (!empty($message)) { ?>
<p class="message"><?php echo $message; ?></p>
<?php } ?>

<form method="POST">

<input type="email" name="email" placeholder="Email" required>

<input type="password" name="password" placeholder="Password" required>

<button type="submit">Login</button>

</form>

<p class="small-text">
Don't have an account? <a href="index.php">Register here</a>
</p>

</div>

<footer class="footer">
<p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>