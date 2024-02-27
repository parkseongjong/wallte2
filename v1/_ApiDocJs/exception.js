/**
 * @apiDefine UnauthorizedException
 * @apiErrorExample 401 UnauthorizedException
 * Error 401: Unauthorized
 * {
 *     "error": "invalid_access_token",
 *     "message": "Invalid Access Token."
 * }
 * Error 401: Unauthorized
 * {
 *     "error": "bad_request_autologin",
 *     "message": "비정상적인 요청 입니다. 자동로그인을 다시 설정해 주세요."
 * }
 * Error 401: Unauthorized
 * {
 *     "error": "expired_key",
 *     "message": "만료 된 KEY 값 입니다.(15일간 유효) 자동로그인을 다시 설정해 주세요."
 * }
 * Error 401: Unauthorized
 * {
 *     "error": "not_match_key",
 *     "message": "KEY 값 정보가 일치하지 않습니다. 자동로그인을 다시 설정해 주세요."
 * }
 * */
