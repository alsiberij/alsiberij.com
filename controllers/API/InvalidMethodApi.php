<?php


class InvalidMethodApi implements ApiCall {

    public function respond(?User &$user): void {
        http_response_code(400);
        header('Content-type: application/json');
        echo json_encode(['error' => 'invalid method']);
    }
}