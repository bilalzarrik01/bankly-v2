<?php
session_start();
require "config/db.php";

if (!isset($_SESSION['user'])) {
    header("Location: index.php");
    exit;
}

$result = mysqli_query($connect, "SELECT * FROM clients");
?>

<h2>Liste des clients</h2>
<a href="create.php">Ajouter client</a>

<table border="1" cellpadding="8">
  <tr>
    <th>Nom</th>
    <th>Email</th>
    <th>CIN</th>
    <th>Actions</th>
  </tr>
  <?php while($c = mysqli_fetch_assoc($result)): ?>
  <tr>
    <td><?= htmlspecialchars($c['name']) ?></td>
    <td><?= htmlspecialchars($c['email']) ?></td>
    <td><?= htmlspecialchars($c['cin']) ?></td>
    <td>
      <a href="edit.php?id=<?= $c['id'] ?>">Edit</a>
      <a href="delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Supprimer ce client ?')">Delete</a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>
