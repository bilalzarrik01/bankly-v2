<?php
session_start();
include "config/db.php";

if (isset($_POST['submit'])) {
    $username = mysqli_real_escape_string($connect, $_POST['email']);
    $password = mysqli_real_escape_string($connect, $_POST['password']);

    $result = mysqli_query($connect, "SELECT * FROM user WHERE username='$username' AND password='$password'");

    if (mysqli_num_rows($result) > 0) {
        $_SESSION['user'] = $username;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Nom d'utilisateur ou mot de passe incorrect";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Login - Bankly V2</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #000000ff, #9b9fa0ff);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .login-box {
            background: white;
            padding: 30px;
            width: 350px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            text-align: center;
        }

        .login-box h2 {
            margin-bottom: 20px;
            color: #000000ff;
        }

        .login-box input {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 15px;
        }

        .login-box input:focus {
            outline: none;
            border-color: #007bff;
        }

        .login-box button {
            width: 100%;
            padding: 12px;
            margin-top: 15px;
            background: #3f4d5dff;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .login-box button:hover {
            background: #0056b3;
        }

        .error {
            margin-top: 15px;
            color: red;
            font-size: 14px;
        }
    </style>
</head>
<body>

<div class="login-box">
    <h2>Login Bankly</h2>

    <form method="POST">
        <input type="text" name="email" placeholder="Nom d'utilisateur" required>
        <input type="password" name="password" placeholder="Mot de passe" required>
        <button type="submit" name="submit">Se connecter</button>
    </form>

    <?php if(isset($error)) echo "<p class='error'>$error</p>"; ?>
</div>

</body>
</html>
