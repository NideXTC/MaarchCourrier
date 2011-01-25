<?php
require_once('modules/entities/class/EntityControler.php');
if($_SESSION['service_tag'] == 'user_init')
{
    $_SESSION['m_admin']['nbentities'] = EntityControler::getEntitiesCount();

    $tmp_array = EntityControler::getUsersEntities($_SESSION['m_admin']['users']['user_id']);
    for($i=0; $i<count($tmp_array);$i++)
    {
        $ent = EntityControler::get($tmp_array[$i]['ENTITY_ID']);
        $tmp_array[$i]['LABEL'] = $ent->__get('entity_label');
        $tmp_array[$i]['SHORT_LABEL'] = $ent->__get('short_label');
    }
    $_SESSION['m_admin']['entity']['entities'] = $tmp_array;
    unset($tmp_array);
}
elseif($_SESSION['service_tag'] == 'formuser')
{
?>
<script type="text/javascript" src="<?php  echo $_SESSION['config']['businessappurl'];?>static.php?module=entities&amp;filename=users_entities_management.js"></script>
<div id="user_entities"></div>
<script type="text/javascript">updateContent('<?php echo $_SESSION['config']['businessappurl'];?>index.php?display=true&page=users_entities_form&module=entities', 'user_entities');</script>

<?php

}
elseif($_SESSION['service_tag'] == 'users_list_init')
{
    $_SESSION['m_admin']['load_entities'] = true;

}
elseif($_SESSION['service_tag'] == 'user_check')
{
    $primary_set = false;
    if(isset($_SESSION['m_admin']['entity']['entities']) && !empty($_SESSION['m_admin']['entity']['entities'])   )
    {
        for($i=0; $i < count($_SESSION['m_admin']['entity']['entities']); $i++)
        {
            if($_SESSION['m_admin']['entity']['entities'][$i]['PRIMARY'] == 'Y')
            {
                $primary_set = true;
                break;
            }
        }
    }
    if($primary_set == false)
    {
        $_SESSION['error'] .= _NO_PRIMARY_ENTITY;
    }
}
elseif($_SESSION['service_tag'] == 'user_add' || $_SESSION['service_tag'] == 'user_up')
{
    EntityControler::cleanUsersentities($_SESSION['m_admin']['users']['user_id'], 'user_id');
    EntityControler::loadDbUsersentities($_SESSION['m_admin']['users']['user_id'], $_SESSION['m_admin']['entity']['entities']);
}
?>
