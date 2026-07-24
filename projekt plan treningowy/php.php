<?php
// Przykład bezpiecznego usuwania rekordu 
$stmt = $pdo->prepare("DELETE FROM plan_treningowy WHERE id = ? AND user_id = ?"); 
// Parametry są przesyłane oddzielnie od zapytania SQL 
$stmt->execute([$_POST['entry_id'], $_SESSION['user_id']]);

>
