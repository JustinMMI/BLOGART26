
<?php
include '../../../header.php';

// LOGOUT
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

$error = '';

// Traitement login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Vérification du captcha
    if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
        $error = "Captcha requis";
    } else {
        $token = $_POST['g-recaptcha-response'];
        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $data = array(
            'secret' => '6Lcv_losAAAAALgmId0ujnWyFEzApB_LYkdkIALq',
            'response' => $token
        );
        $options = array(
            'http' => array(
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'method' => 'POST',
                'content' => http_build_query($data)
            )
        );

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        $response = json_decode($result);

        if (!($response->success && $response->score >= 0.5)) {
            $error = "Vous êtes peut-être un robot. Captcha échoué.";
        }
    }

    if ($error) {
        // Captcha KO, on stoppe ici
    } else {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

    // Récupération du membre
    $membre = sql_select("MEMBRE", "*", "eMailMemb = '$email'");

    if (empty($membre)) {
        $error = "Email ou mot de passe incorrect.";
    } else {
        $membre = $membre[0];

        if (!password_verify($password, $membre['passMemb'])) {
            $error = "Email ou mot de passe incorrect.";
        } else {
            // Récupération du statut
            $statut = sql_select(
                "STATUT",
                "libStat",
                "numStat = " . $membre['numStat']
            )[0]['libStat'];

            $_SESSION['user'] = [
                'id'     => $membre['numMemb'],
                'email'  => $membre['eMailMemb'],
                'pseudo' => $membre['pseudoMemb'],
                'statut' => $statut
            ];

            if ($statut === 'admin') {
                header('Location: ../dashboard.php');
            } else {
                header('Location: /index.php'); // Home
            }
            exit;
        }
    }
    }
}
?>

<div class="container mt-5">
    <h2>Connexion</h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form id="form-recaptcha" method="post">
        <button class="g-recaptcha" data-sitekey="6Lcv_losAAAAAGBCPCiH7FwZBeXDoHKPjjQuygZJ" data-callback='onSubmit' data-action='submit'>Submit </button>
            <input class="form-control mb-2" name="email" type="email" placeholder="Email" required>
            <input class="form-control mb-2" name="password" type="password" placeholder="Mot de passe" required>

        <button class="btn btn-primary mt-2">Connexion</button>
        <a href="signup.php" class="btn btn-secondary mt-2">Créer un compte</a>
    </form>
</div>