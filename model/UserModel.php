<?php

namespace model;

class UserModel {
    public function __construct(string $username, string $password, string $passwordMatch) {
        $this->username = $username;
        $this->password = $password;
        $this->passwordMatch = $passwordMatch;
        $this->validateUsername();
        $this->validatePassword();
    }

    private function validateUsername() {
        preg_match('/^[a-zA-Z0-9]+$/', $this->username, $matches);

        if (strlen($this->username) < 3) {
            throw new \UsernameTooShortException('User entered a too short username');
        }

        if (!(count($matches) > 0)) {
            throw new \InvalidSymbolsUsernameException('User entered username with invalid characters');
        }

    }

    private function validatePassword() {
        
        if (strlen($this->password) < 6) {
            throw new \PasswordTooShortException('User entered a password that was too short');
        }

        if ($this->password !== $this->passwordMatch) {
            throw new \PasswordsDoNotMatchException('User entered two different passwords.');
        }

    }

    public function stripUsername(string $username) : string {
        $cleanedUsername = strip_tags($username);
        return $cleanedUsername;
    }

}
