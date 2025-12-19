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
    $name  = mysqli_real_escape_string($connect, $_POST['name']);
    $email = mysqli_real_escape_string($connect, $_POST['email']);
    $cin   = mysqli_real_escape_string($connect, $_POST['cin']);
    $phone = mysqli_real_escape_string($connect, $_POST['phone']);

    // Account data
    $account_type = $_POST['account_type'];
    $balance      = floatval($_POST['balance']);

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
        // Générer un numéro unique (INT uniquement)
        $account_num = rand(100000000, 999999999); // 9 chiffres
        
        // Vérifier si le numéro existe déjà
        $check = mysqli_query($connect, "SELECT account_num FROM accounts WHERE account_num = $account_num");
        while(mysqli_num_rows($check) > 0) {
            $account_num = rand(100000000, 999999999);
            $check = mysqli_query($connect, "SELECT account_num FROM accounts WHERE account_num = $account_num");
        }

        $stmt = mysqli_prepare(
            $connect,
            "INSERT INTO accounts (account_num, account_type, balance, customer_id)
             VALUES (?, ?, ?, ?)"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "isdi",
            $account_num,
            $account_type,
            $balance,
            $customer_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        // Commit
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
<title>Ajouter Client - Bankly V2</title>

<style>
body{
    font-family: Arial, sans-serif;
    background-image: url('images/image.png');
    background-repeat: no-repeat;
    background-size: cover;
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
    margin:0;
    padding:20px;
}
.form-box{
    background:rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding:30px;
    width:100%;
    max-width:420px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.3);
}
h2{
    text-align:center;
    color:#333;
    margin-bottom:20px;
}
.form-section{
    margin-bottom:20px;
}
.form-section h3{
    color:#555;
    margin-bottom:10px;
    font-size:16px;
    border-bottom:2px solid #28a745;
    padding-bottom:5px;
}
input, select, button{
    width:100%;
    padding:12px;
    margin:8px 0;
    box-sizing:border-box;
    border:1px solid #ddd;
    border-radius:5px;
    font-size:14px;
}
select{
    background:white;
    cursor:pointer;
}
input:focus, select:focus{
    outline:none;
    border-color:#28a745;
}
button{
    background:#28a745;
    color:white;
    border:none;
    font-size:16px;
    font-weight:bold;
    cursor:pointer;
    margin-top:15px;
    transition:background 0.3s;
}
button:hover{
    background:#1e7e34;
}
.btn-back{
    display:block;
    text-align:center;
    margin-top:15px;
    color:#6c757d;
    text-decoration:none;
    font-weight:bold;
}
.btn-back:hover{
    color:#333;
    text-decoration:underline;
}
hr{
    margin:20px 0;
    border:none;
    border-top:1px solid #ddd;
}
</style>
</head>

<body>

<div class="form-box">
    <h2>➕ Ajouter Client & Compte</h2>

    <form method="POST">

        <!-- Section Client -->
        <div class="form-section">
            <h3>👤 Informations Client</h3>
            <input type="text" name="name" placeholder="Nom complet" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="text" name="cin" placeholder="CIN" required>
            <input type="tel" name="phone" placeholder="Téléphone" required>
        </div>

        <hr>

        <!-- Section Compte -->
        <div class="form-section">
            <h3>💳 Informations Compte</h3>
            <select name="account_type" required>
                <option value="">-- Type de compte --</option>
                <option value="saving">💰 Épargne (Saving)</option>
                <option value="checking">✅ Courant (Checking)</option>
                <option value="business">🏢 Professionnel (Business)</option>
            </select>

            <input type="number" name="balance" placeholder="Solde initial (MAD)" min="0" step="0.01" value="0" required>
        </div>

        <button type="submit">✔️ Créer Client & Compte</button>
    </form>

    <a href="dashboard.php" class="btn-back">← Retour au Dashboard</a>
</div>

</body>
</html>