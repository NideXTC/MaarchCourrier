<?php
if (file_exists('../../../core/init.php')) {
    include_once '../../../core/init.php';
}
if (!isset($_SESSION['config']['corepath'])) {
    header('location: ../../../');
}
require_once('core/class/class_functions.php');
require_once('core/class/class_core_tools.php');
$core = new core_tools();
$core->load_lang();
?>
<div id="about" title="<?php echo _MAARCH_CREDITS;?>" class="panel">
    <p id="logo" align="center">
    <img src="<?php
                    functions::xecho($_SESSION['config']['businessappurl'])
                ?>static.php?filename=default_maarch.gif" alt="Maarch" />
    </p>
    <p>
        Maarch is a French software editor specialised in optimising document flows.
        Maarch solutions are born from the need to provide our customers with easy and fast to build proofs of concept. The solution has quickly reach a fully operational level. Based on web technologies, it is now able to manage a document form its creation or digitization until its end-of-life.
    </p>
    <p>
        According to their beliefs that DMS and archiving solutions require open and standardised solutions to make sure of the continuity of data, Maarch solutions are all released under the terms of the open source license GNU GPL.
    </p>
    <p>
        Maarch is the main developer of Maarch Solutions and finance the core team.</h2>
    </p>
</div>
