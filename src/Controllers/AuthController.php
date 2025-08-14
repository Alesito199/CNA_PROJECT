<?php

namespace CNA\Controllers;

use CNA\Models\User;
use CNA\Utils\Security;
use CNA\Utils\Session;

class AuthController extends BaseController
{
    private User $userModel;

    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }

    public function showLogin(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view->setLayout('auth')->render('auth/login', [
            'title' => $this->lang->translate('auth.login')
        ]);
    }

    public function login(): void
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/login');
        }

        $email = trim($this->getInput('email', ''));
        $password = $this->getInput('password', '');
        $remember = $this->getInput('remember') === '1';

        // Validation
        $errors = $this->validate([
            'email' => 'required|email',
            'password' => 'required'
        ], compact('email', 'password'));

        if (!empty($errors)) {
            $this->setOldInput(compact('email'));
            Session::flash('error', $this->lang->translate('auth.login_failed'));
            $this->redirect('/login');
        }

        // Rate limiting
        $identifier = $_SERVER['REMOTE_ADDR'] . ':' . $email;
        if (!Security::rateLimit($identifier, 5, 900)) { // 5 attempts per 15 minutes
            Session::flash('error', 'Too many login attempts. Please try again later.');
            Security::logSecurityEvent('login_rate_limited', ['email' => $email], false);
            $this->redirect('/login');
        }

        // Find user
        $user = $this->userModel->findByEmail($email);
        if (!$user || !$user['is_active']) {
            Security::logSecurityEvent('login_failed', ['email' => $email], false);
            Session::flash('error', $this->lang->translate('auth.login_failed'));
            $this->setOldInput(compact('email'));
            $this->redirect('/login');
        }

        // Verify password
        $storedHash = $this->getPasswordHash($user['id']);
        if (!Security::verifyPassword($password, $storedHash)) {
            Security::logSecurityEvent('login_failed', ['email' => $email, 'user_id' => $user['id']], false);
            Session::flash('error', $this->lang->translate('auth.login_failed'));
            $this->setOldInput(compact('email'));
            $this->redirect('/login');
        }

        // Successful login
        Session::setUser($user);
        $this->userModel->updateLastLogin($user['id']);
        
        Security::logSecurityEvent('login_success', ['user_id' => $user['id']], true);
        
        $this->clearOldInput();
        
        // Handle remember me (simplified - in production use proper remember tokens)
        if ($remember) {
            setcookie('remember_token', base64_encode($user['id']), time() + (30 * 24 * 60 * 60), '/', '', true, true);
        }

        $this->redirect('/dashboard');
    }

    public function logout(): void
    {
        $user = Session::user();
        if ($user) {
            Security::logSecurityEvent('logout', ['user_id' => $user['id']], true);
        }

        Session::logout();
        
        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/', '', true, true);
        }

        Session::flash('success', $this->lang->translate('auth.logout_success'));
        $this->redirect('/login');
    }

    public function showRegister(): void
    {
        if (Session::isAuthenticated()) {
            $this->redirect('/dashboard');
        }

        $this->view->setLayout('auth')->render('auth/register', [
            'title' => $this->lang->translate('auth.register')
        ]);
    }

    public function register(): void
    {
        if (!$this->validateCsrf()) {
            Session::flash('error', 'Invalid request');
            $this->redirect('/register');
        }

        $data = [
            'username' => trim($this->getInput('username', '')),
            'email' => trim($this->getInput('email', '')),
            'password' => $this->getInput('password', ''),
            'password_confirm' => $this->getInput('password_confirm', ''),
            'first_name' => trim($this->getInput('first_name', '')),
            'last_name' => trim($this->getInput('last_name', '')),
        ];

        // Validation
        $errors = $this->validate([
            'username' => 'required|min:3|max:50',
            'email' => 'required|email',
            'password' => 'required|min:8',
            'first_name' => 'required|max:100',
            'last_name' => 'required|max:100',
        ], $data);

        // Custom validations
        if ($data['password'] !== $data['password_confirm']) {
            $errors['password_confirm'] = 'Passwords do not match';
        }

        if ($this->userModel->isEmailTaken($data['email'])) {
            $errors['email'] = 'Email is already taken';
        }

        if ($this->userModel->isUsernameTaken($data['username'])) {
            $errors['username'] = 'Username is already taken';
        }

        if (!empty($errors)) {
            $this->setOldInput($data);
            Session::flash('errors', $errors);
            $this->redirect('/register');
        }

        // Create user
        try {
            $userId = $this->userModel->create([
                'username' => $data['username'],
                'email' => $data['email'],
                'password_hash' => Security::hashPassword($data['password']),
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'role' => 'user',
                'is_active' => true,
                'email_verified_at' => date('Y-m-d H:i:s'),
            ]);

            Security::logSecurityEvent('user_registered', ['user_id' => $userId], true);
            
            Session::flash('success', 'Account created successfully. You can now login.');
            $this->clearOldInput();
            $this->redirect('/login');
            
        } catch (\Exception $e) {
            error_log("Registration failed: " . $e->getMessage());
            Session::flash('error', 'Registration failed. Please try again.');
            $this->setOldInput($data);
            $this->redirect('/register');
        }
    }

    private function getPasswordHash(string $userId): string
    {
        // Get password hash directly (not returned in regular user query due to hidden fields)
        $result = $this->userModel->db->fetch(
            "SELECT password_hash FROM users WHERE id = :id",
            ['id' => $userId]
        );
        
        return $result['password_hash'] ?? '';
    }
}