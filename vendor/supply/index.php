<?php 
	
echo '9999435479.383826';	
	

// mk eth 0x986f2D7Ca6fa33C1e02d639f53D2E926acf20587
// tets 0x69f0ac3bb7c4779eb3ef30a76743a930d82a5667
/*

0x88f9af7b596bdc8d1b54e9dc6a2f27df2eed210c
0xf891568ad820be8a04ef898818740c498b06704d
0x6d229d5fb4c4700617ae890b5e2dc5fcd1fef403
0x18444c3e336a851d39289122f7f26d933536c517
0x1b94f5b888655a85694e56a0e997286a35690d7a
0x0b8b02ca231d0f371162d3ef8ba77794afd8d7cf
0xfa4d20b9790f9ea92e48722415ecd47021e5f95c
0x4f5675ca15a4b47f8190c5a006f296b2fa4fa05f
0x91dc16efff381b012255fccd42163717539e0a39
0x69f0ac3bb7c4779eb3ef30a76743a930d82a5667
*/

/*
function find_key_value($address, $key, $val)
{
	$json = 'http://api.ethplorer.io/getAddressInfo/'.$address.'?apiKey=freekey';
	$array = json_decode(file_get_contents($json), true);
	
    foreach ($array as $item)
    {
        if (is_array($item) && find_key_value($item, $key, $val)) { return 'yes1'; };

        if (isset($item[$key]) && $item[$key] == $val) { return 'yes2'; };
    }

	return 'no';
	
}


echo find_key_value('0x85c199aceb4634325c18e4786107c891a14abff6', 'symbol', 'CTC');



echo '<pre>';
//var_export($array);
echo '</pre>';
*/


?>
	