<?php
/*if (($_NOW = time()) < strtotime('2022-10-12 10:00:00') || $_NOW > strtotime('2022-10-12 11:20:00')) {
    unset($_NOW);
    return;
}*/

?>
    <!doctype html>
    <html lang="ko">
    <head>
        <meta charset="utf-8">

        <meta http-equiv="imagetoolbar" content="no">
        <meta http-equiv="X-UA-Compatible" content="IE=Edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>CTC Wallet</title>

        <!-- CSS only -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous" />
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css" />
        <style type="text/css">
            body {background-color: var(--bs-gray-dark);font-size: 14px;}
            section.card {margin-top: 10%;max-width: 820px;margin-left: auto; margin-right: auto;box-shadow: 0 0 10px rgba(0, 0, 0, .5)}
            section.card .card-body {font-weight: initial;color: var(--bs-gray-300);}

        </style>
    </head>
    <body>
    <div class="container">
        <section class="card text-bg-secondary border-dark">
            <div class="card-header "><h5 class="card-title"><i class="bi bi-info-circle"></i> 점검 안내</h5></div>
            <div class="card-body">
                안녕하세요. CTC Wallet입니다.<br />
                저희 사이트를 이용해 주시는 고객님들께 감사의 말씀을 드립니다.<br /><br />

                시스템 개편을 위하여 일시적으로 서비스가 중단될 예정입니다.<br />
                이에 불편을 끼쳐드려 죄송하다는 말씀을 먼저 드리며, 서비스 일시 중지됨을 알려드립니다.<br /><br />
                <!--<h6 class="text-white">시스템 개편 적용을 위해 2022년 10월 12일(수) 오전 11:00 ~ 11:30까지 전체 서비스가 일시 중단 됩니다.</h6><br />-->
                본 시간동안에는 서비스 접속이 불가능하며 최대한 빠르게 정상적인 서비스를 제공해드릴 수 있도록 노력하겠습니다.<br />
                감사합니다.<br />
            </div>
        </section>

    </div>
    </body>
    </html>
<?php
die;