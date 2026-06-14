<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'promoteur') {
    header('Location: index.php');
    exit();
}

$message = null;

// Création d'un module
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['creer_module'])) {
    $titre = trim($_POST['titre']);
    $desc = trim($_POST['description']);
    $id_prom = $_SESSION['user_id'];

    if (!empty($titre)) {
        $stmt = $pdo->prepare("INSERT INTO modules (titre, description, id_promoteur) VALUES (?, ?, ?)");
        $stmt->execute([$titre, $desc, $id_prom]);
        $message = "Module créé avec succès !";
    }
}

// Récupération des modules et des étudiants pour les certificats
$modules = $pdo->query("SELECT * FROM modules")->fetchAll();

// On cherche les étudiants qui ont validé des leçons (pour l'exemple du TP, on liste les progressions existantes)
$certificats = $pdo->query("
    SELECT u.nom as etudiant_nom, m.titre as module_titre, u.id as etudiant_id, m.id as module_id
    FROM progressions p
    JOIN lecons l ON p.id_lecon = l.id
    JOIN modules m ON l.id_module = m.id
    JOIN utilisateurs u ON p.id_etudiant = u.id
    WHERE p.note >= 10
    GROUP BY u.id, m.id
")->fetchAll();
?>

<div class="row mb-4">
    <div class="col-12 text-center my-4">
        <h1 class="fw-bold text-dark">Espace Promoteur</h1>
        <p class="text-muted">Gestion des modules d'enseignement et diplomation.</p>
    </div>
</div>

<?php if ($message): ?>
    <div class="alert alert-success border-0 shadow-sm mb-4"><?php echo $message; ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4">
            <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-folder-plus text-primary me-2"></i>Créer un Module</h4>
            <form action="promoteur.php" method="POST">
                <input type="hidden" name="creer_module" value="1">
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Intitulé du Module</label>
                    <input type="text" name="titre" class="form-control bg-light" placeholder="Ex: Algorithmique et Structure de Données" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold text-secondary">Description</label>
                    <textarea name="description" class="form-control bg-light" rows="3" placeholder="Objectifs pédagogiques..."></textarea>
                </div>
                <button type="submit" class="btn btn-primary w-100 fw-semibold">Enregistrer le module</button>
            </form>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4">
            <h4 class="fw-bold mb-3 text-dark"><i class="bi bi-award-fill text-warning me-2"></i>Certificats de Validation</h4>
            <?php if (empty($certificats)): ?>
                <p class="text-muted">Aucun certificat disponible pour le moment. Les étudiants doivent d'abord valider des évaluations.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead>
                            <tr>
                                <th>Étudiant</th>
                                <th>Module validé</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($certificats as $cert): ?>
                                <tr>
                                    <td class="fw-bold"><?php echo htmlspecialchars($cert['etudiant_nom']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['module_titre']); ?></td>
                                    <td>
                                        <button onclick="genererCertificat('<?php echo addslashes($cert['etudiant_nom']); ?>', '<?php echo addslashes($cert['module_titre']); ?>')" class="btn btn-sm btn-success">
                                            <i class="bi bi-printer"></i> Imprimer
                                        </button>
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

<script>
function genererCertificat(nom, module) {
    const fenetre = window.open('', '_blank', 'width=800,height=600');
    fenetre.document.write(`
        <html>
        <head>
            <title>Certificat de Réussite</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { background: #f1f5f9; padding: 50px; font-family: sans-serif; }
                .diplome { background: white; border: 10px double #1e3a8a; padding: 50px; text-center; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); text-align: center; }
                h1 { color: #1e3a8a; font-family: serif; font-size: 3.5rem; }
            </style>
        </head>
        <body>
            <div class="diplome">
                <p class="text-muted uppercase tracking-widest fw-bold">Certificat officiel de réussite</p>
                <h1 class="my-4">UNIVERSITÉ DE YAOUNDÉ I</h1>
                <p class="fs-4">Le présent document atteste que l'étudiant(e)</p>
                <h2 class="text-primary my-3 fw-bold">${nom}</h2>
                <p class="fs-4">a validé avec succès l'ensemble des compétences du module d'enseignement :</p>
                <h3 class="text-dark my-4 fw-italic">"${module}"</h3>
                <div class="mt-5 d-flex justify-content-around">
                    <div><hr style="width:150px;"><span>Le Promoteur</span></div>
                    <div><hr style="width:150px;"><span>Le Jury</span></div>
                </div>
            </div>
            <script>window.print();<\/script>
        </body>
        </html>
    `);
}
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>