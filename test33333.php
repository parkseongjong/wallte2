  <?php
  exit();
session_start();
require_once './config/config.php';
require_once './config/new_config.php';


$st = filter_input(INPUT_GET, 'st');
$q = filter_input(INPUT_GET, 'q');
$offset = filter_input(INPUT_GET, 'offset');
$page = filter_input(INPUT_GET, 'page');
$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');

if ( $offset == '' ) {
	$offset = 10;
}
if ( $page == '' ) {
	$page = 1;
}


if ($filter_col == "") {
    $filter_col = "id";
}
if ($order_by == "") {
    $order_by = "desc";
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
	$db = getDbInstance();
	if ( $st == 'addr' ) {
		$db->where('wallet_address', $q);
	}
	if ($order_by) {
		$db->orderBy($filter_col, $order_by);
	}

	$db->pageLimit = $offset;
	$resultData = $db->arraybuilder()->paginate("etoken_logs", $page);
	$total_pages = $db->totalPages;
}

function addr_text_block($addr) {
	$tmp = substr($addr, 6, 30);
	$addr = str_replace($tmp, '********', $addr);
	//$addr = str_pad(substr($addr, 0, 10), strlen($addr), "*");
	return $addr;
}


$fee_unit = 'E-CTC'; // ������ ����
  ?>

  <form method="get" name="search" id="searchfrm">

		<select name="st">
			<option value="addr" <?php if ( $st == 'addr' ) echo "selected"; ?>">Address</option>
			<!--<option value="epay" <?php if ( $st == 'epay' ) echo "selected"; ?>">E-PAY</option>-->
		</select>

		<input type="text" name="q" id="q" value="<?php if ( $q ) echo $q; ?>" placeholder="Search by Address" size="100" required />

		<input type="hidden" name="filter_col" id="filter_col" value="" />
		<input type="hidden" name="order_by" id="order_by" value="" />

		<input type="submit" value="search" />
  </form>

<script>
function order2(filter_col, order_by) {
	var sform = document.search;
	sform.filter_col.value = filter_col;
	if ( order_by == 'desc' ) {
		sform.order_by.value = 'asc';
	} else {
		sform.order_by.value = 'desc';
	}
	sform.submit();
}
</script>


  <?php
if ($_SERVER['REQUEST_METHOD'] == 'GET' && !empty($st) ) {
	?>
	<table border="1">
		<thead>
			<tr>
				<th>From</th>
				<th>To</th>
				<th>Point <a href="javascript:;" onclick="order2('points', '<?php echo $order_by; ?>');">order</a></th>
				<th>Fee <a href="javascript:;" onclick="order2('send_fee', '<?php echo $order_by; ?>');">order</a></th>
				<th>Date <a href="javascript:;" onclick="order2('id', '<?php echo $order_by; ?>');">order</a></th>
			</tr>
		</thead>
		<tbody>
			<?php
			foreach($resultData as $row) {
				$coin = new_number_format($row['points'], $n_decimal_point_array2[$row['coin_type']]).' '.$n_epay_name_array[$row['coin_type']];

				$db = getDbInstance();
				$db->where('id', $row['user_id']);
				$getData = $db->getOne('admin_accounts');
				$name = '';
				$name = get_user_real_name($getData['auth_name'], $getData['name'], $getData['lname']);

				$db = getDbInstance();
				$db->where('id', $row['send_user_id']);
				$getTargetData = $db->getOne('admin_accounts');
				$target_name = '';
				$target_name = get_user_real_name($getTargetData['auth_name'], $getTargetData['name'], $getTargetData['lname']);


				$addr = $row['wallet_address']; // �ŷ���� �ּ�
				$addr = addr_text_block($addr);
				
				$target_addr = $row['send_wallet_address'];
				$target_addr = addr_text_block($target_addr);

				if ( $row['in_out'] == 'out' ) {
					// from : user_id, to : send_user_id
					$from_addr = $addr;
					$from_name = $name;
					$to_addr = $target_addr;
					$to_name = $target_name;
				} else {
					// from : send_user_id, to : user_id
					$from_addr = $target_addr;
					$from_name = $target_name;
					$to_addr = $addr;
					$to_name = $name;
				}

				?><tr>
					<td><?php echo $from_name.'<br />'.$from_addr; ?></td>
					<td><?php echo $to_name.'<br />'.$to_addr; ?></td>
					<td><?php echo $coin; ?></td>
					<td><?php if ( !empty($row['send_fee']) ) { echo $row['send_fee'].' '.$fee_unit; } ?></td>
					<td><?php echo $row['created_at']; ?></td>
				</tr>
			<?php
			} // foreach
		?></tbody>
	</table>
	
	<div class="text-center">
		<?php
		$currentPage = 1;
		$get_infos = '';
		if ( isset($_GET) &&!empty($_GET) ) {
			$get_infos = $_GET;
			if (isset($_GET['page']) && !empty($_GET['page'])) {
				$currentPage = $_GET['page'];
			}
		}
		echo new_set_page_list($currentPage, '', $total_pages, $get_infos, '10');
		?>
	</div>


	<?php
} // if (post)

?>
