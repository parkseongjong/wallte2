<h3>비밀번호 설정하기(Set passcode)</h3>
<form action="set_form.pro.php" method="post" name="frm1">
	
	<input type="hidden" name="mode" value="set_passcode" />

	<label for="passcode1">사용할 passcode(사용자ID) 입력 / UserID</label><br />
	<input type="text" name="passcode1" id="passcode1" required size="50" placeholder="+821012345678 또는 aaaaa@aaaaa.com" /><br /><br />
	
	<label for="key1">passcode를 적용할 PVT Key 입력</label><br />
	<input type="text" name="key1" id="key1" required size="50"  /><br /><br />
	
	<label for="pass1">암호</label><br />
	<input type="hidden" name="passwd" id="pass1" required size="50" value="cybertronchain" /><br /><br />
	
	<input type="submit" value="비밀번호 설정" />
</form>

<br />
<h3>키 확인하기(Get Key)</h3>
<form action="set_form.pro.php" method="post" name="frm2">
	
	<input type="hidden" name="mode" value="get_key" />

	<label for="wallet_address1">Wallet Address</label><br />
	<input type="text" name="wallet_address1" id="wallet_address1" required size="50" /><br /><br />
	
	<label for="passcode2">비밀번호(사용자ID) Passcode(UserID)</label><br />
	<input type="text" name="passcode2" id="passcode2" required size="50" /><br /><br />
	
	<label for="pass2">암호</label><br />
	<input type="hidden" name="passwd" id="pass2" required size="50" value="cybertronchain" /><br /><br />
	
	<input type="submit" value="키 확인" />
</form>
