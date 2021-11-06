<?php


class BuyPhotoApi implements ApiCall {

    public function respond(?User &$user): void {
        header('Content-type: application/json');
        $error = '';
        if (!$user) {
            http_response_code(401);
            $error = 'Unauthorized';
        } elseif (!isset($_GET['photoID'])) {
            http_response_code(400);
            $error = 'Argument was not passed (photoID)';
        } elseif (!isset($_GET['bundle'])) {
            http_response_code(400);
            $error = 'Argument was not passed (bundle)';
        } elseif (!in_array($_GET['bundle'], ['JPG', 'RAW', 'JPGRAW'])) {
            http_response_code(400);
            $error = 'Invalid bundle';
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
            do {
                $token = DownloadLink::generateToken();
            } while (!DownloadLink::isUniqueToken($token));

            $payErrors = DownloadLink::create($token, $user, $photo, $_GET['bundle']);
            if (empty($payErrors)) {
                $photo->increaseDownloads();
                $user->increaseDownloads();
                $photo->getCategory()->increaseDownloads();
                $fullLink = WEB."store/download?token=$token";

                $msg = "
                            <!DOCTYPE html>
                            <html lang='ru'>
                                Премногоуважаемый(ая) <b>".$user->getNickname()."</b>. Ваша ссылка для скачивания: $fullLink действительна в течение 24-x часов.
                            </html>";

                $from = 'From: '.EMAIL.'\r\n';

                mail($user->getEmail(), 'Ваш заказ на alsiberij.com', $msg, $from);
                $response = json_encode(['success' => 'true']);
            } else {
                $response = json_encode(['error' => 'payment error']);
            }
        }
        echo($response);
    }

}