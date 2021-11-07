<?php

return array(
    '^api$' => 'Api -> Respond',

    '^news/(\d+)$' => 'News -> Show -> $1',
    '^news/add$' => 'News -> Add',
    '^news$' => 'News -> Index',

    '^account/signup$' => 'Account -> SignUp',
    '^account/login$' => 'Account -> LogIn',
    '^account/logout$' => 'Account -> LogOut',
    '^account/activate' => 'Account -> Activate',
    '^account/(\d+)/manage$' => 'Account -> Manage -> $1',
    '^account/(\d+)$' => 'Account -> Show -> $1',
    '^account$' => 'Account -> Index',

    '^store/buy$' => 'Store -> Buy',
    '^store/download$' => 'Store -> Download',
    '^store/add/category$' => 'Store -> AddCategory',
    '^store/add/photo$' => 'Store -> AddPhoto',
    '^store/([a-zA-Z]+)$' => 'Store -> ShowCategory -> $1',
    '^store/([a-zA-Z]+)/(\d+)$' => 'Store -> ShowPhoto -> $1 & $2',
    '^store$' => 'Store -> Index',

    '^$' => 'Base -> Index'
);