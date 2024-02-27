/**
 * @api               {get} /user Info
 * @apiDescription    유저 정보
 * @apiVersion        1.0.0
 * @apiName           User-Info
 * @apiGroup          User
 *
 * @apiSampleRequest  /wallet2/v1/user
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 * @apiSuccess {int} paid_point 1000
 * @apiSuccess {int} free_point 10
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "paid_point": 6000,
 *     "free_point": 28
 * }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 * @apiUse UnauthorizedException
 */

/**
 * @api               {get} /user/free Free Point
 * @apiDescription    유저 무료 포인트 리스트
 * @apiVersion        1.0.0
 * @apiName           User-Free
 * @apiGroup          User
 *
 * @apiSampleRequest  /wallet2/v1/user/free
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 * @apiSuccess {int} page 1
 * @apiSuccess {int} page_size 15
 * @apiSuccess {int} total_count 100
 * @apiSuccess {int} total_page 10
 * @apiSuccess {array} list []

 * @apiSuccess {string} list.code code
 * @apiSuccess {string} list.code "PURCHASE",
 * @apiSuccess {int} list.amount 1000,
 * @apiSuccess {int} list.balance 1000,
 * @apiSuccess {string} list.type P, M,
 * @apiSuccess {int} list.quantity 1,
 * @apiSuccess {string} list.comment "1000 Cash",
 * @apiSuccess {string} list.created "2022-07-22 05:24:50"
 *
 * @apiQuery {int} [page] 1
 *
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "page": 1,
 *     "page_size": 15,
 *     "total_count": 1,
 *     "total_page": 1,
 *     "list": [
 *         {
 *             "code": "ATTENDANCE",
 *             "quantity": 1,
 *             "amount": 1,
 *             "balance": 1,
 *             "comment": "구글 광고 보상",
 *             "created": "2022-08-02 08:55:43",
 *             "type": "P"
 *         }
 *     ]
 * }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 * @apiUse UnauthorizedException
 *
 * @apiErrorExample 400 InvalidArgumentException
 * Error 400: Bad Request
 * {
 *     "error": "invalid_page",
 *     "message": "Invalid Page"
 * }
 */


/**
 * @api               {get} /user/paid Paid Point
 * @apiDescription    유저 유료 포인트 리스트
 * @apiVersion        1.0.0
 * @apiName           User-Paid
 * @apiGroup          User
 *
 * @apiSampleRequest  /wallet2/v1/user/paid
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 * @apiSuccess {int} page 1
 * @apiSuccess {int} page_size 15
 * @apiSuccess {int} total_count 100
 * @apiSuccess {int} total_page 10
 * @apiSuccess {array} list []
 * @apiSuccess {string} list.code code
 * @apiSuccess {string} list.code "PURCHASE",
 * @apiSuccess {int} list.amount 1000,
 * @apiSuccess {int} list.balance 1000,
 * @apiSuccess {string} list.type P, M,
 * @apiSuccess {int} list.quantity 1,
 * @apiSuccess {string} list.comment "1000 Cash",
 * @apiSuccess {string} list.created "2022-07-22 05:24:50"
 *
 * @apiQuery {int} [page] 1
 *
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "page": 1,
 *     "page_size": 15,
 *     "total_count": 2,
 *     "total_page": 1,
 *     "list": [
 *         {
 *             "code": "PURCHASE",
 *             "amount": 1000,
 *             "balance": 1000,
 *             "type": "P",
 *             "quantity": 1,
 *             "comment": "1000 Cash",
 *             "created": "2022-07-22 05:24:50"
 *         }
 *     ]
 * }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 * @apiUse UnauthorizedException
 *
 * @apiErrorExample 400 InvalidArgumentException
 * Error 400: Bad Request
 * {
 *     "error": "invalid_page",
 *     "message": "Invalid Page"
 * }
 */
