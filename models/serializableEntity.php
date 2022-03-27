<?php

// Possible basic type
//  a string. prefixed by s_
//  a number. prefixed by i_ or d_
//  an object. prefixed by o_
//  an array. prefixed by a_
//  a boolean. prefixed by b_
//  null
class SerializableEntity
{
	protected $m_data = null;

	public function __construct($arr = array())
	{
		$this->m_data = $arr;
	}

	public function toArray(): array
	{
		return $this->m_data;
	}

	public function fromArray(array $data): void
	{
		// Got all array to have default value
		foreach ($this->m_data as $key => $value)
		{
			if (array_key_exists($key, $data))
			{
				$this->m_data[$key] = $data[$key];
			}
		}
	}

	public function fromStringArray(array $data): void
	{
		$newarray = array();
		foreach ($data as $key => $val)
		{
			if (!array_key_exists ($key, $this->m_data)) continue;
			$type = gettype($this->getValue($key));
			switch ($type)
			{
				case 'boolean':
					if ($val == 'true' || $val == 'checked' || $val == '1' || $val == 'on')
					{
						$newarray[$key] = true;
					}
					else
					{
						$newarray[$key] = false;
					}
					break;

				case 'integer':
					$newarray[$key] = intval($val);
					break;

				case 'double':
					$newarray[$key] = doubleval($val);
					break;

				case 'string':
					$newarray[$key] = $val;
					break;

				case 'array':
					// Multiple
					if (is_array($val))
					{
						$newarray[$key] = $val;
						if (count($val) == 1 && empty($val[0])) $newarray[$key] = array();
					}
					// As string input
					else
					{
						$newarray[$key] = explode(',', $val);
					}
					array_walk($newarray[$key], function (&$x) { $x = trim($x); });
					break;

				case 'object':
					$newarray[$key] = json_decode($val);
					break;

				case 'resource';
					break; // not managed

				case 'NULL':
					$newarray[$key] = $val;
					break;

				default:
					break;
			}
		}
		$this->fromArray($newarray);
	}

	public function getValue(string $name)
	{
		return $this->m_data[$name];
	}

	public function setValue(string $name, $value): SerializableEntity
	{
		$this->m_data[$name] = $value;
		return $this;
	}
}
