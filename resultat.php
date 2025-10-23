<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_SESSION['dernier_score'])) {
    header('Location: index.php');
    exit;
}

$score = $_SESSION['dernier_score'];
$total = $_SESSION['total_questions'];
$theme = $_SESSION['dernier_theme'];
$pseudo = $_SESSION['user_pseudo'];

$pourcentage = ($score / $total) * 100;

$niveaux = [
    ['min' => 0, 'max' => 2, 'titre' => '🔇 Débutant', 'badge' => 'Mélomane en herbe', 'couleur' => 'from-gray-400 to-gray-600', 'message' => 'C\'est un début ! La musique n\'a pas encore de secrets pour vous, mais c\'est le moment d\'ouvrir grand vos oreilles !'],
    ['min' => 3, 'max' => 4, 'titre' => '🎵 Amateur', 'badge' => 'Auditeur curieux', 'couleur' => 'from-blue-400 to-blue-600', 'message' => 'Pas mal ! Vous commencez à reconnaître quelques classiques. Continuez à explorer !'],
    ['min' => 5, 'max' => 6, 'titre' => '🎶 Confirmé', 'badge' => 'Mélomane averti', 'couleur' => 'from-purple-400 to-purple-600', 'message' => 'Bravo ! Vous avez de bonnes bases musicales. Votre culture s\'étend bien !'],
    ['min' => 7, 'max' => 8, 'titre' => '🎸 Expert', 'badge' => 'Connaisseur', 'couleur' => 'from-orange-400 to-orange-600', 'message' => 'Impressionnant ! Vous maîtrisez vraiment votre sujet. Peu de choses vous échappent !'],
    ['min' => 9, 'max' => 10, 'titre' => '🏆 Maître', 'badge' => 'Virtuose musical', 'couleur' => 'from-yellow-400 to-yellow-600', 'message' => 'Exceptionnel ! Vous êtes un véritable expert. Bravo pour cette performance parfaite !']
];

$niveauActuel = null;
foreach ($niveaux as $niveau) {
    if ($score >= $niveau['min'] && $score <= $niveau['max']) {
        $niveauActuel = $niveau;
        break;
    }
}

require_once 'classes/Database.php';
require_once 'classes/Badge.php';

$pdo = Database::getConnexion();
$stmt = $pdo->prepare("SELECT * FROM questionnaires WHERE code = ?");
$stmt->execute([$theme]);
$themeInfo = $stmt->fetch();

$badgesDebloques = [];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM scores WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if ($stmt->fetch()['count'] >= 1) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ? AND b.nom = 'Premier Pas'");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) SELECT ?, id FROM badges WHERE nom = 'Premier Pas'");
        $stmt->execute([$_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("SELECT * FROM badges WHERE nom = 'Premier Pas'");
        $stmt->execute();
        $b = $stmt->fetch();
        $badgesDebloques[] = new Badge($b['id'], $b['nom'], $b['description'], date('Y-m-d H:i:s'));
    }
}

if ($score === $total && $total === 5) {
    $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_badges ub JOIN badges b ON ub.badge_id = b.id WHERE ub.user_id = ? AND b.nom = 'Perfectionniste'");
    $stmt->execute([$_SESSION['user_id']]);
    if ($stmt->fetch()['count'] == 0) {
        $stmt = $pdo->prepare("INSERT INTO user_badges (user_id, badge_id) SELECT ?, id FROM badges WHERE nom = 'Perfectionniste'");
        $stmt->execute([$_SESSION['user_id']]);
        
        $stmt = $pdo->prepare("SELECT * FROM badges WHERE nom = 'Perfectionniste'");
        $stmt->execute();
        $b = $stmt->fetch();
        $badgesDebloques[] = new Badge($b['id'], $b['nom'], $b['description'], date('Y-m-d H:i:s'));
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Résultats - QuizMusic 🎵</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.8);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        @keyframes bounceIn {
            0% {
                transform: scale(0);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        .animate-fade-scale {
            animation: fadeInScale 0.8s ease-out;
        }

        .animate-bounce-in {
            animation: bounceIn 1s ease-out;
        }

        @keyframes progressBar {
            from {
                width: 0%;
            }
            to {
                width: var(--progress-width);
            }
        }

        .progress-animated {
            animation: progressBar 2s ease-out forwards;
        }
    </style>
</head>

<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-scale">

            <div class="bg-gradient-to-r <?php echo $themeInfo['couleur']; ?> p-8 text-white text-center">
                <div class="text-6xl mb-4"><?php echo $themeInfo['emoji']; ?></div>
                <h1 class="text-3xl font-bold mb-2">Quiz <?php echo htmlspecialchars($themeInfo['titre']); ?></h1>
                <p class="text-white/80">Résultats de <?php echo htmlspecialchars($pseudo); ?></p>
            </div>

            <div class="p-8 text-center">

                <div class="inline-block animate-bounce-in mb-6">
                    <div class="bg-gradient-to-r <?php echo $niveauActuel['couleur']; ?> text-white px-8 py-4 rounded-2xl shadow-xl">
                        <div class="text-5xl mb-2"><?php echo $niveauActuel['titre']; ?></div>
                        <div class="text-xl font-semibold"><?php echo $niveauActuel['badge']; ?></div>
                    </div>
                </div>

                <div class="text-7xl font-bold text-gray-800 mb-4">
                    <?php echo $score; ?> / <?php echo $total; ?>
                </div>

                <div class="text-2xl text-gray-600 mb-6">
                    <?php echo round($pourcentage); ?>% de réussite
                </div>

                <div class="max-w-md mx-auto mb-8">
                    <div class="bg-gray-200 rounded-full h-6 overflow-hidden">
                        <div class="progress-animated h-full bg-gradient-to-r <?php echo $niveauActuel['couleur']; ?> rounded-full flex items-center justify-end pr-3"
                             style="--progress-width: <?php echo $pourcentage; ?>%;">
                            <span class="text-white text-sm font-bold"><?php echo round($pourcentage); ?>%</span>
                        </div>
                    </div>
                </div>

                <div class="bg-purple-50 border-2 border-purple-200 rounded-2xl p-6 mb-8">
                    <p class="text-lg text-gray-700 leading-relaxed">
                        <?php echo $niveauActuel['message']; ?>
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="quiz.php?theme=<?php echo urlencode($theme); ?>"
                       class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                        🔄 Rejouer
                    </a>

                    <a href="historique.php"
                       class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                        📊 Mon historique
                    </a>

                    <a href="index.php"
                       class="bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                        🏠 Accueil
                    </a>
                </div>
            </div>
        </div>

        <?php if (!empty($badgesDebloques)): ?>
        <div class="mt-8 bg-white rounded-3xl shadow-2xl overflow-hidden animate-fade-scale">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-6 text-white text-center">
                <h2 class="text-3xl font-bold">
                    🎉 Nouveau<?php echo count($badgesDebloques) > 1 ? 'x' : ''; ?> Badge<?php echo count($badgesDebloques) > 1 ? 's' : ''; ?> Débloqué<?php echo count($badgesDebloques) > 1 ? 's' : ''; ?> !
                </h2>
                <p class="text-white/90 mt-2">Félicitations ! Vous avez débloqué de nouveaux badges !</p>
            </div>
            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-<?php echo min(count($badgesDebloques), 3); ?> gap-6">
                    <?php foreach ($badgesDebloques as $badge): ?>
                        <div class="animate-bounce-in">
                            <?php echo $badge->afficherHTML(true); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-6">
                    <a href="profile.php" class="inline-block bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                        🏆 Voir tous mes badges
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <div class="mt-8 bg-white/10 backdrop-blur-sm rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-white text-center mb-6">
                🎯 Système de niveaux
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <?php foreach ($niveaux as $niveau): ?>
                    <div class="bg-white/20 backdrop-blur-sm rounded-xl p-4 text-center <?php echo ($niveau === $niveauActuel) ? 'ring-4 ring-yellow-400' : ''; ?>">
                        <div class="text-2xl mb-2"><?php echo explode(' ', $niveau['titre'])[0]; ?></div>
                        <div class="text-white text-sm font-medium"><?php echo explode(' ', $niveau['titre'], 2)[1] ?? ''; ?></div>
                        <div class="text-purple-200 text-xs mt-2">
                            <?php echo $niveau['min']; ?>-<?php echo $niveau['max']; ?> pts
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <script>
        <?php if ($score >= 7): ?>
        function createConfetti() {
            const confetti = document.createElement('div');
            confetti.style.position = 'fixed';
            confetti.style.width = '10px';
            confetti.style.height = '10px';
            confetti.style.backgroundColor = ['#ff0000', '#00ff00', '#0000ff', '#ffff00', '#ff00ff'][Math.floor(Math.random() * 5)];
            confetti.style.left = Math.random() * 100 + '%';
            confetti.style.top = '-10px';
            confetti.style.borderRadius = '50%';
            confetti.style.zIndex = '9999';
            document.body.appendChild(confetti);

            let pos = -10;
            const fall = setInterval(() => {
                if (pos >= window.innerHeight) {
                    clearInterval(fall);
                    confetti.remove();
                } else {
                    pos += 5;
                    confetti.style.top = pos + 'px';
                }
            }, 50);
        }

        for (let i = 0; i < 50; i++) {
            setTimeout(createConfetti, i * 100);
        }
        <?php endif; ?>
    </script>
</body>

</html>
