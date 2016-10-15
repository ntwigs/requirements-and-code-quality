<?php

namespace controller;

require_once('view/LoginView.php');
require_once('view/GetFlashMessages.php');
require_once('model/FlashModel.php');
require_once('model/DAL.php');
require_once('model/SessionModel.php');
require_once('view/GetFlashMessages.php');

class LoginController {
    private static $username = '';
    private static $password = '';

    public function __construct(\model\FlashModel $flashModel, \model\SessionModel $sessionModel, \model\UsernameModel $usernameModel) {
        $this->usernameModel = $usernameModel;
        $this->lv = new \view\LoginView($this->usernameModel);
        $this->db = new \model\DAL();
        $this->sm = $sessionModel;
        $this->fm = $flashModel;

        try {

            if ($this->hasCookiesTryingToLogin()) {
                $this->triesToLoginUserWithCookies();
            } else if ($this->isTryingToLogIn()) {
                $this->triesToAuthenticateUser();
            } else if ($this->isTryingToLogOut()) {
                $this->triesToLogoutUser();
            }

        } catch (\NoUsernameException $e) {
                $this->fm->setFlashMessage($this->lv->getWrongUsernameMessage());
        } catch (\NoPasswordException $e) {
                $this->fm->setFlashMessage($this->lv->getWrongPasswordMessage());
        } catch (\Exception $e) {
                $this->fm->setFlashMessage($e->getMessage());
        } finally {
            $this->usernameModel->setUsernameUsedInCredentials(self::$username);
            $this->lv->loginToLayoutView($this->fm, $this->sm, $this->usernameModel);
            if ($this->lv->isLoggingIn() || $this->lv->isLoggingOut()) {
                header('Location: /');
                exit();
            }
        }
    }

    private function hasCookiesTryingToLogin() : bool {
        if ($this->lv->hasUsernameCookie() && !$this->lv->isLoggingOut()) {
            return true;
        }
        return false;
    }

    private function triesToLoginUserWithCookies() {
        self::$username = $this->lv->getUsernameCookie();
        self::$password = $this->lv->getPasswordCookie();
        $this->compareEnteredCredentials();
    }

    private function isTryingToLogIn() : bool {
        if ($this->lv->isLoggingIn() && !$this->sm->getIsLoggedIn()) {
            return true;
        }
        return false;
    }

    private function isTryingToLogOut() : bool {
        if ($this->lv->isLoggingOut() && $this->sm->getIsLoggedIn()) {
            return true;
        }
        return false;
    }

    private function triesToLogoutUser() {
        $this->removeUserCredentialsInCookie();
        $this->usernameModel->removeStoredUsername();
        $this->sm->setIsLoggedIn(false);
        $this->fm->setFlashMessage($this->lv->getLogoutMessage());
        $this->fm->setFlashMessage($this->getFlashMessages->setLogoutMessage());
    }

    private function triesToAuthenticateUser() {
        $this->setUsername();
        $this->setPassword();
        $this->compareEnteredCredentials();
        $this->storeUserCredentialsInCookie();
    }

    private function compareEnteredCredentials() {

        if ($this->db->compareCredentials(self::$username, self::$password)) {
            if ($this->lv->isKeepingLogin()) {
                $this->fm->setFlashMessage($this->lv->getWelcomeRemember());
            } else if (!$this->sm->getIsLoggedIn() && $this->lv->hasUsernameCookie()) {
                $this->fm->setFlashMessage($this->lv->getWelcomeCookie());
            } else if (!$this->sm->getIsLoggedIn()) {
                $this->fm->setFlashMessage($this->lv->getWelcomeStandard());
            }

            $this->sm->setIsLoggedIn(true);

        } else if (strlen(self::$password) > 0 || strlen(self::$username) > 0) {
            $this->fm->setFlashMessage($this->lv->getWrongCredentials());
        }

    }

    private function setUsername() {
        if ($this->lv->hasUsernameCookie()) {
            self::$username = $this->lv->getUsernameCookie();
        } else {
            self::$username = $this->lv->getUsername();
        }
    }

    private function setPassword() {
        if ($this->lv->hasPasswordCookie()) {
            self::$password = $this->lv->getPasswordCookie();
        } else {
            self::$password = $this->lv->getPassword();
        }
    }

    public function storeUserCredentialsInCookie() {
        if ($this->lv->isKeepingLogin()) {
            $this->lv->setUsernameCookie();
            $this->lv->setPasswordCookie();
        }
    }

    public function removeUserCredentialsInCookie() {
        $this->lv->removeUsernameCookie();
        $this->lv->removePasswordCookie();
    }

}
