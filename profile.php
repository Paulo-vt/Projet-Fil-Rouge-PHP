<?php

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'classes/Database.php';
require_once 'classes/User.php';
require_once 'classes/Badge.php';

$user = new User(
    $_SESSION['user_id'],
    $_SESSION['user_pseudo'],
    $_SESSION['user_email'] ?? ''
);

// Récupération des badges
$pdo = Database::getConnexion();
$sql = "
    SELECT 
        b.id, b.nom, b.description,
        ub.date_obtention
    FROM badges b
    LEFT JOIN user_badges ub ON b.id = ub.badge_id AND ub.user_id = ?
    ORDER BY b.nom ASC
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$_SESSION['user_id']]);
$lignes = $stmt->fetchAll();

$badges = [];
foreach ($lignes as $ligne) {
    $badges[] = new Badge(
        $ligne['id'],
        $ligne['nom'],
        $ligne['description'],
        $ligne['date_obtention']
    );
}

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_badges WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$nbBadgesDebloques = $stmt->fetch()['count'];
$nbBadgesTotal = count($badges);

$messageSucces = '';
$messageErreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nouveau_pseudo'])) {
    $nouveauPseudo = trim($_POST['nouveau_pseudo']);

    if (empty($nouveauPseudo)) {
        $messageErreur = "Le pseudo ne peut pas être vide.";
    } elseif (strlen($nouveauPseudo) < 3) {
        $messageErreur = "Le pseudo doit contenir au moins 3 caractères.";
    } elseif (strlen($nouveauPseudo) > 50) {
        $messageErreur = "Le pseudo ne peut pas dépasser 50 caractères.";
    } else {
        if ($user->updatePseudo($nouveauPseudo)) {
            $_SESSION['user_pseudo'] = $nouveauPseudo;
            $messageSucces = "Votre pseudo a été modifié avec succès !";
        } else {
            $messageErreur = "Ce pseudo est déjà utilisé par un autre utilisateur.";
        }
    }
}

$dateInscription = $user->getDateInscription();
$nombreParties = $user->getNombreParties();
$themePreference = $user->getThemePreference();

$dateFormatee = '';
if ($dateInscription) {
    $date = new DateTime($dateInscription);
    $dateFormatee = $date->format('d/m/Y à H:i');
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon profil - QuizMusic 🎵</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen">
    <div class="container mx-auto px-4 py-8 max-w-4xl">

        <nav class="mb-8">
            <a href="index.php" class="text-purple-300 hover:text-white transition-colors">
                ← Retour à l'accueil
            </a>
        </nav>

        <header class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-4">
                👤 Mon profil
            </h1>
            <p class="text-xl text-purple-200">
                Gérez vos informations personnelles
            </p>
        </header>

        <?php if ($messageSucces): ?>
            <div class="bg-green-100 border border-green-400 text-green-700 px-6 py-4 rounded-xl mb-8">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">✅</span>
                    <span class="font-medium"><?php echo htmlspecialchars($messageSucces); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($messageErreur): ?>
            <div class="bg-red-100 border border-red-400 text-red-700 px-6 py-4 rounded-xl mb-8">
                <div class="flex items-center">
                    <span class="text-2xl mr-3">❌</span>
                    <span class="font-medium"><?php echo htmlspecialchars($messageErreur); ?></span>
                </div>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-purple-500 to-purple-600 p-6">
                <h2 class="text-2xl font-bold text-white">
                    📋 Informations personnelles
                </h2>
            </div>

            <div class="p-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-3">👤</span>
                            <h3 class="text-lg font-semibold text-gray-700">Pseudo</h3>
                        </div>
                        <p class="text-2xl font-bold text-purple-600">
                            <?php echo htmlspecialchars($user->getPseudo()); ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-3">📧</span>
                            <h3 class="text-lg font-semibold text-gray-700">Email</h3>
                        </div>
                        <p class="text-xl font-medium text-gray-800 break-all">
                            <?php echo htmlspecialchars($user->getEmail()); ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-3">📅</span>
                            <h3 class="text-lg font-semibold text-gray-700">Membre depuis</h3>
                        </div>
                        <p class="text-xl font-medium text-gray-800">
                            <?php echo htmlspecialchars($dateFormatee); ?>
                        </p>
                    </div>

                    <div class="bg-gray-50 rounded-xl p-6">
                        <div class="flex items-center mb-2">
                            <span class="text-2xl mr-3">🎮</span>
                            <h3 class="text-lg font-semibold text-gray-700">Parties jouées</h3>
                        </div>
                        <p class="text-2xl font-bold text-blue-600">
                            <?php echo $nombreParties; ?>
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-r from-yellow-50 to-orange-50 rounded-xl p-6 mt-6 border-2 border-yellow-200">
                    <div class="flex items-center mb-2">
                        <span class="text-2xl mr-3">⭐</span>
                        <h3 class="text-lg font-semibold text-gray-700">Thème préféré</h3>
                    </div>
                    <?php if ($themePreference): ?>
                        <div class="flex items-center gap-3">
                            <span class="text-4xl"><?php echo $themePreference['emoji']; ?></span>
                            <div>
                                <p class="text-2xl font-bold text-orange-600">
                                    <?php echo htmlspecialchars($themePreference['titre']); ?>
                                </p>
                                <p class="text-sm text-gray-600">
                                    Joué <?php echo $themePreference['nb_parties']; ?> fois
                                </p>
                            </div>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-600 italic">
                            Aucune partie jouée pour le moment. Commencez à jouer pour découvrir votre thème préféré !
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-yellow-400 to-orange-500 p-6">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-white">🏆 Mes badges</h2>
                    <div class="bg-white/20 backdrop-blur-sm px-4 py-2 rounded-full">
                        <span class="text-white font-bold text-lg"><?php echo $nbBadgesDebloques; ?> / <?php echo $nbBadgesTotal; ?></span>
                    </div>
                </div>
                <p class="text-white/90 mt-2">Débloquez des badges en jouant et en réussissant des défis !</p>
            </div>

            <div class="p-8">
                <?php if (empty($badges)): ?>
                    <div class="text-center py-12">
                        <div class="text-6xl mb-4">🎯</div>
                        <p class="text-xl text-gray-600 mb-4">Aucun badge disponible</p>
                        <p class="text-gray-500">Jouez à des quiz pour débloquer des badges !</p>
                    </div>
                <?php else: ?>
                    <?php 
                    $badgesDebloques = array_filter($badges, fn($b) => $b->isUnlocked());
                    $badgesVerrouilles = array_filter($badges, fn($b) => !$b->isUnlocked());
                    ?>
                    
                    <?php if (!empty($badgesDebloques)): ?>
                        <div class="mb-8">
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span>✨</span>
                                <span>Badges débloqués (<?php echo count($badgesDebloques); ?>)</span>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php foreach ($badgesDebloques as $badge): ?>
                                    <?php echo $badge->afficherHTML(true); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($badgesVerrouilles)): ?>
                        <div>
                            <h3 class="text-xl font-bold text-gray-800 mb-4 flex items-center gap-2">
                                <span>🔒</span>
                                <span>Badges à débloquer (<?php echo count($badgesVerrouilles); ?>)</span>
                            </h3>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <?php foreach ($badgesVerrouilles as $badge): ?>
                                    <?php echo $badge->afficherHTML(true); ?>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>

        <div class="bg-white rounded-2xl shadow-xl overflow-hidden mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 p-6">
                <h2 class="text-2xl font-bold text-white">
                    ✏️ Modifier mon pseudo
                </h2>
            </div>

            <div class="p-8">
                <form method="POST" action="profile.php" class="max-w-md">
                    <div class="mb-6">
                        <label for="nouveau_pseudo" class="block text-gray-700 font-semibold mb-2">
                            Nouveau pseudo
                        </label>
                        <input
                            type="text"
                            id="nouveau_pseudo"
                            name="nouveau_pseudo"
                            value="<?php echo htmlspecialchars($user->getPseudo()); ?>"
                            required
                            minlength="3"
                            maxlength="50"
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-xl focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="Votre nouveau pseudo"
                        >
                        <p class="text-sm text-gray-500 mt-2">
                            Entre 3 et 50 caractères
                        </p>
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg">
                        💾 Enregistrer les modifications
                    </button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <a href="historique.php"
               class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white p-6 rounded-xl transition-all duration-200 text-center">
                <div class="text-4xl mb-2">📊</div>
                <h3 class="text-xl font-bold mb-2">Mon historique</h3>
                <p class="text-purple-200 text-sm">Consultez toutes vos performances</p>
            </a>

            <a href="index.php"
               class="bg-white/10 backdrop-blur-sm hover:bg-white/20 text-white p-6 rounded-xl transition-all duration-200 text-center">
                <div class="text-4xl mb-2">🎵</div>
                <h3 class="text-xl font-bold mb-2">Jouer un quiz</h3>
                <p class="text-purple-200 text-sm">Retournez à la liste des quiz</p>
            </a>
        </div>
    </div>
</body>

</html>
