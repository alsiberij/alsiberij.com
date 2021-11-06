<?php


class ApiCallFactory{

    public static function newInstance($methodName): ApiCall {
        switch ($methodName) {
            case 'LikeNews': return new LikeNewsApi();
            case 'LikeCommentNews': return new LikeCommentNewsApi();
            case 'ChangeNewsPrivacy': return new ChangeNewsPrivacyApi();
            case 'DeleteNews': return new DeleteNewsApi();

            case 'SwitchAdminPrivileges': return new SwitchAdminPrivilegesApi();
            case 'SwitchModerPrivileges': return new SwitchModerPrivilegesApi();

            case 'LikePhoto': return new LikePhotoApi();
            case 'LikeCommentPhoto': return new LikeCommentPhotoApi();
            case 'BuyPhoto': return new BuyPhotoApi();

            default: return new InvalidMethodApi();
        }
    }

}