<?php
// Previously used pages (not used now)
exit();
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

function dd($var='') {
	ob_start();
	echo '<pre>';
	var_export($var);
	$result = ob_get_clean();
	die($result);
}

function getStoreLoops($name) { 
	$db = getDbInstance();
	$select = array('id', 'store_name','store_cat','store_region','store_address','store_phone','store_wallet_address','store_description','created_at');

	$db->orderby("store_region", "asc");
	
	$search_string = filter_input(INPUT_GET, 'search_string');
	$del_id = filter_input(INPUT_GET, 'del_id');	
	$filter_col = filter_input(INPUT_GET, 'filter_col');
	$order_by = filter_input(INPUT_GET, 'order_by');
	$page = filter_input(INPUT_GET, 'page');
	$pagelimit = filter_input(INPUT_GET, 'filter_limit');

	if($pagelimit == "") {
		$pagelimit = 20;
	}

	if ($page == "") {
		$page = 1;
	}

	if ($filter_col == "") {
	$filter_col = "id";
	}

	if ($order_by == "") {
	$order_by = "desc";
	}
	
	
	$db->pageLimit = $pagelimit;
	$total_pages = $db->totalPages;
	$query = $db->where('store_region = "'.$name.'"')->arraybuilder()->paginate("stores", $page, $select);
	
	?>
	
	<h3><?php echo $name; ?></h3>
		<div class="table-responsive">
		    <table class="table table-bordered">
		        <thead>
		            <tr>
		                <th width="10%">Name</th>
		                <th width="5%">Category</th>
		                <th width="5%">Region</th>
		                <th width="20%">Store Address</th>
		                <th width="10%">Phone</th>
						<th width="20%">Wallet Address</th>
						<th width="20%">Description</th>
						<th width="10%">Created</th>
		            </tr>
		        </thead>
		        <tbody>
		        <?php foreach ($query as $row) { ?>					
		            <tr>
		                <td><?php echo htmlspecialchars($row['store_name']) ?></td>
		                <td><?php echo htmlspecialchars($row['store_cat']) ?></td>
		                <td><?php echo htmlspecialchars($row['store_region']) ?></td>
		                <td><?php echo htmlspecialchars($row['store_address']) ?></td>
		                <td><?php echo htmlspecialchars($row['store_phone']) ?></td>
						<td><?php echo htmlspecialchars($row['store_wallet_address']) ?></td>
						<td><?php echo htmlspecialchars($row['store_description']) ?></td>
						<td><?php echo htmlspecialchars($row['created_at']) ?></td>
		            </tr>
		            <?php } ?>   
		        </tbody>
		    </table>	
		</div>
		
<?php }

include_once 'includes/header.php'; ?>

<style>
.well {
	background-color:inherit;
	text-align:left;
}

#page-wrapper {
	height:auto !important;
}
</style>

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header">Customer Stores</h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
<?php include('./includes/flash_messages.php') ?>

	<div class="expand-collapse">		
		
		<?php
			
			 getStoreLoops('서울'); 
			 
			 getStoreLoops('인천'); 
			 
			 getStoreLoops('경기'); 
			 
			 getStoreLoops('충남'); 

		?>
		
    <!-- Pagination links-->
    <div class="text-center none">

        <?php
        /*
if (!empty($_GET)) {
            //we must unset $_GET[page] if built by http_build_query function
            unset($_GET['page']);
            $http_query = "?" . http_build_query($_GET);
        } else {
            $http_query = "?";
        }
        if ($total_pages > 1) {
            echo '<ul class="pagination text-center">';
            for ($i = 1; $i <= $total_pages; $i++) {
                ($page == $i) ? $li_class = ' class="active"' : $li_class = "";
                echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul></div>';
        }
*/
        ?>
    </div>
</div>

<script type="text/javascript">
	
	$(document).ready(function() {
	$('.expand-collapse h3').each(function() {
		var tis = $(this), state = false, answer = tis.next('div').slideUp();
		tis.click(function() {
			state = !state;
			answer.slideToggle(100);
			tis.toggleClass('active',state);
		});
	});
});
	</script>
	

<?php include_once 'includes/footer.php'; ?>