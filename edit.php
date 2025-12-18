<?php
session_start();
require __DIR__ . '/config/db.php';

/* ===== AUTH ===== */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* ===== GET CLIENT ID ===== */
$customer_id = intval($_GET['id'] ?? 0);
if ($customer_id <= 0) {
    die("ID client invalide");
}

/* ===== UPDATE DATA ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Client
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $cin   = $_POST['cin'];
    $phone = $_POST['phone'];

    // Account
    $account_type = $_POST['account_type'];
    $balance      = $_POST['balance'];

    mysqli_begin_transaction($connect);

    try {
        /* ===== Update Customer ===== */
        $stmt = mysqli_prepare(
            $connect,
            "UPDATE customers
             SET full_name=?, email=?, cin=?, phone=?
             WHERE customer_id=?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "ssssi",
            $name,
            $email,
            $cin,
            $phone,
            $customer_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        /* ===== Update Account ===== */
        $stmt = mysqli_prepare(
            $connect,
            "UPDATE accounts
             SET account_type=?, balance=?
             WHERE customer_id=?"
        );
        mysqli_stmt_bind_param(
            $stmt,
            "sdi",
            $account_type,
            $balance,
            $customer_id
        );
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);

        mysqli_commit($connect);
        header("Location: dashboard.php");
        exit;

    } catch (Exception $e) {
        mysqli_rollback($connect);
        die("Erreur : " . $e->getMessage());
    }
}

/* ===== FETCH CLIENT + ACCOUNT ===== */
$stmt = mysqli_prepare(
    $connect,
    "SELECT c.*, a.account_num, a.account_type, a.balance
     FROM customers c
     JOIN accounts a ON c.customer_id = a.customer_id
     WHERE c.customer_id = ?"
);
mysqli_stmt_bind_param($stmt, "i", $customer_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$data = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$data) {
    die("Client introuvable");
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Modifier Client & Compte</title>

<style>
body{
    font-family: Arial, sans-serif;
    background:#f4f6f8;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh;
}
.form-box{
    background:white;
    padding:25px;
    border-radius:10px;
    width:400px;
    box-shadow:0 4px 10px rgba(0,0,0,.1);
}
h2{text-align:center;}
input, select, button{
    width:100%;
    padding:10px;
    margin:8px 0;
}
button{
    background:#007bff;
    color:white;
    border:none;
    border-radius:5px;
    cursor:pointer;
}
button:hover{
    background:#0056b3;
}
a{
    display:block;
    text-align:center;
    margin-top:10px;
}
hr{
    margin:15px 0;
}
</style>
</head>

<body>

<div class="form-box">
<h2>Modifier Client & Compte</h2>

<form method="post">

    <!-- CLIENT -->
    <input type="text" name="name"
        value="<?= htmlspecialchars(string: $data['full_name']) ?>" required>

    <input type="email" name="email"
        value="<?= htmlspecialchars($data['email']) ?>" required>

    <input type="text" name="cin"
        value="<?= htmlspecialchars($data['cin']) ?>" required>

    <input type="text" name="phone"
        value="<?= htmlspecialchars($data['phone']) ?>" required>

    <hr>

    <!-- ACCOUNT -->
    <input type="text"
        value="<?= htmlspecialchars($data['account_num']) ?>"
        disabled>

    <select name="account_type" required>
        <option value="saving" <?= $data['account_type']=='saving'?'selected':'' ?>>Saving</option>
        <option value="checking" <?= $data['account_type']=='checking'?'selected':'' ?>>Checking</option>
        <option value="business" <?= $data['account_type']=='business'?'selected':'' ?>>Business</option>
    </select>

    <input type="number" name="balance"
        value="<?= htmlspecialchars($data['balance']) ?>"
        step="0.01" min="0" required>

    <button type="submit">Enregistrer</button>
</form>

<a href="dashboard.php">⬅ Retour</a>
</div>

</body>
</html>
