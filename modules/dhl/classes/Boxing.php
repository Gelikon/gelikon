<?php

class Boxing {

	public $outer_boxes;
	public $inner_boxes;

	public function Boxing() {

		$this -> outer_boxes = array();
		$this -> inner_boxes = array();

		return;

	}

	public function add_outer_box($l,$w,$h,$we) {

		if ($l > 0 && $w > 0 && $h > 0) {

			$this -> outer_boxes[] = array(

				"dimensions" => $this -> sort_dimensions($l,$w,$h),
				"weight" => $we,
				"used_weight" => 0,
				"packed" => false

			);

			return true;
		}
		return false;

	}

	public function add_inner_box($l,$w,$h,$we) {

		if ($l > 0 && $w > 0 && $h > 0) {

			$this -> inner_boxes[] = array(

				"dimensions" => $this -> sort_dimensions($l,$w,$h),
				"weight" => $we,
				"packed" => false
			);
			return true;
		}
		return false;
	}


	public function fits() {

		/* first we do a simple volume check, this can save a lot of calculations */

		if (!$this -> fits_volume()) {

			return false;

		}

		/* get next inner box */

		while (true) {

			$inner_box_id = $this->next_inner_box();
			if ($inner_box_id > 0)
				$last_inner = $inner_box_id;
			//print "id = $inner_box_id<br />\n";
			if ($inner_box_id === false) {

				break;

			}

			$found_fitting_box = false;

			foreach ($this->outer_boxes as $outer_box_id => $outer_box) {

				if (!$outer_box["packed"] && $this->fits_inside($inner_box_id, $outer_box_id))
				{
					/* matches! */
					$this -> inner_boxes[$inner_box_id]["packed"] = true;
					$this -> outer_boxes[$outer_box_id]["packed"] = true;
					if (!isset($this -> outer_boxes[$outer_box_id]["parent"]))
					{
						$this -> outer_boxes[$outer_box_id]["used_weight"] = $this -> inner_boxes[$inner_box_id]["weight"];
					}
					else
					{
						$this -> outer_boxes[$this -> outer_boxes[$outer_box_id]["parent"]]["used_weight"] += $this -> inner_boxes[$inner_box_id]["weight"];
					}
					$this -> find_subboxes($inner_box_id, $outer_box_id);

					$found_fitting_box = true;

					break;

				}

			}

			if (!$found_fitting_box) {

				return false;

			}

		}
		/* we ran out of inner boxes but have outer boxes left */
		return true;

	}

	public function fits_volume() {

		$inner_volume = 0;
		$outer_volume = 0;
		$inner_weight = 0;
		$outer_weight = 0;
		foreach ($this -> inner_boxes as $inner)
		{
			$inner_volume += ($inner["dimensions"][0] * $inner["dimensions"][1] * $inner["dimensions"][2]);
			$inner_weight += $inner["weight"];
		}

		foreach ($this -> outer_boxes as $outer)
		{

			if (!isset($outer["parent"]))
			{
				$outer_volume += ($outer["dimensions"][0] * $outer["dimensions"][1] * $outer["dimensions"][2]);
				$outer_weight += $outer["weight"];
			}
		}

		if ($inner_volume > $outer_volume || $inner_weight > $outer_weight)
			return false;
		return true;
	}

	private function find_subboxes($inner_box_id, $outer_box_id)
	{
		$inner_dimensions = $this->inner_boxes[$inner_box_id]["dimensions"];
		$outer_dimensions = $this->outer_boxes[$outer_box_id]["dimensions"];
		sort($outer_dimensions);
		$pairs = array();
		foreach ($inner_dimensions as $inner_id => $inner_value)
		{
			foreach ($outer_dimensions as $outer_id => $outer_value)
			{
				if ($inner_value <= $outer_value)
				{

					$unset = $outer_id;
					$pairs[] = array(
						"inner" => $inner_value,
						"outer" => $outer_value,
						"diff" => $outer_value-$inner_value
					);
					break;
				}
			}
			unset($outer_dimensions[$unset]);
		}
		do
		{
			$pairs = $this-> _diffsort($pairs);
			if (isset($this->outer_boxes[$outer_box_id]["parent"]))
				$parent_id = $this->outer_boxes[$outer_box_id]["parent"];
			else
				$parent_id = $outer_box_id;
			if ($this -> add_outer_box($pairs[0]["diff"], $pairs[1]["outer"], $pairs[2]["outer"],$this->outer_boxes[$parent_id]["weight"] - $this->outer_boxes[$parent_id]["used_weight"]))
			{
				$this->outer_boxes[sizeof($this->outer_boxes)-1]["parent"] = $parent_id;
			}
			$pairs[0]["diff"] = 0;
			$pairs[0]["outer"] = $pairs[0]["inner"];
		} while($pairs[0]["diff"] > 0 || $pairs[1]["diff"] > 0 || $pairs[2]["diff"] > 0);
		return true;
	}

	private function fits_inside($inner_box_id, $outer_box_id)
	{
		$outer_box_parent_id = isset($this->outer_boxes[$outer_box_id]["parent"])?$this->outer_boxes[$outer_box_id]["parent"]:$outer_box_id;
		if (
			$this->inner_boxes[$inner_box_id]["dimensions"][0] <= $this->outer_boxes[$outer_box_id]["dimensions"][0] &&
			$this->inner_boxes[$inner_box_id]["dimensions"][1] <= $this->outer_boxes[$outer_box_id]["dimensions"][1] &&
			$this->inner_boxes[$inner_box_id]["dimensions"][2] <= $this->outer_boxes[$outer_box_id]["dimensions"][2] &&
			$this->inner_boxes[$inner_box_id]["weight"] <= $this->outer_boxes[$outer_box_parent_id]["weight"] - $this->outer_boxes[$outer_box_parent_id]["used_weight"]
			)
				/* fits */
				return true;

		else
			/* fits not */
			return false;
	}

	private function sort_dimensions($l,$w,$h)
	{
		$dimensions = array($l,$w,$h);
		rsort($dimensions);
		return $dimensions;
	}

	private function next_outer_box()
	{
		$biggest_size = 0;
		$biggest_id = false;
		foreach ($this -> outer_boxes as $id => $box)
		{
			if (!$box["packed"] && $box["dimensions"][0] > $biggest_size)
			{
				$biggest_size = $box["dimensions"][0];
				$biggest_id = $id;
			}
		}
		return $id;
	}

	private function next_inner_box()
	{
		$biggest_size = 0;
		$biggest_id = false;
		foreach ($this -> inner_boxes as $id => $box)
		{
			if (!$box["packed"] && $box["dimensions"][0] > $biggest_size)
			{
				$biggest_size = $box["dimensions"][0];
				$biggest_id = $id;
			}
		}
		return $biggest_id;
	}

	function _diffsort($array)
	{
		/* quick and dirty hack since _sksort() does strange things */
		$tmp_array = array();
		foreach ($array as $item)
		{
			$tmp_array[strval($item["diff"])][] = $item;
		}
		krsort($tmp_array, SORT_NUMERIC);
		$array = array();
		foreach ($tmp_array as $a)
		{
			foreach ($a as $item)
			{
				$array[] = $item;
			}
		}
		return $array;
	}
}
