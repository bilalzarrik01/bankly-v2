<?php
session_start();
require "config/db.php";

/* ====== Auth ====== */
if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

/* ====== Fetch Accounts avec info client ====== */
$accounts = mysqli_query(
    $connect, 
    "SELECT a.account_id, a.account_num, a.account_type, a.balance, c.full_name 
     FROM accounts a 
     JOIN customers c ON a.customer_id = c.customer_id 
     ORDER BY a.account_num"
);

/* ====== Messages ====== */
$error = '';
$success = '';

/* ====== Form Submit ====== */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $account_id       = intval($_POST['account_id']);
    $transaction_type = $_POST['transictions_type'];
    $amount           = floatval($_POST['amount']);
    $date             = $_POST['transictions_date'];
    
    // Validation
    if ($amount <= 0) {
        $error = "Le montant doit être supérieur à 0";
    } else {
        // Vérifier le solde pour retrait
        if ($transaction_type === 'retrait') {
            $check = mysqli_fetch_assoc(
                mysqli_query($connect, "SELECT balance FROM accounts WHERE account_id = $account_id")
            );
            if ($check['balance'] < $amount) {
                $error = "Solde insuffisant pour effectuer ce retrait";
            }
        }
        
        if (empty($error)) {
            mysqli_begin_transaction($connect);
            try {
                /* ====== Insert Transaction ====== */
                $stmt = mysqli_prepare(
                    $connect,
                    "INSERT INTO transictions (account_id, transictions_type, amount, transictions_date)
                     VALUES (?, ?, ?, ?)"
                );
                mysqli_stmt_bind_param($stmt, "isds", $account_id, $transaction_type, $amount, $date);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
                
                /* ====== Update Account Balance ====== */
                if ($transaction_type === 'depot') {
                    mysqli_query($connect, "UPDATE accounts SET balance = balance + $amount WHERE account_id = $account_id");
                } else {
                    mysqli_query($connect, "UPDATE accounts SET balance = balance - $amount WHERE account_id = $account_id");
                }
                
                mysqli_commit($connect);
                $success = "Transaction créée avec succès !";
                
                // Redirection après 2 secondes
                header("refresh:2;url=dashboard.php");
                
            } catch (Exception $e) {
                mysqli_rollback($connect);
                $error = "Erreur : " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter Transaction - Bankly V2</title>
<style>
body{
    font-family: Arial, sans-serif;
    background-image: url('images/image.png');
    background-repeat: no-repeat;
    background-size: cover;
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    margin: 0;
    padding: 20px;
}

.form-container{
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    padding: 30px;
    width: 100%;
    max-width: 500px;
    border-radius: 15px;
    box-shadow: 0 8px 20px rgba(0,0,0,0.3);
}

h2{
    text-align: center;
    color: #333;
    margin-bottom: 25px;
}

.form-group{
    margin-bottom: 20px;
}

label{
    display: block;
    margin-bottom: 5px;
    color: #333;
    font-weight: bold;
}

input, select, textarea{
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 5px;
    font-size: 14px;
    box-sizing: border-box;
}

select{
    background: white;
    cursor: pointer;
}

textarea{
    resize: vertical;
    min-height: 80px;
}

input:focus, select:focus, textarea:focus{
    outline: none;
    border-color: #007bff;
}

.btn-group{
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

button{
    flex: 1;
    padding: 12px;
    border: none;
    border-radius: 5px;
    font-size: 16px;
    font-weight: bold;
    cursor: pointer;
    transition: background 0.3s;
}

button[type="submit"]{
    background: #28a745;
    color: white;
}

button[type="submit"]:hover{
    background: #218838;
}

.btn-back{
    background: #6c757d;
    color: white;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
}

.btn-back:hover{
    background: #5a6268;
}

.alert{
    padding: 12px;
    border-radius: 5px;
    margin-bottom: 20px;
    text-align: center;
}

.alert-success{
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-error{
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.account-info{
    font-size: 12px;
    color: #666;
    padding-left: 5px;
}

.amount-warning{
    font-size: 12px;
    color: #856404;
    background: #fff3cd;
    padding: 8px;
    border-radius: 5px;
    margin-top: 5px;
    display: none;
}
</style>
</head>
<body>

<div class="form-container">
    <h2>➕ Nouvelle Transaction</h2>
    
    <?php if($success): ?>
        <div class="alert alert-success"><?= $success ?></div>
    <?php endif; ?>
    
    <?php if($error): ?>
        <div class="alert alert-error"><?= $error ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <!-- Compte -->
        <div class="form-group">
            <label>Compte *</label>
            <select name="account_id" id="account_select" required>
                <option value="">-- Sélectionner un compte --</option>
                <?php 
                mysqli_data_seek($accounts, 0); // Reset pointer
                while($acc = mysqli_fetch_assoc($accounts)): 
                ?>
                    <option value="<?= $acc['account_id'] ?>" 
                            data-balance="<?= $acc['balance'] ?>"
                            <?= (isset($_POST['account_id']) && $_POST['account_id'] == $acc['account_id']) ? 'selected' : '' ?>>
                        <?= $acc['account_num'] ?> - <?= $acc['full_name'] ?> 
                        (<?= ucfirst($acc['account_type']) ?>) - Solde: <?= number_format($acc['balance'], 2) ?> MAD
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <!-- Type de transaction -->
        <div class="form-group">
            <label>Type de Transaction *</label>
            <select name="transictions_type" id="trans_type" required>
                <option value="">-- Type --</option>
                <option value="depot" <?= (isset($_POST['transictions_type']) && $_POST['transictions_type'] == 'depot') ? 'selected' : '' ?>>
                    💰 Dépôt
                </option>
                <option value="retrait" <?= (isset($_POST['transictions_type']) && $_POST['transictions_type'] == 'retrait') ? 'selected' : '' ?>>
                    💸 Retrait
                </option>
            </select>
        </div>
        
        <!-- Montant -->
        <div class="form-group">
            <label>Montant (MAD) *</label>
            <input type="number" 
                   name="amount" 
                   id="amount_input"
                   placeholder="Ex: 1000.50" 
                   min="0.01" 
                   step="0.01" 
                   value="<?= isset($_POST['amount']) ? $_POST['amount'] : '' ?>"
                   required>
            <div class="amount-warning" id="warning">
                ⚠️ Le montant dépasse le solde disponible
            </div>
        </div>
        
        <!-- Date -->
        <div class="form-group">
            <label>Date *</label>
            <input type="date" 
                   name="transictions_date" 
                   value="<?= isset($_POST['transictions_date']) ? $_POST['transictions_date'] : date('Y-m-d') ?>"
                   max="<?= date('Y-m-d') ?>"
                   required>
        </div>
        
        <!-- Buttons -->
        <div class="btn-group">
            <a href="dashboard.php" class="btn-back">← Retour</a>
            <button type="submit">Créer Transaction</button>
        </div>
    </form>
</div>

<script>
// Vérification du solde pour les retraits
const accountSelect = document.getElementById('account_select');
const transType = document.getElementById('trans_type');
const amountInput = document.getElementById('amount_input');
const warning = document.getElementById('warning');

function checkBalance() {
    const selectedOption = accountSelect.options[accountSelect.selectedIndex];
    const balance = parseFloat(selectedOption.dataset.balance || 0);
    const amount = parseFloat(amountInput.value || 0);
    const type = transType.value;
    
    if (type === 'retrait' && amount > balance) {
        warning.style.display = 'block';
        amountInput.style.borderColor = '#dc3545';
    } else {
        warning.style.display = 'none';
        amountInput.style.borderColor = '#ddd';
    }
}

accountSelect.addEventListener('change', checkBalance);
transType.addEventListener('change', checkBalance);
amountInput.addEventListener('input', checkBalance);
</script>

</body>
</html>