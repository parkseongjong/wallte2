
/**
 * @api               {get} /google/admob Admob
 * @apiDescription   광고 시청
 * @apiVersion        1.0.0
 * @apiName           Google-Admob
 * @apiGroup          Google
 *
 * @apiQuery {String} ad_network 광고 네트워크 식별자
 * @apiQuery {String} ad_unit 광고 ID
 * @apiQuery {String} key_id 인증키
 * @apiQuery {String} [custom_data]
 * @apiQuery {String} [reward_amount]
 * @apiQuery {String} reward_item ADMOB_FRONT_001 (Reward Item Code)
 * @apiQuery {String} signature 서명
 * @apiQuery {String} timestamp 광고 완료 시간
 * @apiQuery {String} transaction_id 트렌젝션 아이디
 *
 * @apiSampleRequest  /wallet2/v1/google/admob
 *
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 * @apiExample {curl} Example usage:
 *    /wallet2/v1/admob/callback?user_id={user_id}&ad_network={ad_network}
 *    &ad_unit={ad_unit}&custom_data={custom_data}&key_id={key_id}&reward_amount={reward_amount}
 *    &reward_item={reward_item}&signature={signature}&timestamp=1507770365237823&transaction_id={transaction_id}
 *
 * @apiSuccess {int} paid_point 1000
 * @apiSuccess {int} free_point 10
 *
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "paid_point" : 1000,
 *         "free_point" : 10
 *     }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 *
 * @apiUse UnauthorizedException
 * @apiErrorExample 400 InvalidArgumentException
 * Error 400: Bad Request
 * {
 *     "error": "invalid_signature",
 *     "message": "Invalid Signature"
 * }
 * @apiErrorExample 400 BadRequestException
 * Error 400: Bad Request
 * {
 *     "error": "waiting_time",
 *     "message": "8분 13초 후에 시청하실수 있습니다."
 * }
 * Error 400: Bad Request
 * {
 *     "error": "finished_watching_ad",
 *     "message": "더 이상 광고 보기를 하실 수 없습니다."
 * }
*/

/**
 * @api               {get} /google/admob/check Admob Check
 * @apiDescription   광고 시청 확인
 * @apiVersion        1.0.0
 * @apiName           Google-Admob-Check
 * @apiGroup          Google
 *
 * @apiSampleRequest  /wallet2/v1/google/admob/check
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 * @apiSuccess {String} status "finished|waiting|ok"
 * @apiSuccess {int} count Max Count 7
 * @apiSuccessExample Success-Response:
 * HTTP/1.1 200 OK
 * {
 *     "status": "ok",
 *     "count": 0
 * }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 * @apiUse UnauthorizedException
 */


/**
 * @api               {post} /google/purchase Purchase
 * @apiVersion        1.0.0
 * @apiName           Google-Purchase
 * @apiGroup          Google
 * @apiDescription    인앱 결제
 *
 * @apiSampleRequest  /wallet2/v1/google/purchase
 * @apiHeader {String} Authorization='Bearer autologin|eyJ0eXAiOiJKV1QiLCJ....1MfoVOmztTMBv4izEtKlY'
 *
 * @apiBody  {String} product_id Product Id
 * @apiBody {String} purchase_token Purchase Token
 *
 * @apiExample {json} Request-Example:
 * {
 *      "product_id": "PRODUCT_001",
 *      "purchase_token": "MEUCIQCLJS_s4ia_sN06HqzeW7Wc3nhZi4RlW3qV0oO-6AIYdQIgGJEh-rzKreO-paNDbSCzWGMtmgJHYYW9k2_icM9LFMY"
 * }
 * @apiExample {curl} Curl Example usage:
 *     curl  --header "Content-Type: application/json"\
 *           --header "Authorization: Bearer autologin|KYVAFULuO7fDHjZ3oiCLgYGdTclmkGKLyiakSFqg" \
 *           --request POST \
 *           --data '{"product_id": "PRODUCT_001", "purchase_token": "MEUCIQCLJS_s4ia_sN06HqzeW7Wc3nhZi4RlW3qV0oO-6AIYdQIgGJEh-rzKreO-paNDbSCzWGMtmgJHYYW9k2_icM9LFMY"}' \
 *           https://www.cybertronchain.com/wallet2/v1/google/purchase
 *
 * @apiSuccess {int} paid_point 1000
 * @apiSuccess {int} free_point 10
 * @apiSuccessExample Success-Response:
 *     HTTP/1.1 200 OK
 *     {
 *         "paid_point" : 1000,
 *         "free_point" : 10
 *     }
 *
 * @apiError {String} error Error Code
 * @apiError {String} message Error Message
 * @apiUse UnauthorizedException
 * @apiErrorExample 400 InvalidArgumentException
 * Error 400: Bad Request
 * {
 *     "error": "invalid_product_id",
 *     "message": "Invalid Product Id"
 * }
 * Error 400: Bad Request
 * {
 *     "error": "purchase_token",
 *     "message": "Purchase Token"
 * }
 *
 * @apiErrorExample 400 BadRequestException
 * Error 400: Bad Request
 * {
 *     "error": "order_already_complete",
 *     "message": "완료 된 주문 내역입니다."
 * }
 */


