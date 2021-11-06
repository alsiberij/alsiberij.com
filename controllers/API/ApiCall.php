<?php

interface ApiCall {

    public function respond(?User &$user): void;

}