<?php
session_start();

if (!isset($_SESSION["user_id"])) {
header("Location: login.php");
exit();
}

$host="localhost";
$user="root";
$password="";
$database="student_management";

$conn=new mysqli($host,$user,$password,$database);

if($conn->connect_error){
die("Connection failed: ".$conn->connect_error);
}

$message="";

if($_SERVER["REQUEST_METHOD"]=="POST" && isset($_POST["add_student"])){

$studentId=trim($_POST["student_id"]);
$name=trim($_POST["name"]);
$email=trim($_POST["email"]);

$sql="INSERT INTO students (student_id,name,email)
VALUES ('$studentId','$name','$email')";

if($conn->query($sql)===TRUE){
$message="Student added successfully.";
}else{
$message="Error: ".$conn->error;
}

}

if(isset($_GET["delete"])){

$id=$_GET["delete"];

$conn->query("DELETE FROM students WHERE id='$id'");

$message="Student deleted successfully.";

}

$result=$conn->query("SELECT * FROM students ORDER BY id ASC");

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
<p class="welcome-text">Welcome, <?php echo $_SESSION["username"]; ?></p>
</div>

<a class="logout-link" href="logout.php">Logout</a>
</div>

<?php if(!empty($message)){ ?>
<p class="message"><?php echo $message; ?></p>
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

<?php while($row=$result->fetch_assoc()){ ?>

<tr>

<td><?php echo $row["id"]; ?></td>
<td><?php echo $row["student_id"]; ?></td>
<td><?php echo $row["name"]; ?></td>
<td><?php echo $row["email"]; ?></td>

<td>
<a class="delete-link"
href="students.php?delete=<?php echo $row["id"]; ?>"
onclick="return confirm('Delete this student?')">
Delete
</a>
</td>

</tr>

<?php } ?>

</table>

</div>

<footer class="footer">
<p>Student Management System — Created and developed by Gabriel Puertas Passarelli © 2026</p>
</footer>

</body>
</html>