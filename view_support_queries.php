<?php
include 'db_connect.php';

// Fetch support queries
$result = $conn->query("SELECT * FROM support_queries ORDER BY submitted_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>View Support Queries - FreightX</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to right, #1e90ff, #32cd32);
            color: #fff;
            padding: 20px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: rgba(0,0,0,0.7);
            color: white;
        }
        th, td {
            padding: 15px;
            border-bottom: 1px solid #444;
            text-align: left;
        }
        th {
            background: #444;
        }
        tr:hover {
            background: #555;
        }
    </style>
</head>
<body>

    <h1>Customer Support Queries</h1>

    <table>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Message</th>
            <th>Submitted At</th>
        </tr>

        <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
            <td><?= $row['id'] ?></td>
            <td><?= htmlspecialchars($row['name']) ?></td>
            <td><?= htmlspecialchars($row['email']) ?></td>
            <td><?= nl2br(htmlspecialchars($row['message'])) ?></td>
            <td><?= $row['submitted_at'] ?></td>
        </tr>
        <?php endwhile; ?>

    </table>

</body>
</html>
