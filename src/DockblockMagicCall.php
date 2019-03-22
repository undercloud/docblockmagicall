<?php
namespace Undercloud\Misc;

class DocblockMagicCall
{
	private function isAssocArray(array $array)
	{
	    if (!$array) return false;

	    return array_keys($array) !== range(0, count($array) - 1);
	}

	private function varExportCompress($value)
	{
	    if (is_array($value)) {
	        $toImplode = [];
	        if($this->isAssocArray($value)){
		        foreach ($value as $key => $value) {
		            $toImplode[] = var_export($key, true) . ' => ' . $this->varExportCompress($value);
		        }
		    } else {
		    	$toImplode = $value;
		    }

	        $code = '['. implode(', ', $toImplode) . ']';
	        
	        return $code;
	    }

	    return var_export($value, true);
	}

	private function valueConverter($value)
	{
		if (is_null($value)) {
			return 'null';
		}

		return $this->varExportCompress($value, true);
	}

	public function exportMagicMethod($function, array $options = [])
	{
		$compile = ['@method'];

		if (isset($options['static']) and $options['static']) {
			$compile[] = 'static';
		}

		$reflection = new ReflectionFunction($function);
		$params = $reflection->getParameters();

		if ($reflection->hasReturnType()) {
			$compile[] = $reflection->getReturnType();
		} else if(isset($options['return'])) {
			$compile[] = $options['return'];
		}

		$arguments = [];
		foreach ($params as $param){
			$inline = '';
			if ($param->hasType()) {
				$inline .= $param->getType() . ' ';
			}

			if ($param->isPassedByReference()) {
				$inline .= '&';
			}

			$inline .= '$' . $param->getName();

			if ($param->isDefaultValueAvailable()) {
				$inline .= ' = ';

				if ($param->isDefaultValueConstant()) {
					$inline .= $param->getDefaultValueConstantName();
				} else {
					$inline .= $this->valueConverter($param->getDefaultValue());
				}
			}

			$arguments[] = $inline;
		}

		$compile[] = $reflection->getShortName() . '(' . implode(', ', $arguments) . ')';

		return implode(' ', $compile);
	}
}

function myfunction(DomDocument $dom = null,array $a = [5,6,7,8],&$b = "'\"", $c = true, $d = PHP_INT_MIN, $e = 'foo', $f = 100500, $g = null, callable $j = null): bool
{

}

$doc = new DocblockMagicCall;

echo $doc->exportMagicMethod('myfunction');
