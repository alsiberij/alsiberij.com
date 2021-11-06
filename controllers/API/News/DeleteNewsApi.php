<?php


class DeleteNewsApi implements ApiCall {

    public function respond(?User &$user): void {
        header('Content-type: application/json');
        $error = '';
        if (!$user) {
            http_response_code(401);
            $error = 'Unauthorized';
        } elseif (!isset($_GET['newsID'])) {
            http_response_code(400);
            $error = 'Argument was not passed (newsID)';
        } else {
            $news = &News::newInstance($_GET['newsID']);
            if (!$news) {
                http_response_code(404);
                $error = 'Resource was not found';
            } elseif (!$user->isAdmin() && !($user->isModer() && $news->getAuthor()->getID() == $user->getID())) {
                http_response_code(403);
                $error = 'Access denied';
            } else {
                $comments = CommentNews::getCommentsList($news);
            }
        }

        if ($error) {
            $response = json_encode(array('error'=>$error));
        } else {
            $success = $news->delete();
            http_response_code($success ? 200 : 500);
            $response = json_encode($success ? ['response' => 'success'] : ['error' => 'server error']);

        }

        echo($response);
    }
}