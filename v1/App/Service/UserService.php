<?php

namespace App\Service;

use App\Exception\InvalidArgumentException;
use App\Exception\BadRequestException;
use App\Exception\ApiException;
use Exception;
use App\Library\Pagination;
use App\Models\PaidModel;
use App\Models\PointModel;
use App\Models\PurchaseModel;


/**
 * Class UserService
 *
 * @package App\Service
 * @author seungo.jo
 * @since 2018-02-27
 */
class UserService
{
    private $userID;

    const ENTRIES = 15;

    public function getUserId()
    {
        return $this->userID;
    }

    public function setUserId($userID)
    {
        $this->userID = $userID;
    }

    /**
     */
    public function __construct()
    {
    }

    /**
     * @throws BadRequestException
     */
    public function UserPurchaseList($params)
    {
        $bind = [
            'user_id' => $this->userID,
            's_date' => strtotime($params['s_date'] . ' 00:00:00'),
            'e_date' => strtotime($params['e_date'] . ' 23:59:59'),
        ];
        $purchaseModel = new PurchaseModel();
        $totalCount = $purchaseModel->getPurchaseCount($bind);
        $pagination = new Pagination();
        $pagingInfo = $pagination->getPagingInfo(
            $totalCount,
            $params['page'],
            self::ENTRIES,
            10,
            'page'
        );

        if ($pagingInfo['pages'] < $params['page']) {
            throw new BadRequestException('invalid_page');
        }

        $bind['start'] = $pagingInfo['offset'][0];
        $bind['end'] = $pagingInfo['offset'][1];
        $list = [];
        if ($totalCount > 0) {
            $list = $purchaseModel->getPurchaseList($bind);
        }

        return [
            'page' => $pagingInfo['page'],
            'page_size' => self::ENTRIES,
            'total_count' => $totalCount,
            'total_page' => $pagingInfo['pages'],
            'list' => $list
        ];
    }


    /**
     * @throws BadRequestException
     */
    public function UserPaidList($params)
    {
        $bind = [
            'user_id' => $this->userID,
            's_date' => strtotime($params['s_date'] . ' 00:00:00'),
            'e_date' => strtotime($params['e_date'] . ' 23:59:59'),
        ];

        $PaidModel = new PaidModel();
        $totalCount = $PaidModel->getPaidCount($bind);
        $pagination = new Pagination();
        $pagingInfo = $pagination->getPagingInfo(
            $totalCount,
            $params['page'],
            self::ENTRIES,
            10,
            'page'
        );

        if ($pagingInfo['pages'] < $params['page']) {
            throw new BadRequestException('invalid_page');
        }

        $bind['start'] = $pagingInfo['offset'][0];
        $bind['end'] = $pagingInfo['offset'][1];
        $list = [];
        if ($totalCount > 0) {
            $list = $PaidModel->getPaidList($bind);
            foreach ($list as $key => $row) {
                $row = $PaidModel::removePrefix($row, $PaidModel::PREFIX['PAID']);
                $row['created'] = getDateTime($row['created']);
                $list[$key] = $row;
            }
        }

        return [
            'page' => $pagingInfo['page'],
            'page_size' => self::ENTRIES,
            'total_count' => $totalCount,
            'total_page' => $pagingInfo['pages'],
            'list' => $list
        ];
    }

    /**
     * @throws BadRequestException
     * @throws \Doctrine\DBAL\Exception
     */
    public function UserFreeList($params)
    {
        $bind = [
            'user_id' => $this->userID,
            's_date' => strtotime($params['s_date'] . ' 00:00:00'),
            'e_date' => strtotime($params['e_date'] . ' 23:59:59'),
        ];

        $pointModel = new PointModel();
        $totalCount = $pointModel->getPointCount($bind);
        $pagination = new Pagination();
        $pagingInfo = $pagination->getPagingInfo(
            $totalCount,
            $params['page'],
            self::ENTRIES,
            10,
            'page'
        );

        if ($pagingInfo['pages'] < $params['page']) {
            throw new BadRequestException('invalid_page');
        }

        $bind['start'] = $pagingInfo['offset'][0];
        $bind['end'] = $pagingInfo['offset'][1];
        $list = [];
        if ($totalCount > 0) {
            $list = $pointModel->getPointList($bind);
            foreach ($list as $key => $row) {
                $row = $pointModel::removePrefix($row, $pointModel::PREFIX['FREE']);
                $row['created'] = getDateTime($row['created']);
                $list[$key] = $row;
            }
        }

        return [
            'page' => $pagingInfo['page'],
            'page_size' => self::ENTRIES,
            'total_count' => $totalCount,
            'total_page' => $pagingInfo['pages'],
            'list' => $list
        ];
    }

}
