<?php


class LikePhotoApi implements ApiCall {

    public function respond(?User &$user): void {
        header('Content-type: application/json');
        $error = '';
        if (!$user) {
            http_response_code(401);
            $error = 'Unauthorized';
        } elseif (!isset($_GET['photoID'])) {
            http_response_code(400);
            $error = 'Argument was not passed (photoID)';
        } else {
            $photo = &Photo::newInstance($_GET['photoID']);
            if (!$photo) {
                http_response_code(404);
                $error = 'Resource was not found';
            }
        }

        if ($error) {
            $response = json_encode(['error' => $error]);
        } else {
            $success = $user->like($photo);
            http_response_code($success ? 200 : 500);
            $response = json_encode($success ? ['response' => $photo->getLikes()] : ['error' => 'server error']);
        }

        echo($response);
    }
}
