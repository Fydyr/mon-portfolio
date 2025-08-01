<?php
require_once 'BaseController.php';
require_once __DIR__ . '/../includes/db.php'; // Connexion à la BDD
global $pdo;

class AccountController extends BaseController
{
    public function login()
    {
        // Si déjà connecté, redirige vers l'accueil
        if (isset($_SESSION['user_id'])) {
            header('Location: /');
            exit;
        }
        // Si la requête est POST, on traite la connexion
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $errors = [];

            // Récupération et validation des champs
            $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
            $mdp = $_POST['mdp'] ?? '';

            if (!$email) {
                $errors[] = 'Adresse email invalide.';
            }

            if (empty($mdp)) {
                $errors[] = 'Mot de passe requis.';
            }

            if (empty($errors)) {
                global $pdo;
                $stmt = $pdo->prepare('SELECT id, mail, password FROM user WHERE mail = ?');
                $stmt->execute([$email]);
                $user = $stmt->fetch();

                if ($user && password_verify($mdp, $user['password'])) {
                    session_start();
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_mail'] = $user['mail'];
                    $_SESSION['admin'] = $user['admin'];
                    header('Location:' . url('admin'));
                    exit;
                } else {
                    $errors[] = 'Email ou mot de passe incorrect.';
                }
            }

            // Affiche la vue avec les erreurs
            echo $this->view('login', ['errors' => $errors]);
        } else {
            // Affiche simplement le formulaire
            echo $this->view('login');
        }
    }

    public function logout()
    {
        session_start();
        session_unset();
        session_destroy();
        header('Location: /');
        exit;
    }
}
