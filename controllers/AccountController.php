<?php


/**
 * Контроллер /account
 */
class AccountController extends Controller {

    public function action(string $name): bool {
        switch ($name) {

            case 'Index': {
                if (!$this->isAuthorized()) {
                    header('Location: /account/login', true, 303);
                } else {
                    header('Location: /account/'.$this->getAuthorizedUser()->getID(), true, 303);
                }
                return true;
            }

            case 'ShowAccount': {
                $accountID = $this->getRouteParams()[0];
                $account = &User::newInstance($accountID);

                if ($account) {
                    if (isset($_POST['submitChangeBalance']) && $this->adminPresence()) {
                        $newLocation = $this->postHandleChangeBalance($account);
                        header($newLocation, true, 303);
                        return true;
                    }

                    $isAllowedToWatchProfile = $account->isPublicAccount();
                    $isAllowedToWatchEmail = $account->isPublicEmail();
                    $isAllowedToWatchBalance = $account->isPublicBalance();
                    $isAllowedToModify = false;
                    if ($this->isAuthorized()) {
                        if ($this->authorizedUserIsOwnerOf($account) || $this->adminPresence()) {
                            $isAllowedToWatchProfile = true;
                            $isAllowedToWatchEmail = true;
                            $isAllowedToWatchBalance = true;
                            $isAllowedToModify = true;
                        }
                    }

                    require(ROOT.'views/account/account.php');
                    return true;
                } else {
                    return false;
                }
            }

            case 'ManageAccount': {
                $accountID = $this->getRouteParams()[0];
                $account = &User::newInstance($accountID);

                if ($account) {
                    if ($this->authorizedUserIsOwnerOf($account) || $this->adminPresence()) {
                        if (isset($_POST['submitAccountData'])) {
                            $newLocation = $this->postHandleAccountManage($account);
                            header($newLocation, true, 303);
                            return true;
                        }
                        require(ROOT.'views/account/manage.php');
                    } else {
                        header('Location: /account/'.$accountID.'/manage', true, 303);
                    }
                } else {
                    header('Location: /account/'.$accountID, true, 303);
                }
                return true;
            }

            case 'SignUp': {
                if ($this->isAuthorized()) {
                    header('Location: /account/'.$this->getAuthorizedUser()->getID(), true, 303);
                    return true;
                }

                if (isset($_POST['submitSignUpData'])) {
                    $newLocation = $this->postHandleSignUp();
                    header($newLocation, true, 303);
                    return true;
                }

                require(ROOT . 'views/account/signUp.php');
                return true;
            }

            case 'LogIn': {
                if ($this->isAuthorized()) {
                    header('Location: /account/'.$this->getAuthorizedUser()->getID(), true, 303);
                    return true;
                }

                if (isset($_POST['submitLogInData'])) {
                    $newLocation = $this->postHandleAuth();
                    header($newLocation, true, 303);
                    return true;
                }

                require(ROOT . 'views/account/logIn.php');
                return true;
            }

            case 'LogOut': {
                if ($this->isAuthorized()) {
                    $this->endUserSession();
                }
                header('Location: /', true, 303);
                return true;
            }

            case 'Activation': {
                $token = $this->getQueryParams()['token'] ?? '';
                $success = User::activateUser($token);
                require(ROOT.'views/account/activation.php');
                return true;
            }
            default : return false;
        }
    }

    /**
     * Метод для регистрации новых пользователей
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleSignUp(): string {
        $nickname = $_POST['nickname'] ?? '';
        $email = $_POST['email'] ?? '';
        $password1 = $_POST['password1'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        if ($password1 != $password2) {
            $errors['password'] = 'Mismatch';
        } else {
            $errors = User::create($nickname, $email, $password1);
        }
        $newLocation = 'Location: ';
        if (empty($errors)) {
            $newLocation .= '/account/login?SignupSuccess=1';
        } else {
            $newLocation .= explode('?', $_SERVER['REQUEST_URI'])[0].$this->toQueryString($errors);
        }
        return $newLocation;
    }

    /**
     * Метод для авторизации пользователей
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleAuth(): string {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $user = &User::authenticate($email, $password);
        $newLocation = 'Location: /account/';
        if (!$user) {
            $newLocation .= 'login'.$this->toQueryString(['error' => 'InvalidEmailOrPassword']);
        } elseif (!$user->isActivated()) {
            $newLocation .= 'login'.$this->toQueryString(['error' => 'NotActivated']);
        } else {
            $this->startUserSession($user->getID());
            $newLocation .= $user->getID();
        }
        return $newLocation;
    }

    /**
     * Метод для обработки изменения баланса пользователей
     * @param User $account Идентификатор пользователя
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleChangeBalance(User $account): string {
        $change = $_POST['changeBalance'] ?? '';
        $newValue = $_POST['value'] ?? 0;

        $success = [];
        if ($change == '+' && !$account->increaseBalance($newValue)) {
            $errors['BalanceIncrease'] = 'Error';
        } elseif ($change == '-' && !$account->decreaseBalance($newValue)) {
            $errors['BalanceDecrease'] = 'Error';
        } elseif ($change != '+' && $change != '-')  {
            $errors = ['Parameter' => 'ShouldBePlusOrMinus'];
        }

        $newLocation = 'Location: ';
        if (!empty($errors)) {
            $newLocation .=  explode('?', $_SERVER['REQUEST_URI'])[0].$this->toQueryString($errors);
        } else {
            $newLocation .= '/account/'.$account->getID();
        }
        return $newLocation;
    }

    /**
     * Метод для обработки изменения настроек аккаунта
     * @param User $account Объект пользователя, настройки которого будут изменены
     * @return string Строка с заголовком Location: ... для дальнейшего редиректа
     */
    protected function postHandleAccountManage(User &$account): string {
        $errors = array();

        $nickname = $_POST['newNickname'] ?? '';
        if ($nickname != $account->getNickname()) {
            $errorsNickname = $account->setNickname($nickname);
            if (!empty($errorsNickname)) {
                $errors = array_merge($errors, $errorsNickname);
            }
        }

        $location = $_POST['newLocation'] ?? '';
        if ($location != $account->getLocation()) {
            $errorsLocation = $account->setLocation($location);
            if (!empty($errorsLocation)) {
                $errors = array_merge($errors, $errorsLocation);
            }
        }

        $bio = $_POST['newBio'] ?? '';
        if ($bio != $account->getBio()) {
            $errorsBio = $account->setBio($bio);
            if (!empty($errorsBio)) {
                $errors = array_merge($errors, $errorsBio);
            }
        }

        if (isset($_POST['newBirthday'])) {
            $newBirthday = $_POST['newBirthday'] ?? '';
            if ($newBirthday && !strtotime($newBirthday)) {
                $errors['birthdayPattern'] = 'InvalidData';
            } elseif (!$newBirthday && $account->getDateOfBirth()) {
                $errorsBirthday = $account->deleteBirthday();
                if (!empty($errorsBirthday)) {
                    $errors = array_merge($errors, $errorsBirthday);
                }
            } elseif ($newBirthday) {
                $newBirthdayFormat = (new DateTime())->setTimestamp(strtotime($newBirthday))->format('Y-m-d');
                if ($newBirthdayFormat != $account->getDateOfBirth()) {
                    $errorsBirthday = $account->setDateOfBirth($newBirthday);
                    if (!empty($errorsBirthday)) {
                        $errors = array_merge($errors, $errorsBirthday);
                    }
                }
            }
        }

        $accountPrivacy = $_POST['accountPrivacy'] ?? $account->isPublicAccount();
        $accountPrivacy = $accountPrivacy == true;
        if ($accountPrivacy != $account->isPublicAccount()) {
            if (!$account->setIsPublicAccount($accountPrivacy)) {
                $errors['AccountPrivacy'] = 'DBInternalError';
            }
        }

        $balancePrivacy = $_POST['balancePrivacy'] ?? $account->isPublicBalance();
        $balancePrivacy = $balancePrivacy == true;
        if ($balancePrivacy != $account->isPublicBalance()) {
            if (!$account->setIsPublicBalance($balancePrivacy)) {
                $errors['BalancePrivacy'] = 'DBInternalError';
            }
        }

        $emailPrivacy = $_POST['emailPrivacy'] ?? $account->isPublicEmail();
        $emailPrivacy = $emailPrivacy == true;
        if ($emailPrivacy != $account->isPublicEmail()) {
            if (!$account->setIsPublicEmail($emailPrivacy)) {
                $errors['EmailPrivacy'] = 'DBInternalError';
            }
        }

        $avatarDeletion = $_POST['avatarDeletion'] ?? false;
        if ($avatarDeletion && $account->getPureAvatar() != 'default.jpg' &&
                is_file(ROOT . '/data/account/avatars/' . $account->getPureAvatar())) {

            unlink(ROOT . '/data/account/avatars/' . $account->getPureAvatar());
            $account->updateAvatar();
        } elseif (is_uploaded_file($_FILES['newAvatar']['tmp_name'])) {
            $file = $_FILES['newAvatar'];
            $errorsAvatar = $account->updateAvatar($file);
            if (empty($errorsAvatar)) {
                move_uploaded_file($file['tmp_name'], ROOT.'data/account/avatars/'.$account->getID().'.jpg');
            } else {
                $errors = array_merge($errors, $errorsAvatar);
            }
        }

        $newLocation = 'Location: ';
        if (!empty($errors)) {
            $newLocation .=  explode('?', $_SERVER['REQUEST_URI'])[0].$this->toQueryString($errors);
        } else {
            $newLocation .= '/account/'.$account->getID();
        }
        return $newLocation;
    }

    /**
     * Метод для получения информации о том, является ли авторизованный пользователь владельцем аккаунта
     * @param User $account Объект просматриваемого пользователя
     * @return bool True, если авторизован владелец, иначе - false
     */
    private function authorizedUserIsOwnerOf(User $account): bool {
        return $this->isAuthorized() && $this->getAuthorizedUser()->getID() == $account->getID();
    }

    /**
     * Метод для авторизации пользователя в текущей сессии
     * @param int $userID Идентификатор пользователя
     */
    private function startUserSession(int $userID) {
        $_SESSION['userID'] = $userID;
    }

    /**
     * Метод для завершения текущей сессии
     */
    private function endUserSession() {
        unset($_SESSION['userID']);
    }
}