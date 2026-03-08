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

$userId = (int) $_SESSION["user_id"];
$message = "";

$stmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    $message = "Logged user was not found in the database.";
    $userData = [
        "username" => $_SESSION["username"] ?? "",
        "email" => $_SESSION["user_email"] ?? ""
    ];
} else {
    $userData = $result->fetch_assoc();
}

$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $newPassword = trim($_POST["new_password"]);

    if (!empty($username) && !empty($email)) {
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $checkStmt->bind_param("si", $email, $userId);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $message = "This email is already used by another user.";
        } else {
            if (!empty($newPassword)) {
                $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?");
                $updateStmt->bind_param("sssi", $username, $email, $hashedPassword, $userId);
            } else {
                $updateStmt = $conn->prepare("UPDATE users SET username = ?, email = ? WHERE id = ?");
                $updateStmt->bind_param("ssi", $username, $email, $userId);
            }

            if ($updateStmt->execute()) {
                $_SESSION["username"] = $username;
                $_SESSION["user_email"] = $email;
                $message = "Profile updated successfully.";

                $refreshStmt = $conn->prepare("SELECT id, username, email FROM users WHERE id = ?");
                $refreshStmt->bind_param("i", $userId);
                $refreshStmt->execute();
                $refreshResult = $refreshStmt->get_result();

                if ($refreshResult && $refreshResult->num_rows === 1) {
                    $userData = $refreshResult->fetch_assoc();
                }

                $refreshStmt->close();
            } else {
                $message = "Error updating profile: " . $conn->error;
            }

            $updateStmt->close();
        }

        $checkStmt->close();
    } else {
        $message = "Username and email are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Profile</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h1>My Profile</h1>

    <?php if (!empty($message)) { ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php } ?>

    <form method="POST">
        <input type="text" name="username" value="<?php echo htmlspecialchars($userData["username"] ?? ""); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($userData["email"] ?? ""); ?>" required>
        <input type="password" name="new_password" placeholder="New Password (optional)">
        <button type="submit">Save Changes</button>
    </form>

    <p class="small-text"><a href="students.php">Back to student page</a></p>
</div>

<footer class="footer">
    <p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>