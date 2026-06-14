<?php
require_once 'config/db.php';
require_once 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'etudiant') {
    header('Location: index.php');
    exit();
}

$id_etudiant = $_SESSION['user_id'];

// Chargement des leçons avec les détails du module et de l'évaluation correspondante
$lecons = $pdo->query("
    SELECT l.id as lecon_id, l.titre as lecon_titre, l.contenu_path, m.titre as module_titre,
           e.question, e.option_a, e.option_b, e.option_c, e.reponse_correcte
    FROM lecons l
    JOIN modules m ON l.id_module = m.id
    LEFT JOIN evaluations e ON e.id_lecon = l.id
")->fetchAll();
?>

<div class="row my-4">
    <div class="col-12 text-center">
        <h1 class="fw-bold text-dark">Mon Espace d'Apprentissage</h1>
        <p class="text-muted">Consultez vos cours en ligne et validez vos acquis instantanément.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm p-3">
            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-book-half text-primary me-2"></i>Cours disponibles</h5>
            <div class="list-group list-group-flush" id="course-list">
                <?php if (empty($lecons)): ?>
                    <p class="text-muted p-3">Aucun cours n'est encore disponible sur la plateforme.</p>
                <?php else: ?>
                    <?php foreach ($lecons as $index => $lec): ?>
                        <button class="list-group-item list-group-item-action border-0 rounded-3 mb-2 p-3 text-start card-hover <?php echo $index === 0 ? 'active' : ''; ?>" 
                                onclick="chargerCours('<?php echo htmlspecialchars($lec['lecon_titre']); ?>', '<?php echo $lec['contenu_path']; ?>', <?php echo $lec['lecon_id']; ?>, '<?php echo htmlspecialchars($lec['question']); ?>', '<?php echo htmlspecialchars($lec['option_a']); ?>', '<?php echo htmlspecialchars($lec['option_b']); ?>', '<?php echo htmlspecialchars($lec['option_c']); ?>')">
                            <span class="d-block small text-uppercase opacity-75 fw-bold"><?php echo htmlspecialchars($lec['module_titre']); ?></span>
                            <span class="fw-semibold"><?php echo htmlspecialchars($lec['lecon_titre']); ?></span>
                        </button>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <?php if (!empty($lecons)): $first = $lecons[0]; ?>
            <div class="card border-0 shadow-sm p-4 mb-4">
                <h3 class="fw-bold text-dark mb-3" id="course-title"><?php echo htmlspecialchars($first['lecon_titre']); ?></h3>
                <div class="ratio ratio-16x9 border rounded-3 mb-4 bg-dark">
                    <iframe id="pdf-viewer" src="<?php echo $first['contenu_path']; ?>" class="rounded-3" allowfullscreen></iframe>
                </div>
                
                <div class="bg-light rounded-3 p-4 border border-subtle" id="quiz-container">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-patch-question-fill text-primary me-2"></i>Évaluation de progression</h5>
                    <p class="fw-semibold text-secondary mb-3" id="quiz-question"><?php echo htmlspecialchars($first['question']); ?></p>
                    
                    <form id="form-quiz">
                        <input type="hidden" name="id_lecon" id="quiz-lecon-id" value="<?php echo $first['lecon_id']; ?>">
                        <div class="form-check p-3 border rounded-3 mb-2 bg-white card-hover">
                            <input class="form-check-input ms-1" type="radio" name="reponse" id="optA" value="A" required>
                            <label class="form-check-label ms-3 fw-medium" for="optA" id="lblA"><?php echo htmlspecialchars($first['option_a']); ?></label>
                        </div>
                        <div class="form-check p-3 border rounded-3 mb-2 bg-white card-hover">
                            <input class="form-check-input ms-1" type="radio" name="reponse" id="optB" value="B">
                            <label class="form-check-label ms-3 fw-medium" for="optB" id="lblB"><?php echo htmlspecialchars($first['option_b']); ?></label>
                        </div>
                        <div class="form-check p-3 border rounded-3 mb-3 bg-white card-hover">
                            <input class="form-check-input ms-1" type="radio" name="reponse" id="optC" value="C">
                            <label class="form-check-label ms-3 fw-medium" for="optC" id="lblC"><?php echo htmlspecialchars($first['option_c']); ?></label>
                        </div>
                        <button type="submit" class="btn btn-primary px-4 fw-semibold"><i class="bi bi-send-check-fill me-2"></i>Valider ma réponse</button>
                    </form>
                    <div id="quiz-result" class="mt-3 fw-bold"></div>
                </div>
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm p-5 text-center text-muted">
                <i class="bi bi-journal-x style='font-size:4rem;'"></i>
                <p class="mt-3">Sélectionnez un cours pour commencer votre progression.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
// Interactivité JavaScript pour basculer d'une leçon à une autre sans rechargement
function chargerCours(titre, path, id, question, a, b, c) {
    document.getElementById('course-title').innerText = titre;
    document.getElementById('pdf-viewer').src = path;
    document.getElementById('quiz-lecon-id').value = id;
    document.getElementById('quiz-question').innerText = question;
    document.getElementById('lblA').innerText = a;
    document.getElementById('lblB').innerText = b;
    document.getElementById('lblC').innerText = c;
    document.getElementById('quiz-result').innerHTML = '';
    document.getElementById('form-quiz').reset();
    
    // Gérer la classe active sur les boutons
    const buttons = document.querySelectorAll('#course-list button');
    buttons.forEach(btn => btn.classList.remove('active'));
    event.currentTarget.classList.add('active');
}

// Soumission asynchrone AJAX pour la validation du Quiz
document.getElementById('form-quiz')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch('ajax_quiz.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        const resultDiv = document.getElementById('quiz-result');
        if(data.statut === 'succes') {
            resultDiv.innerHTML = `<div class="alert alert-success border-0 py-2"><i class="bi bi-check-circle-fill me-2"></i>\${data.message} - Progression : 100%</div>`;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger border-0 py-2"><i class="bi bi-x-circle-fill me-2"></i>\${data.message} - Progression : 0%</div>`;
        }
    });
});
</script>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>