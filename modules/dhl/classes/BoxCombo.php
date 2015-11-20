<?php

function generate_box_combos($a, $n)
{
	if ($n == 1)
		return get_1_pack($a);
	elseif ($n == 2)
		return get_2_pack($a);
	elseif ($n == 3)
		return get_3_pack($a);
	elseif ($n == 4)
		return get_4_pack($a);
	elseif ($n == 5)
		return get_5_pack($a);
	elseif ($n == 6)
		return get_6_pack($a);
	elseif ($n == 7)
		return get_7_pack($a);
	elseif ($n == 8)
		return get_8_pack($a);
	elseif ($n == 9)
		return get_9_pack($a);
	else 
		return get_10_pack($a, $n);
}

function get_1_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	{
		$tar = vsort(array($a[$i1]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_2_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	{
		$tar = vsort(array($a[$i1],$a[$i2]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_3_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_4_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_5_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_6_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	for ($i6 = $i5 ; $i6 < sizeof($a) ; $i6++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5],$a[$i6]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_7_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	for ($i6 = $i5 ; $i6 < sizeof($a) ; $i6++)
	for ($i7 = $i6 ; $i7 < sizeof($a) ; $i7++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5],$a[$i6],$a[$i7]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_8_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	for ($i6 = $i5 ; $i6 < sizeof($a) ; $i6++)
	for ($i7 = $i6 ; $i7 < sizeof($a) ; $i7++)
	for ($i8 = $i7 ; $i8 < sizeof($a) ; $i8++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5],$a[$i6],$a[$i7],$a[$i8]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_9_pack($a)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	for ($i6 = $i5 ; $i6 < sizeof($a) ; $i6++)
	for ($i7 = $i6 ; $i7 < sizeof($a) ; $i7++)
	for ($i8 = $i7 ; $i8 < sizeof($a) ; $i8++)
	for ($i9 = $i8 ; $i9 < sizeof($a) ; $i9++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5],$a[$i6],$a[$i7],$a[$i8],$a[$i9]));
		array_push($ret, $tar);
	}
	return vsort2($ret);
}

function get_10_pack($a, $n)
{
	$ret = array();
	for ($i1 = 0 ; $i1 < sizeof($a) ; $i1++)
	for ($i2 = $i1 ; $i2 < sizeof($a) ; $i2++)
	for ($i3 = $i2 ; $i3 < sizeof($a) ; $i3++)
	for ($i4 = $i3 ; $i4 < sizeof($a) ; $i4++)
	for ($i5 = $i4 ; $i5 < sizeof($a) ; $i5++)
	for ($i6 = $i5 ; $i6 < sizeof($a) ; $i6++)
	for ($i7 = $i6 ; $i7 < sizeof($a) ; $i7++)
	for ($i8 = $i7 ; $i8 < sizeof($a) ; $i8++)
	for ($i9 = $i8 ; $i9 < sizeof($a) ; $i9++)
	{
		$tar = vsort(array($a[$i1],$a[$i2],$a[$i3],$a[$i4],$a[$i5],$a[$i6],$a[$i7],$a[$i8],$a[$i9]));
		array_push($ret, $tar);
	}
	$ret = vsort2($ret);
	$size = $ret[sizeof($ret) -1][8];
	for ($i = 0 ; $i < $n - 9 ; $i++)
	{
		foreach ($ret as $key => $val)
		{
			array_push($val, $size);
			$ret[$key] = $val;
		}
		
	}
	return $ret;
}

function vsort($arr)
{
	$tmp = array();
	$ret = array();
	foreach ($arr as $key => $val)
	{
		$vol = 1;
		foreach ($val as $v)
			$vol *= $v;
		if (isset($tmp[$vol]))
			array_push($tmp[$vol], $val);
		else
			$tmp[$vol] = array($val);
	}
	ksort($tmp);
	foreach ($tmp as $key)
		foreach ($key as $val)
			array_push($ret, $val);
	return $ret;
}

function vsort2($arr)
{
	$tmp = array();
	$ret = array();
	foreach ($arr as $key => $val)
	{
		$vol = 0;
		foreach ($val as $v)
		{
			$tvol = 1;
			foreach ($v as $vkey => $v1)
				if ($vkey < 3)
					$tvol *= $v1;
			$vol += $tvol;
		}
		if (isset($tmp[$vol]))
			array_push($tmp[$vol], $val);
		else
			$tmp[$vol] = array($val);
	}
	ksort($tmp);
	foreach ($tmp as $key)
		foreach ($key as $val)
			array_push($ret, $val);
	return $ret;
}
?>