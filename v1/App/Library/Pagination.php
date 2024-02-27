<?php
namespace App\Library;

/**
 * Class Pagination
 * @package api\library
 * @author jso
 * @since 2018-02-28
 */
class Pagination
{

    /**
     * @Constructor
     */
    public function __construct()
    {
    }

    /**
     * @param $totalEntries 게시물의 총 수
     * @param $page 현재 페이지 번호
     * @param $perEntries 페이지당 출력할 게시물 갯수
     * @param $perLinks 페이지당 출력할 페이지 링크 수
     * @param $pageName
     * @return array
     * @description 페이징 계산 함수(Template을 갖지 않음)
     */
    public function getPagingInfo(
        $totalEntries,
        $page = 1,
        $perEntries = 20,
        $perLinks = 10,
        $pageName = 'page'
    ): array
    {

        $pagination = [
            'first' => null,
            'last' => null,
            'prev' => null,
            'next' => null,
            'list' => null,
            'pages' => null,
            'page' => null,
            'offset' => null,
            'limitSql' => null,
            'skip' => null,
            'index' => 0
        ];

        /*--------  페이지 URL 설정 --------*/
        $parts = parse_url($_SERVER['REQUEST_URI']);
        $base_url = '';

        // Query string이 있을 경우
        if (!empty($parts['query'])) {
            $parts['query'] = trim(
                preg_replace(
                    '/&?(' . preg_quote($pageName) . '\=)([^&]+)?/',
                    '',
                    $parts['query']
                ),
                '&'
            );
        }

        // Scheme(http or https) AND hostname
        if (!empty($parts['scheme']) && !empty($parts['host'])) {
            $base_url = $parts['scheme'] . '://' . $parts['host'];
        }

        $base_url .= $parts['path'] . '?';

        if (!empty($parts['query'])) {
            $base_url .= rtrim($parts['query'], '&') . '&';
        }

        $base_url .= $pageName . '=';
        $pagination['current'] = $base_url.$page;

        if ($page < 1) {
            $page = 1;
        }

        if ($totalEntries < 1) {
            $totalEntries = 1;
        }

        if ($perLinks < 1) {
            $perLinks = $totalEntries + 1;
        }

        $pagination['page'] = $page;

        /*--------  전체 페이지 수 구하기 --------*/
        $pagination['pages'] = (INT)($totalEntries / $perEntries);

        if ($pagination['pages'] < 1) {
            // 전체 페이지 수가 1보다 작은 경우 전체 페이지수는 1개
            $pagination['pages'] = 1;
        }

        if (($totalEntries > $perEntries)
            && ($totalEntries % $perEntries) > 0) {
            // 마지막 페이지의 게시물 수의 나머지 값이 있는 경우 전체 페이지 수 증가
            $pagination['pages'] += 1;
        }

        if ($totalEntries < 1 || $pagination['page'] < 1) {
            // 전체 게시물 수가 없는 경우 현재 페이지는 1page
            $pagination['page'] = 1;
        } elseif ($pagination['page'] > $pagination['pages']) {
            // 전체 페이지수를 넘는 경우
            $pagination['page'] = $pagination['pages'];
        }

        /*--------  Offset 구하기 --------*/
        $start = ($pagination['page'] - 1) * $perEntries;
        //MySQL
        $pagination['offset'] = array($start, $perEntries);
        $pagination['limitSql'] = " limit {$start}, {$perEntries}";
        //MongoDB
        $pagination['skip'] = ($page-1) * $perEntries;
        //Oracle
        $pagination['row_min'] = ($pagination['page'] * $perEntries) - $perEntries;
        $pagination['row_max'] = $pagination['row_min'] + $perEntries;
        $pagination['row_min']++;

        /*--------  처음, 마지막 페이지 구하기 --------*/
        if ($pagination['pages'] > $perLinks) {
            if ($pagination['page'] > $perLinks) {
                // 처음 페이지
                $pagination['first'] = $base_url . '1';
            }
            if (($pagination['pages'] - $pagination['page']) > $perLinks) {
                //마지막 페이지
                $pagination['last'] = $base_url . $pagination['pages'];
            }
        }
        if ($pagination['page'] > 0 && $totalEntries > $perEntries) {
            $pagination['first'] = $base_url . '1'; // 처음 페이지
        }
        if (($pagination['pages'] - $pagination['page']) > 0
            && $totalEntries > $perEntries) {
            //마지막 페이지
            $pagination['last'] = $base_url . $pagination['pages'];
        }
        /*--------  이전, 다음 페이지 구하기 --------*/
        if ($pagination['page'] > 1) {
            // 이전 페이지
            $pagination['prev'] = $base_url . ($pagination['page'] - 1);
        }
        if ($pagination['page'] + 1 <= $pagination['pages']) {
            $pagination['next'] = $base_url . ($pagination['page'] + 1);
        }

        $nInspire = (((int)(($pagination['page'] - 1) / $perLinks)) * $perLinks) + 1;
        if ($nInspire < 1) {
            $nInspire = 1;
        }

        $nExpire = $nInspire + $perLinks - 1;
        if ($nExpire >= $pagination['pages']) {
            $nExpire = $pagination['pages'];
        }

        $pagination['list'] = array();
        for ($nIndex = $nInspire; $nIndex <= $nExpire; $nIndex++) {
            $pagination['list'][$nIndex] = $base_url . $nIndex;
        }

        $pagination['index'] = $totalEntries - $start;

        return $pagination;
    }
}

/* End of file Pagination.php */
/* Location: /app/Library/Pagination.php */
