<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Protection de la page : réservé uniquement aux enseignants
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'enseignant') {
    header('Location: index.php');
    exit();
}

$message_succes = null;
$message_erreur = null;
$id_enseignant = $_SESSION['user_id'];

// Traitement du formulaire d'ajout de cours et d'évaluation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre_lecon = trim($_POST['titre']);
    $id_module = intval($_POST['id_module']);
    $question = trim($_POST['question']);
    $option_a = trim($_POST['option_a']);
    $option_b = trim($_POST['option_b']);
    $option_c = trim($_POST['option_c']);
    $reponse_correcte = trim($_POST['reponse_correcte']);

    // Gestion du fichier uploadé (PDF obligatoire pour le TP)
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $file_name = $_FILES['pdf_file']['name'];
        $file_tmp = $_FILES['pdf_file']['tmp_name'];
        $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if ($file_ext === 'pdf') {
            // Génération d'un nom unique pour éviter les collisions de fichiers
            $new_file_name = uniqid('course_', true) . '.' . $file_ext;
            $upload_dir = 'public/uploads/';
            $dest_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp, $dest_path)) {
                try {
                    $pdo->beginTransaction();

                    // 1. Insertion de la leçon
                    $stmt = $pdo->prepare("INSERT INTO lecons (titre, contenu_path, id_module, id_enseignant) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$titre_lecon, $dest_path, $id_module, $id_enseignant]);
                    $id_lecon = $pdo->lastInsertId();

                    // 2. Insertion de l'évaluation associée
                    $stmt_eval = $pdo->prepare("INSERT INTO evaluations (id_lecon, question, option_a, option_b, option_c, reponse_correcte) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt_eval->execute([$id_lecon, $question, $option_a, $option_b, $option_c, $reponse_correcte]);

                    $pdo->commit();
                    $message_succes = "La leçon et son évaluation ont été ajoutées avec succès.";
                } catch (Exception $e) {
                    $pdo->rollBack();
                    $message_erreur = "Erreur lors de l'enregistrement en base de données : " . $e->getMessage();
                }
            } else {
                $message_erreur = "Erreur lors du déplacement du fichier vers le dossier des uploads.";
            }
        } else {
            $message_erreur = "Seuls les fichiers au format PDF sont acceptés pour ce support de cours.";
        }
    } else {
        $message_erreur = "Veuillez sélectionner un fichier PDF valide pour la leçon.";
    }
}

// Récupération des modules disponibles pour le menu déroulant
$modules = $pdo->query("SELECT * FROM modules")->fetchAll();

// Récupération des leçons déjà publiées par cet enseignant
$stmt_liste = $pdo->prepare("
    SELECT l.titre AS lecon_titre, m.titre AS module_titre, e.question 
    FROM lecons l 
    JOIN modules m ON l.id_module = m.id = m.id 
    LEFT JOIN evaluations e ON e.id_lecon = l.id
    WHERE l.id_enseignant = ?
");
$stmt_liste->execute([$id_enseignant]);
$mes_lecons = $stmt_liste->fetchAll();
?>

<div class="row mb-5">
    <div class="col-12 text-center my-4">
        <h1 class="fw-bold text-dark">Espace Pédagogique</h1>
        <p class="text-muted">Concevez vos leçons et associez-y des évaluations interactives.</p>
    </div>
</div>

<?php if ($message_succes): ?>
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($message_succes); ?></div>
    </div>
<?php endif; ?>

<?php if ($message_erreur): ?>
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
        <div><?php echo htmlspecialchars($message_erreur); ?></div>
    </div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-plus-circle text-primary me-2"></i>Nouvelle Leçon</h3>
            
            <form action="enseignant.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Rattachement au Module</label>
                    <select name="id_module" class="form-select bg-light" required>
                        <option value="" disabled selected>Choisir un module...</option>
                        <?php foreach ($modules as $mod): ?>
                            <option value="<?php echo $mod['id']; ?>"><?php echo htmlspecialchars($mod['titre']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Titre de la leçon</label>
                    <input type="text" name="titre" class="form-control bg-light" placeholder="Ex: Introduction aux architectures n-tiers" required>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Support de Cours (PDF)</label>
                    <input type="file" name="pdf_file" class="form-control bg-light" accept=".pdf" required>
                </div>

                <hr class="text-muted my-4">
                <h5 class="fw-bold mb-3 text-dark"><i class="bi bi-patch-question text-primary me-2"></i>Évaluation de fin de leçon</h5>

                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Question du Quiz</label>
                    <textarea name="question" class="form-control bg-light" rows="2" placeholder="Saisissez l'énoncé de la question de validation..." required></textarea>
                </div>

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Option A</label>
                        <input type="text" name="option_a" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Option B</label>
                        <input type="text" name="option_b" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Option C</label>
                        <input type="text" name="option_c" class="form-control bg-light" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold text-secondary">Bonne Réponse</label>
                    <select name="reponse_correcte" class="form-select bg-light" required>
                        <option value="A">Option A</option>
                        <option value="B">Option B</option>
                        <option value="C">Option C</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold shadow-sm">
                    <i class="bi bi-cloud-arrow-up-fill me-2"></i> Publier la leçon et le quiz
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm p-4 h-100">
            <h3 class="fw-bold mb-4 text-dark"><i class="bi bi-journal-bookmark-fill text-primary me-2"></i>Vos publications</h3>
            
            <?php if (empty($mes_lecons)): ?>
                <div class="text-center my-5 py-4">
                    <i class="bi bi-folder-symlink text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">Aucune leçon publiée pour le moment.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle border-0">
                        <thead class="table-light">
                            <tr>
                                <th class="border-0 rounded-start">Module</th>
                                <th class="border-0">Leçon</th>
                                <th class="border-0 rounded-end">Quiz associé</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mes_lecons as $lec): ?>
                                <tr>
                                    <td class="small fw-bold text-primary"><?php echo htmlspecialchars($lec['module_titre']); ?></td>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($lec['lecon_titre']); ?></td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1.5 rounded-pill border border-success-subtle">
                                            <i class="bi bi-check2-circle me-1"></i> Configuré
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</div> <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>