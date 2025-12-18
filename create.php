<?php
session_start();
require "config/db.php";

/* ====== Auth ====== */
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

/* ====== Form Submit ====== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Client data
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $cin   = $_POST['cin'];
    $phone = $_POST['phone'];

    // Account data
    $account_type = $_POST['account_type'];
    $balance      = $_POST['balance'];

    mysqli_begin_transaction($connect);

    try {
        /* ====== 1️⃣ Insert Customer ====== */
        $stmt = mysqli_prepare(
            $connect,
            "INSERT INTO customers (full_name, email, cin, phone)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ssss", $name, $email, $cin, $phone);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        $customer_id = mysqli_insert_id($connect);

        /* ====== 2️⃣ Insert Account ====== */
        $account_num = "ACC" . rand(100000, 999999);

        $stmt = mysqli_prepare(
            $connect,
            "INSERT INTO accounts (account_num, account_type, balance, customer_id)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssdi",
            $account_num,
            $account_type,
            $balance,
            $customer_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        /* ====== Commit ====== */
        mysqli_commit($connect);

        header("Location: dashboard.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($connect);
        echo "Erreur : " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter Client</title>

<style>
body{
    font-family: Arial, sans-serif;
    background-image: url('images/image.png');
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.form-box{
    background:white;
    padding:25px;
    width:380px;
    border-radius:10px;
    box-shadow:0 4px 10px rgba(0,0,0,.1);
}
h2{
    text-align:center;
}
input, select, button{
    width:100%;
    padding:10px;
    margin:8px 0;
}
select{
    background:white;
}
button{
    background:#28a745;
    color:white;
    border:none;
    border-radius:5px;
    font-size:15px;
    cursor:pointer;
}
button:hover{
    background:#1e7e34;
}
a{
    display:block;
    text-align:center;
    margin-top:10px;
    text-decoration:none;
}
hr{
    margin:15px 0;
}
</style>
</head>

<body>

<div class="form-box">
    <h2>Ajouter Client & Compte</h2>

    <form method="post">

        <!-- Client -->
        <input type="text" name="name" placeholder="Nom complet" required>
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="cin" placeholder="CIN" required>
        <input type="text" name="phone" placeholder="Téléphone" required>

        <hr>

        <!-- Account -->
        <select name="account_type" required>
            <option value="">-- Type de compte --</option>
            <option value="saving">Saving</option>
            <option value="checking">Checking</option>
            <option value="business">Business</option>
        </select>

        <input type="number" name="balance" placeholder="Solde initial" min="0" step="0.01" required>

        <button type="submit">Créer Client</button>
    </form>

    <a href="dashboard.php"> Retour</a>
</div>

</body>
</html>
