<?php
try {
	require_once("core/class/BaseObject.php");
} catch (Exception $e){
	echo $e->getMessage().' // ';
}
class SecurityObj extends BaseObject
{

	function __toString(){
		return $this->maarch_comment ; 
	}

}
?>
