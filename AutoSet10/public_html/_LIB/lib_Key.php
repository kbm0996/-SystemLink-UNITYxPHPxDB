<?php
function KeyGen32()
{
	$Key = hash('md5', time() + rand(0,1000));

	for($i=0; $i<6; ++$i)
	{
		$UpperCase = rand(0,31);
		$Key[$UpperCase] = strtoupper($Key[$UpperCase]);
	}

	return substr($Key, 0, 32);
}

function Hashing64($Value)
{
	// + 보안을 위해 소금치는게 좋음(Salt값 부여)
	$Key = hash('sha256', $Value);

	return substr($Key, 0, 64);
}

?>