<?php
session_start();
include "config/db.php";

/* ====== Auth ====== */
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit;
}

/* ====== Stats ====== */
$clients_count  = mysqli_fetch_assoc(
    mysqli_query($connect, "SELECT COUNT(*) AS total FROM customers")
)['total'];

$accounts_count = mysqli_fetch_assoc(
    mysqli_query($connect, "SELECT COUNT(*) AS total FROM accounts")
)['total'];

$balance_sum    = mysqli_fetch_assoc(
    mysqli_query($connect, "SELECT SUM(balance) AS total FROM accounts")
)['total'];

/* ====== Lists ====== */
$clients_list = mysqli_query($connect, "SELECT * FROM customers ORDER BY customer_id DESC");

$accounts_list = mysqli_query(
    $connect,
    "SELECT a.*, c.full_name 
     FROM accounts a 
     JOIN customers c ON a.customer_id = c.customer_id 
     ORDER BY a.account_id DESC"
);

$transactions_list = mysqli_query(
    $connect,
    "SELECT t.*, a.account_num 
     FROM transictions t 
     JOIN accounts a ON t.account_id = a.account_id 
     ORDER BY t.transictions_id DESC"
);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Dashboard - Bankly V2</title>

<style>
/* ====== Global ====== */
body{
    font-family: Arial, sans-serif;
    background:#f4f6f8;
    margin:0;
}

/* ====== Header ====== */
header{
    background:#007bff;
    color:white;
    padding:20px;
    text-align:center;
    position:relative;
}

header a{
    position:absolute;
    right:20px;
    top:20px;
    padding:10px 15px;
    color:white;
    text-decoration:none;
    border-radius:5px;
}

/* Logout red */
header a[href="logout.php"]{
    background:#dc3545;
}
header a[href="logout.php"]:hover{
    background:#a71d2a;
}

/* ====== Actions ====== */
.actions{
    display:flex;
    justify-content:center;
    gap:15px;
    margin:20px 0;
}

.actions a{
    background:#28a745;
    color:white;
    padding:10px 15px;
    border-radius:5px;
    text-decoration:none;
}
.actions a:hover{
    background:#1e7e34;
}

/* ====== Stats ====== */
.stats{
    display:flex;
    justify-content:space-around;
    margin:20px;
}
.card{
    background:white;
    width:25%;
    padding:20px;
    text-align:center;
    border-radius:10px;
    box-shadow:0 4px 6px rgba(0,0,0,0.1);
}

/* ====== Tables ====== */
.section{
    width:90%;
    margin:30px auto;
}

.section h2{
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.section h2 a{
    font-size:14px;
    padding:6px 10px;
    background:#007bff;
    color:white;
    border-radius:5px;
    text-decoration:none;
}

table{
    width:100%;
    border-collapse:collapse;
    background:white;
    border-radius:10px;
    overflow:hidden;
    box-shadow:0 4px 6px rgba(0,0,0,0.1);
}

th,td{
    padding:12px;
    border-bottom:1px solid #ddd;
}

th{
    background:#007bff;
    color:white;
}

.actions-table a{
    margin-right:8px;
    text-decoration:none;
}

.actions-table .edit{ color:#007bff; }
.actions-table .delete{ color:#dc3545; }
</style>
</head>

<body>

<header>
    <h1>Dashboard Bankly V2</h1>
    <a href="logout.php">Logout</a>
</header>

<!-- ACTIONS -->
<div class="actions">
    <a href="create.php">➕ Ajouter Client</a>
  
</div>

<!-- STATS -->
<div class="stats">
    <div class="card">
        <h3>Clients</h3>
        <p><?= $clients_count ?></p>
    </div>
    <div class="card">
        <h3>Comptes</h3>
        <p><?= $accounts_count ?></p>
    </div>
    <div class="card">
        <h3>Total Soldes</h3>
        <p><?= $balance_sum ?> MAD</p>
    </div>
</div>

<!-- CLIENTS -->
<div class="section">
<h2>
    Liste des clients
    <a href="create.php">➕ Ajouter</a>
</h2>
<table>
<tr>
    <th>ID</th>
    <th>Nom</th>
    <th>Email</th>
    <th>CIN</th>
    <th>Téléphone</th>
    <th>Actions</th>
</tr>
<?php while($c = mysqli_fetch_assoc($clients_list)){ ?>
<tr>
    <td><?= $c['customer_id'] ?></td>
    <td><?= $c['full_name'] ?></td>
    <td><?= $c['email'] ?></td>
    <td><?= $c['cin'] ?></td>
    <td><?= $c['phone'] ?></td>
    <td class="actions-table">
        <a class="edit" href="edit.php?id=<?= $c['customer_id'] ?>">Edit</a>
        <a class="delete" href="delete.php?id=<?= $c['customer_id'] ?>"
           onclick="return confirm('Supprimer ce client ?')">Delete</a>
    </td>
</tr>
<?php } ?>
</table>
</div>

<!-- ACCOUNTS -->
<div class="section">
<h2>
    Liste des comptes
  
</h2>
<table>
<tr>
    <th>ID</th>
    <th>Compte</th>
    <th>Type</th>
    <th>Solde</th>
    <th>Client</th>
</tr>
<?php while($a = mysqli_fetch_assoc($accounts_list)){ ?>
<tr>
    <td><?= $a['account_id'] ?></td>
    <td><?= $a['account_num'] ?></td>
    <td><?= $a['account_type'] ?></td>
    <td><?= $a['balance'] ?></td>
    <td><?= $a['full_name'] ?></td>
</tr>
<?php } ?>
</table>
</div>

<!-- TRANSACTIONS -->
<div class="section">
<h2>
    Liste des transactions
</h2>
<table>
<tr>
    <th>ID</th>
    <th>Montant</th>
    <th>Type</th>
    <th>Compte</th>
    <th>Date</th>
</tr>
<?php while($t = mysqli_fetch_assoc($transactions_list)){ ?>
<tr>
    <td><?= $t['transictions_id'] ?></td>
    <td><?= $t['amount'] ?></td>
    <td><?= $t['transictions_type'] ?></td>
    <td><?= $t['account_num'] ?></td>
    <td><?= $t['transictions_date'] ?></td>
</tr>
<?php } ?>
</table>
</div>

</body>
</html>
