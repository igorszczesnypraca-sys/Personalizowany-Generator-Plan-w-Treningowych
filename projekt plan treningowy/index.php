<?php
require 'db.php';

// --- KONFIGURACJA ---
$cwiczenia_czasowe_zawsze = ['Plank (Deska)', 'Spacer farmera'];
$json_czasowe = json_encode($cwiczenia_czasowe_zawsze);

// --- GENERATOR PLANU ---
function dobierzCwiczenie($sprzet_usera, $opcje) {
    if (isset($opcje['sztanga_lawka']) && in_array('sztanga', $sprzet_usera) && in_array('lawka', $sprzet_usera)) return $opcje['sztanga_lawka'];
    if (isset($opcje['hantle']) && in_array('hantle', $sprzet_usera)) return $opcje['hantle'];
    if (isset($opcje['drazek']) && in_array('drazek', $sprzet_usera)) return $opcje['drazek'];
    return $opcje['default'];
}

function zaladujPlanStartowy($pdo, $user_id, $wariant, $sprzet = []) {
    if ($wariant == 'silownia') {
        $plan = [
            'Poniedziałek' => ['Wyciskanie sztangi na ławce' => [4,10], 'Rozpiętki' => [3,12], 'Brzuszki' => [3,20]],
            'Wtorek'       => ['Martwy ciąg' => [4,8], 'Ściąganie drążka wyciągu górnego' => [4,10]],
            'Środa'        => [], 
            'Czwartek'     => ['Przysiady' => [4,8], 'Wypychanie nóg na suwnicy' => [3,12]],
            'Piątek'       => ['Wyciskanie żołnierskie (barki)' => [4,10], 'Uginanie ramion ze sztangą (biceps)' => [3,12]],
            'Sobota'       => ['Orbitrek' => [1,30]], 
            'Niedziela'    => []
        ];
    } else {
        $klatka_glowne = dobierzCwiczenie($sprzet, ['sztanga_lawka' => 'Wyciskanie sztangi na ławce', 'hantle' => 'Wyciskanie hantli na skosie', 'default' => 'Pompki']);
        $klatka_pomoc = dobierzCwiczenie($sprzet, ['hantle' => 'Rozpiętki', 'default' => 'Pompki diamentowe']);
        $plecy_glowne = dobierzCwiczenie($sprzet, ['sztanga_lawka' => 'Wiosłowanie sztangą', 'drazek' => 'Podciąganie na drążku', 'default' => 'Supermans (Grzbiety)']);
        $nogi_glowne = dobierzCwiczenie($sprzet, ['sztanga_lawka' => 'Przysiady', 'hantle' => 'Wykroki z hantlami', 'default' => 'Przysiady']);
        $barki_glowne = dobierzCwiczenie($sprzet, ['sztanga_lawka' => 'Wyciskanie żołnierskie (barki)', 'default' => 'Pompki z nogami na podwyższeniu']);
        $barki_boczne = dobierzCwiczenie($sprzet, ['hantle'  => 'Wznosy hantli bokiem', 'default' => 'Plank (Deska)']);

        $plan = [
            'Poniedziałek' => [$klatka_glowne => [4,10], $klatka_pomoc => [3,12]],
            'Wtorek'       => [$plecy_glowne => [4,10], 'Brzuszki' => [3,20]],
            'Środa'        => ['Bieg w miejscu (High Knees)' => [1, 15]], 
            'Czwartek'     => [],
            'Piątek'       => [$nogi_glowne => [4,15], $barki_glowne => [3,10]],
            'Sobota'       => [$barki_boczne => [3,1], 'Burpees' => [3,10]],
            'Niedziela'    => []
        ];
    }
    $stmt_ins = $pdo->prepare("INSERT INTO plan_treningowy (user_id, dzien_tygodnia, cwiczenie_id, powtorzenia, serie) VALUES (?, ?, ?, ?, ?)");
    $stmt_get_id = $pdo->prepare("SELECT id FROM rodzaje_cwiczen WHERE nazwa = ?");
    foreach ($plan as $dzien => $cwiczenia) {
        if (empty($cwiczenia)) continue;
        foreach ($cwiczenia as $nazwa => $params) {
            $stmt_get_id->execute([$nazwa]);
            $id = $stmt_get_id->fetchColumn();
            if ($id) $stmt_ins->execute([$user_id, $dzien, $id, $params[1], $params[0]]);
        }
    }
}

// --- OBSŁUGA POST ---
if (isset($_POST['action']) && $_POST['action'] == 'register') {
    $login = trim($_POST['reg_login']); $haslo = trim($_POST['reg_haslo']);
    $czy_chce_plan = isset($_POST['chce_plan']); $typ_miejsca = $_POST['miejsce'] ?? 'dom'; 
    $sprzet = isset($_POST['sprzet']) ? $_POST['sprzet'] : [];
    $stmt = $pdo->prepare("INSERT INTO uzytkownicy (login, haslo) VALUES (?, ?)");
    try {
        $stmt->execute([$login, password_hash($haslo, PASSWORD_DEFAULT)]);
        $new_id = $pdo->lastInsertId();
        if ($czy_chce_plan) {
            zaladujPlanStartowy($pdo, $new_id, $typ_miejsca, $sprzet);
            $_SESSION['msg'] = "Konto założone! Plan dostosowany.";
        } else { $_SESSION['msg'] = "Konto założone!"; }
    } catch (PDOException $e) { $_SESSION['msg'] = "Login zajęty."; }
    header("Location: index.php"); exit;
}
if (isset($_POST['action']) && $_POST['action'] == 'login') {
    $stmt = $pdo->prepare("SELECT * FROM uzytkownicy WHERE login = ?");
    $stmt->execute([trim($_POST['login'])]);
    $user = $stmt->fetch();
    if ($user && password_verify(trim($_POST['haslo']), $user['haslo'])) {
        $_SESSION['user_id'] = $user['id']; $_SESSION['login'] = $user['login'];
    } else { $_SESSION['msg'] = "Błędny login lub hasło."; }
    header("Location: index.php"); exit;
}
if (isset($_GET['logout'])) { session_destroy(); header("Location: index.php"); exit; }
$is_logged = isset($_SESSION['user_id']);
if ($is_logged && isset($_POST['add_to_plan'])) {
    $stmt = $pdo->prepare("INSERT INTO plan_treningowy (user_id, dzien_tygodnia, cwiczenie_id, powtorzenia, serie) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $_POST['dzien'], $_POST['cwiczenie_id'], $_POST['powtorzenia'], $_POST['serie']]);
    header("Location: index.php"); exit;
}
if ($is_logged && isset($_POST['add_exercise_type'])) {
    try { $pdo->prepare("INSERT INTO rodzaje_cwiczen (nazwa) VALUES (?)")->execute([$_POST['nazwa_cwiczenia']]); } catch(Exception $e) {}
    header("Location: index.php"); exit;
}
if ($is_logged && isset($_POST['delete_entry'])) {
    $stmt = $pdo->prepare("DELETE FROM plan_treningowy WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['entry_id'], $_SESSION['user_id']]);
    $_SESSION['msg'] = "Usunięto."; header("Location: index.php"); exit;
}
if ($is_logged && isset($_POST['edit_entry_action'])) {
    $stmt = $pdo->prepare("UPDATE plan_treningowy SET serie = ?, powtorzenia = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$_POST['edit_serie'], $_POST['edit_powt'], $_POST['edit_id'], $_SESSION['user_id']]);
    $_SESSION['msg'] = "Zaktualizowano."; header("Location: index.php"); exit;
}

// --- POBIERANIE ---
$moj_plan = []; $rodzaje_cwiczen = [];
if ($is_logged) {
    $rodzaje_cwiczen = $pdo->query("SELECT * FROM rodzaje_cwiczen ORDER BY nazwa")->fetchAll(PDO::FETCH_ASSOC);
    $stmt = $pdo->prepare("SELECT p.dzien_tygodnia, p.id, p.serie, p.powtorzenia, r.nazwa as nazwa_cwiczenia FROM plan_treningowy p JOIN rodzaje_cwiczen r ON p.cwiczenie_id = r.id WHERE p.user_id = ? ORDER BY FIELD(dzien_tygodnia, 'Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela')");
    $stmt->execute([$_SESSION['user_id']]);
    $moj_plan = $stmt->fetchAll(PDO::FETCH_GROUP);
}

$dni_pl = ['Poniedziałek', 'Wtorek', 'Środa', 'Czwartek', 'Piątek', 'Sobota', 'Niedziela'];
$miesiace_pl = [1=>'Styczeń', 'Luty', 'Marzec', 'Kwiecień', 'Maj', 'Czerwiec', 'Lipiec', 'Sierpień', 'Wrzesień', 'Październik', 'Listopad', 'Grudzień'];
$aktualny_dzien_tyg = $dni_pl[date('N') - 1]; $aktualny_dzien_miesiaca = date('j');
$numer_miesiaca = date('n'); $rok = date('Y'); $ilosc_dni_w_miesiacu = date('t');
$pierwszy_dzien_tygodnia_miesiac = date('N', strtotime("$rok-$numer_miesiaca-01"));
$aktualny_numer_tygodnia = date('W');
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <title>Aplikacja Treningowa</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .info-tip {
            font-size: 0.85em; color: #666; margin-top: 5px; background: #e9ecef;
            padding: 8px; border-radius: 4px; border-left: 3px solid var(--primary);
        }
        body.dark-mode .info-tip { background: #333; color: #ccc; }
    </style>
</head>
<body>

<div class="container">
    <h1>🏋️ Aplikacja Treningowa <span id="theme-toggle" title="Zmień motyw">🌙</span></h1>
    <?php if (isset($_SESSION['msg'])): ?>
        <div style="background: #ffeeba; padding: 10px; margin-bottom: 10px; border-radius: 4px; color: #333;">
            <?= htmlspecialchars($_SESSION['msg']); unset($_SESSION['msg']); ?>
        </div>
    <?php endif; ?>

    <?php if (!$is_logged): ?>
        <div class="auth-box">
            <h2>Zaloguj się</h2>
            <form method="POST">
                <input type="text" name="login" placeholder="Login" required>
                <input type="password" name="haslo" placeholder="Hasło" required>
                <button type="submit" name="action" value="login">Wejdź</button>
            </form>
            <hr><p style="text-align:center">Nie masz konta?</p>
            <button onclick="document.getElementById('registerModal').style.display='block'" style="background-color: #28a745;">Utwórz konto</button>
        </div>
        <div id="registerModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="document.getElementById('registerModal').style.display='none'">&times;</span>
                <h2>Rejestracja</h2>
                <form method="POST">
                    <input type="text" name="reg_login" placeholder="Wybierz Login" required>
                    <input type="password" name="reg_haslo" placeholder="Wybierz Hasło" required>
                    <div class="plan-options">
                        <div style="margin-bottom: 15px;">
                            <label class="option-card" style="flex-direction: row; justify-content: flex-start; gap: 10px; padding: 10px;">
                                <input type="checkbox" name="chce_plan" id="checkPlan" onchange="togglePlanOptions()" style="width: auto;">
                                <span>🚀 Chcę otrzymać gotowy plan startowy</span>
                            </label>
                        </div>
                        <div id="miejsceSelect" style="display: none;">
                            <div class="options-grid">
                                <input type="radio" name="miejsce" value="dom" id="opt_dom" class="option-input" checked onchange="toggleEquipment()"><label for="opt_dom" class="option-card"><span>🏠</span>W Domu</label>
                                <input type="radio" name="miejsce" value="silownia" id="opt_silownia" class="option-input" onchange="toggleEquipment()"><label for="opt_silownia" class="option-card"><span>🏋️</span>Na Siłowni</label>
                            </div>
                            <div id="sprzetSection">
                                <p>Masz sprzęt?</p>
                                <div class="options-grid full-width">
                                    <input type="checkbox" name="sprzet[]" value="hantle" id="eq_hantle" class="option-input"><label for="eq_hantle" class="option-card"><span> Hantle</span></label>
                                    <input type="checkbox" name="sprzet[]" value="sztanga" id="eq_sztanga" class="option-input"><label for="eq_sztanga" class="option-card"><span> Sztanga</span></label>
                                    <input type="checkbox" name="sprzet[]" value="lawka" id="eq_lawka" class="option-input"><label for="eq_lawka" class="option-card"><span> Ławka</span></label>
                                    <input type="checkbox" name="sprzet[]" value="drazek" id="eq_drazek" class="option-input"><label for="eq_drazek" class="option-card"><span> Drążek</span></label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="submit" name="action" value="register" style="margin-top: 20px;">Zarejestruj się</button>
                </form>
            </div>
        </div>

    <?php else: ?>
        <div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 20px; gap: 15px;">
            <span>Zalogowany jako: <strong><?= htmlspecialchars($_SESSION['login']) ?></strong></span>
            <a href="?logout=1" class="btn-logout">Wyloguj</a>
        </div>
        <div class="flex-row">
            <div class="col panel">
                <h3>➕ Dodaj do planu</h3>
                <form method="POST">
                    <select name="dzien"><?php foreach($dni_pl as $d) echo "<option>$d</option>"; ?></select>
                    <select name="cwiczenie_id"><?php foreach($rodzaje_cwiczen as $rc) echo "<option value='{$rc['id']}'>".htmlspecialchars($rc['nazwa'])."</option>"; ?></select>
                    <div style="display: flex; gap: 10px;">
                        <input type="number" name="serie" placeholder="Serie" required>
                        <input type="number" name="powtorzenia" placeholder="Powtórzenia / Czas" required>
                    </div>
                    <div class="info-tip">
                        ℹ️ <strong>Wskazówka:</strong> Jeśli ustawisz <strong>1 serię</strong>, system potraktuje "Powtórzenia" jako czas w minutach (np. dla biegania).
                    </div>
                    <button type="submit" name="add_to_plan" style="margin-top: 10px;">Zapisz</button>
                </form>
            </div>
            <div class="col panel">
                <h3>🆕 Brak ćwiczenia?</h3>
                <form method="POST">
                    <input type="text" name="nazwa_cwiczenia" placeholder="Nazwa np. Yoga" required>
                    <button type="submit" name="add_exercise_type" style="background:#28a745;">Dodaj do bazy</button>
                </form>
            </div>
        </div>
        
        <div class="calendar-wrapper">
            <div class="calendar-header"><?= $miesiace_pl[$numer_miesiaca] . " " . $rok ?></div>
            <div class="calendar-grid">
                <?php foreach(['Pn','Wt','Śr','Cz','Pt','So','Nd'] as $d) echo "<div class='cal-day-name'>$d</div>"; ?>
                <?php 
                $brakujace_dni_start = $pierwszy_dzien_tygodnia_miesiac - 1; 
                $poprzedni_miesiac_dni = date('t', mktime(0, 0, 0, $numer_miesiaca - 1, 1, $rok));
                for ($i = $brakujace_dni_start; $i > 0; $i--) {
                    $dzien_num = $poprzedni_miesiac_dni - $i + 1;
                    $nazwa_dnia = $dni_pl[$pierwszy_dzien_tygodnia_miesiac - $i - 1];
                    echo "<div class='cal-cell other-month' onclick=\"otworzDzien('$nazwa_dnia', this)\">$dzien_num</div>";
                }
                for ($d = 1; $d <= $ilosc_dni_w_miesiacu; $d++) {
                    $timestamp = strtotime("$rok-$numer_miesiaca-$d");
                    $dzien_tyg_nazwa = $dni_pl[date('N', $timestamp) - 1]; 
                    $klasy = "cal-cell";
                    if ($d == $aktualny_dzien_miesiaca) $klasy .= " today active"; 
                    if (date('W', $timestamp) == $aktualny_numer_tygodnia) $klasy .= " current-week";
                    echo "<div class='$klasy' onclick=\"otworzDzien('$dzien_tyg_nazwa', this)\">$d</div>";
                }
                $ostatni_dzien_tygodnia = date('N', strtotime("$rok-$numer_miesiaca-$ilosc_dni_w_miesiacu"));
                if ($ostatni_dzien_tygodnia < 7) {
                    for ($d = 1; $d <= (7 - $ostatni_dzien_tygodnia); $d++) {
                        $nazwa_dnia = $dni_pl[$ostatni_dzien_tygodnia + $d - 1];
                        echo "<div class='cal-cell other-month' onclick=\"otworzDzien('$nazwa_dnia', this)\">$d</div>";
                    }
                }
                ?>
            </div>
        </div>

        <?php foreach ($dni_pl as $d): ?>
            <div id="content-<?= $d ?>" class="day-content <?= $d===$aktualny_dzien_tyg?'active-content':'' ?>">
                <h3 style="border-bottom: 2px solid var(--primary); padding-bottom: 10px; margin-bottom: 20px;">Plan na: <?= $d ?></h3>
                <?php if (isset($moj_plan[$d])): ?>
                    <table class="plan-table">
                        <thead><tr><th>Ćwiczenie</th><th>Serie</th><th>Powtórzenia / Czas</th><th style="width: 140px;">Akcje</th></tr></thead>
                        <tbody>
                            <?php foreach ($moj_plan[$d] as $w): 
                                $czy_czasowe = in_array($w['nazwa_cwiczenia'], $cwiczenia_czasowe_zawsze) || $w['serie'] == 1;
                                $jednostka = $czy_czasowe ? ' min' : '';
                            ?>
                                <tr>
                                    <td><?= htmlspecialchars($w['nazwa_cwiczenia']) ?></td>
                                    <td><?= $w['serie'] ?></td>
                                    <td><?= $w['powtorzenia'] . $jednostka ?></td>
                                    <td>
                                        <button class="btn-sm btn-edit" onclick="openEditModal(<?= $w['id'] ?>, '<?= htmlspecialchars($w['nazwa_cwiczenia']) ?>', <?= $w['serie'] ?>, <?= $w['powtorzenia'] ?>)">Edytuj</button>
                                        <form method="POST" class="form-inline" onsubmit="return confirm('Na pewno usunąć?');"><input type="hidden" name="entry_id" value="<?= $w['id'] ?>"><button type="submit" name="delete_entry" class="btn-sm btn-delete">Usuń</button></form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state"><h3>💤 Dzień wolny</h3><p>Brak zaplanowanych treningów na <?= $d ?>.</p></div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <div id="editModal" class="modal">
            <div class="modal-content">
                <span class="close-btn" onclick="document.getElementById('editModal').style.display='none'">&times;</span>
                <h2>Edytuj Trening</h2>
                <form method="POST">
                    <input type="hidden" name="edit_id" id="edit_id_input">
                    <p>Ćwiczenie:</p><div id="edit_cwiczenie_name" class="edit-info"></div>
                    <label>Liczba Serii:</label>
                    <input type="number" name="edit_serie" id="edit_serie_input" required oninput="checkIfTimeBased()">
                    <label id="label_powt">Liczba Powtórzeń:</label>
                    <input type="number" name="edit_powt" id="edit_powt_input" required>
                    <button type="submit" name="edit_entry_action" style="margin-top: 15px;">Zapisz zmiany</button>
                </form>
            </div>
        </div>

        <script>
            const cwiczeniaZawszeCzasowe = <?= $json_czasowe ?>;
            let currentExerciseName = "";

            function openEditModal(id, nazwa, serie, powt) {
                document.getElementById('editModal').style.display = 'block';
                document.getElementById('edit_id_input').value = id;
                document.getElementById('edit_cwiczenie_name').innerText = nazwa;
                document.getElementById('edit_serie_input').value = serie;
                document.getElementById('edit_powt_input').value = powt;
                currentExerciseName = nazwa;
                checkIfTimeBased();
            }

            function checkIfTimeBased() {
                const serie = parseInt(document.getElementById('edit_serie_input').value);
                const label = document.getElementById('label_powt');
                if (cwiczeniaZawszeCzasowe.includes(currentExerciseName) || serie === 1) {
                    label.innerText = "Czas trwania (minuty):";
                } else {
                    label.innerText = "Liczba Powtórzeń:";
                }
            }

            function otworzDzien(nazwaDnia, elementKlikniety) {
                if (elementKlikniety) {
                    document.querySelectorAll('.cal-cell').forEach(c => c.classList.remove('active'));
                    elementKlikniety.classList.add('active');
                }
                document.querySelectorAll('.day-content').forEach(c => c.classList.remove('active-content'));
                document.getElementById('content-' + nazwaDnia).classList.add('active-content');
            }
        </script>
    <?php endif; ?>
    
    <footer class="project-footer">
        <h3>Tygodniowy Plan Treningowy</h3>
        <p><strong>Autorzy:</strong> Igor Szczęsny, Maciej Zybała</p>
        <p><strong>Data:</strong> 30.12.2025</p>
    </footer>
</div>

<script>
    const modal = document.getElementById("registerModal"); const editModal = document.getElementById("editModal");
    window.onclick = function(e) { if (e.target == modal) modal.style.display = "none"; if (e.target == editModal) editModal.style.display = "none"; }
    function togglePlanOptions() { const checkbox = document.getElementById("checkPlan"); const options = document.getElementById("miejsceSelect"); options.style.display = checkbox.checked ? "block" : "none"; }
    function toggleEquipment() { const domRadio = document.getElementById("opt_dom"); const eqSection = document.getElementById("sprzetSection"); if (domRadio.checked) eqSection.style.display = "block"; else eqSection.style.display = "none"; }
    const toggleBtn = document.getElementById('theme-toggle'); const body = document.body;
    const currentTheme = localStorage.getItem('theme'); if (currentTheme === 'dark') { body.classList.add('dark-mode'); toggleBtn.textContent = '☀️'; }
    toggleBtn.addEventListener('click', () => { body.classList.toggle('dark-mode'); if (body.classList.contains('dark-mode')) { toggleBtn.textContent = '☀️'; localStorage.setItem('theme', 'dark'); } else { toggleBtn.textContent = '🌙'; localStorage.setItem('theme', 'light'); } });
</script>
</body>
</html>