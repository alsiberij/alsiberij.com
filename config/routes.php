<?php

return array(
    '^api$' => 'Api -> Do',

    '^news/(\d+)$' => 'News -> ShowNews -> $1',
    '^news/add$' => 'News -> Add',
    '^news$' => 'News -> Index',

    '^account/signup$' => 'Account -> SignUp',
    '^account/login$' => 'Account -> LogIn',
    '^account/logout$' => 'Account -> LogOut',
    '^account/activation' => 'Account -> Activation',
    '^account/(\d+)/manage$' => 'Account -> ManageAccount -> $1',
    '^account/(\d+)$' => 'Account -> ShowAccount -> $1',
    '^account$' => 'Account -> Index',

    '^store/buy$' => 'Store -> BuyItem',
    '^store/download$' => 'Store -> DownloadItem',
    '^store/add/category$' => 'Store -> AddCategory',
    '^store/add/photo$' => 'Store -> AddPhoto',
    '^store/([a-zA-Z]+)$' => 'Store -> IndexCategory -> $1',
    '^store/([a-zA-Z]+)/(\d+)$' => 'Store -> ShowItem -> $1 & $2',
    '^store$' => 'Store -> Index',

    '^$' => 'Base -> Index'
);