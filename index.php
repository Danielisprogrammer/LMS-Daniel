<?php
die("<h1 style='color: white; background: red; padding: 20px; text-align: center;'>OUI, C'EST LE BON FICHIER !</h1>");
require_once 'config/db.php';
session_start();

// --- CONFIGURATION AUTOMATIQUE DES COMPTES DE TEST ---
// Ce bloc s'assure que la base de données a exactement les bons mots de passe hachés
$password_hash = password_hash('password', PASSWORD_BCRYPT);
try {
    // On vérifie si les comptes existent déjà pour éviter de saturer la base
    $check = $pdo->query("SELECT COUNT(*) FROM utilisateurs")->fetchColumn();
    if ($check == 0) {
        $stmt = $pdo->prepare("INSERT INTO utilisateurs (id, nom, email, mot_de_passe, role) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([1, 'Daniel Étudiant', 'etudiant@lms.com', $password_hash, 'etudiant']);
        $stmt->execute([2, 'Professeur Enseignant', 'prof@lms.com', $password_hash, 'enseignant']);
        $stmt->execute([3, 'Monsieur le Promoteur', 'admin@lms.com', $password_hash, 'promoteur']);
    }
} catch (Exception $e) {
    // En cas d'erreur de table manquante
}
// --- FIN DE LA CONFIGURATION AUTOMATIQUE ---

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$erreur = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
    $password = trim($_POST['password']);

    if ($email && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        

        if ($user && password_verify($password, $user['mot_de_passe'])) {
            session_regenerate_id(true);
            
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nom'] = $user['nom'];
            $_SESSION['user_role'] = $user['role'];

            header('Location: dashboard.php');
            exit();
        } else {
            $erreur = "Identifiants incorrects ou utilisateur introuvable.";
        }
    } else {
        $erreur = "Veuillez saisir un format d'adresse email valide.";
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center align-items-center" style="min-height: 80vh;">
    <div class="col-md-5">
        <div class="card shadow border-0 rounded-3">
            <div class="card-body p-5">
                <div class="text-center mb-4">
                    <i class="bi bi-mortarboard-fill text-primary" style="font-size: 3rem;"></i>
                    <h2 class="fw-bold mt-2 text-dark">Portail UniLMS</h2>
                    <p class="text-muted">Accédez à vos cours et évaluations</p>
                </div>

                <?php if ($erreur): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <div><?php echo htmlspecialchars($erreur); ?></div>
                    </div>
                <?php endif; ?>

                <form action="index.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label text-secondary fw-semibold">Adresse Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-envelope text-muted"></i></span>
                            <input type="email" name="email" id="email" class="form-control border-start-0 bg-light" placeholder="exemple@lms.com" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label text-secondary fw-semibold">Mot de passe</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-lock text-muted"></i></span>
                            <input type="password" name="password" id="password" class="form-control border-start-0 bg-light" placeholder="••••••••" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Se connecter
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card mt-3 bg-light border-0">
            <div class="card-body py-2 px-3 text-center">
                <small class="text-muted">
                    <strong>Tests :</strong> admin@lms.com | prof@lms.com | etudiant@lms.com (Mdp: password)
                </small>
            </div>
        </div>
    </div>
</div>
</body>
</html>