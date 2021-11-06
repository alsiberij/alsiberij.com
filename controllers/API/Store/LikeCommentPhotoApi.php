<?php


class LikeCommentPhotoApi implements ApiCall {

    public function respond(?User &$user): void {
        header('Content-type: application/json');
        $error = '';
        if (!$user) {
            http_response_code(401);
            $error = 'Unauthorized';
        } elseif (!isset($_GET['commentID'])) {
            http_response_code(400);
            $error = 'Argument was not passed (commentID)';
        } else {
            $comment = &CommentPhoto::newInstance($_GET['commentID']);
            if (!$comment) {
                http_response_code(404);
                $error = 'Resource was not found';
            }
        }

        if ($error) {
            $response = json_encode(['error' => $error]);
        } else {
            $success = $user->like($comment);
            http_response_code($success ? 200 : 500);
            $response = json_encode($success ? ['response' => $comment->getLikes()] : ['error' => 'server error']);
        }

        echo($response);
    }
}