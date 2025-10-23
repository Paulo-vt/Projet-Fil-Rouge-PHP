
<?php

session_start();
require_once 'classes/Database.php';
require_once 'classes/User.php';

$erreur = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pseudo = trim($_POST['pseudo'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    
    if (empty($pseudo)) {
        $erreur = 'Le pseudo est obligatoire';
    } elseif (strlen($pseudo) < 3) {
        $erreur = 'Le pseudo doit contenir au moins 3 caractères';
    } elseif (strlen($pseudo) > 50) {
        $erreur = 'Le pseudo ne peut pas dépasser 50 caractères';
    }
    
    elseif (empty($email)) {
        $erreur = 'L\'email est obligatoire';
    } 
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erreur = 'L\'email n\'est pas valide';
    }
    
    elseif (empty($password)) {
        $erreur = 'Le mot de passe est obligatoire';
    } elseif (strlen($password) < 6) {
        $erreur = 'Le mot de passe doit contenir au moins 6 caractères';
    }
    
    elseif ($password !== $password_confirm) {
        $erreur = 'Les mots de passe ne correspondent pas';
    }
    
    else {
        $user = User::register($pseudo, $email, $password);
        
        if ($user) {
            $_SESSION['user_id'] = $user->getId();
            $_SESSION['user_pseudo'] = $user->getPseudo();
            
            $_SESSION['message_succes'] = 'Bienvenue ' . htmlspecialchars($pseudo) . ' ! Votre compte a été créé avec succès.';
            
            header('Location: index.php');
            exit;
            
        } else {
            $erreur = 'Ce pseudo ou cet email est déjà utilisé. Veuillez en choisir un autre.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - QuizMusic 🎵</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <style>
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-slide-in {
        animation: slideIn 0.5s ease-out;
    }
    </style>
</head>

<body
    class="bg-gradient-to-br from-purple-900 via-blue-900 to-indigo-900 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md animate-slide-in">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">🎵 QuizMusic</h1>
            <p class="text-gray-600">Créez votre compte pour jouer</p>
        </div>

        <?php if ($erreur): ?>
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-xl mb-4 animate-pulse">
            <div class="flex items-center">
                <span class="text-xl mr-2">⚠️</span>
                <span><?php echo htmlspecialchars($erreur); ?></span>
            </div>
        </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4" novalidate>

            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Pseudo <span class="text-red-500">*</span>
                </label>
                <input type="text" name="pseudo" value="<?php echo htmlspecialchars($_POST['pseudo'] ?? ''); ?>"
                    required minlength="3" maxlength="50" placeholder="Votre pseudo (min. 3 caractères)"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                <p class="text-sm text-gray-500 mt-1">Entre 3 et 50 caractères</p>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Email <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required
                    placeholder="votre.email@exemple.fr"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password" required minlength="6" placeholder="Minimum 6 caractères"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                <p class="text-sm text-gray-500 mt-1">Minimum 6 caractères</p>
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-2">
                    Confirmer le mot de passe <span class="text-red-500">*</span>
                </label>
                <input type="password" name="password_confirm" required minlength="6"
                    placeholder="Retapez votre mot de passe"
                    class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <p class="text-sm text-blue-800">
                    🔒 Votre mot de passe sera chiffré de manière sécurisée.
                    Nous ne stockons jamais les mots de passe en clair.
                </p>
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white font-bold py-3 rounded-xl transition-all duration-200 transform hover:scale-105 shadow-lg hover:shadow-xl">
                🚀 Créer mon compte
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Vous avez déjà un compte ?
                <a href="login.php" class="text-purple-600 hover:text-purple-700 font-medium transition-colors">
                    Connectez-vous
                </a>
            </p>
        </div>

    </div>

    <script>

    document.querySelector('form').addEventListener('submit', function(e) {
        const password = document.querySelector('input[name="password"]').value;
        const passwordConfirm = document.querySelector('input[name="password_confirm"]').value;

        if (password !== passwordConfirm) {
            e.preventDefault();
            alert('⚠️ Les mots de passe ne correspondent pas !');
            document.querySelector('input[name="password_confirm"]').focus();
            return false;
        }

        if (password.length < 6) {
            e.preventDefault();
            alert('⚠️ Le mot de passe doit contenir au moins 6 caractères !');
            document.querySelector('input[name="password"]').focus();
            return false;
        }
    });

    document.querySelectorAll('input').forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('transform', 'scale-105');
        });

        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('transform', 'scale-105');
        });
    });
    </script>
</body>

</html>