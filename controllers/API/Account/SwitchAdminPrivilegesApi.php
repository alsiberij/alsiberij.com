<?php


class SwitchAdminPrivilegesApi implements ApiCall {

    public function respond(?User &$user): void {
        header('Content-type: application/json');
        $error = '';
        if (!$user) {
            http_response_code(401);
            $error = 'Unauthorized';
        } elseif (!$user->isAdmin()) {
            http_response_code(403);
            $error = 'Access denied';
        } elseif (!isset($_GET['userID'])) {
            http_response_code(400);
            $error = 'Argument was not passed (userID)';
        } else {
            $account = &User::newInstance($_GET['userID']);
            if (!$account) {
                http_response_code(404);
                $error = 'User was not found';
            }
        }

        if ($error) {
            $response = json_encode(['error' => $error]);
        } else {
            $success = $account->setIsAdmin(!$account->isAdmin());
            http_response_code($success ? 200 : 500);
            $response = json_encode($success ? ['response' => $account->isAdmin()] : ['error' => 'server error']);
        }

        echo($response);
    }
}