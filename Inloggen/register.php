<?php
// Include the database connection file
include 'db.php';

$message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 🔒 Hash the password securely before saving it
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    // Prepare SQL query to insert user data safely
    $sql = "INSERT INTO users (name, email, password) VALUES (?, ?, ?)";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sss", $name, $email, $hashed_password);
        
        if (mysqli_stmt_execute($stmt)) {
            // Redirect to your register success page
            header("Location: register_success.php");
            exit();
        } else {
            $message = "Error: Could not register user.";
        }
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Registreren</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h2>Account Aanmaken</h2>
    <?php if(!empty($message)) echo "<p style='color:red;'>$message</p>"; ?>
    
    <form action="register.php" method="POST">
        <label>Naam:</label><br>
        <input type="text" name="name" required><br><br>

        <label>Email:</label><br>
        <input type="email" name="email" required><br><br>

        <label>Wachtwoord:</label><br>
        <input type="password" name="password" required><br><br>

        <button type="submit">Registreren</button>
    </form>
</body>
</html>