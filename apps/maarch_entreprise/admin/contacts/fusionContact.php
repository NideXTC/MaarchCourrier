<?php

require_once('core/class/class_core_tools.php');
$Core_Tools = new core_tools;
$Core_Tools->load_lang();
$db = new Database();
$return = '';
$status = 0;


$master_contact_id = $_POST['master_contact_id'];
$master_address_id = $_POST['master_address_id'];
$contacts_id = explode(',', $_POST['slave_contact_id']);
$del_address_id = explode(',', $_POST['del_address_id']);

foreach ($contacts_id as $key => $value) {

    //mise à jour des contacts courriers de type arrivé
    $query = "UPDATE mlb_coll_ext SET dest_contact_id = ? WHERE dest_contact_id = ?";
    $arrayPDO = array($master_contact_id,$value);
    $db->query($query, $arrayPDO);

    //mise à jour des contacts courriers de type départ
    $query = "UPDATE mlb_coll_ext SET exp_contact_id = ? WHERE exp_contact_id = ?";
    $arrayPDO = array($master_contact_id,$value);
    $db->query($query, $arrayPDO);

    //mise à jour des pièces jointes
    $query = "UPDATE res_attachments SET dest_contact_id = ? WHERE dest_contact_id = ?";
    $arrayPDO = array($master_contact_id,$value);
    $db->query($query, $arrayPDO);

    //mise à jour des pièces jointes versionnée
    $query = "UPDATE res_version_attachments SET dest_contact_id = ? WHERE dest_contact_id = ?";
    $arrayPDO = array($master_contact_id,$value);
    $db->query($query, $arrayPDO);

    //deplace adresse au master
    $query = "UPDATE contact_addresses SET contact_id = ? WHERE contact_id = ?";
    $arrayPDO = array($master_contact_id,$value);
    $db->query($query, $arrayPDO);

    //supression du contact substitué
    $query = "DELETE FROM contacts_v2 WHERE contact_id = ?";
    $arrayPDO = array($value);
    $db->query($query, $arrayPDO);
}

foreach ($del_address_id as $key => $value) {
    if (!empty($value)) {
        //mise à jour des adresses courriers de type arrivé
        $query = "UPDATE mlb_coll_ext SET address_id = ? WHERE address_id = ?";
        $arrayPDO = array($master_address_id,$value);
        $db->query($query, $arrayPDO);

        //mise à jour des pièces jointes
        $query = "UPDATE res_attachments SET dest_address_id = ? WHERE dest_address_id = ?";
        $arrayPDO = array($master_address_id,$value);
        $db->query($query, $arrayPDO);

        //mise à jour des pièces jointes versionnée
        $query = "UPDATE res_version_attachments SET dest_address_id = ? WHERE dest_address_id = ?";
        $arrayPDO = array($master_address_id,$value);
        $db->query($query, $arrayPDO);

        //supression de l'adresse
        $query = "DELETE FROM contact_addresses WHERE id = ?";
        $arrayPDO = array($value);
        $db->query($query, $arrayPDO);
    }
    
}
$return = 'OK';

echo "{status : " . $status . ", toShow : '" . addslashes($return) . "'}";
exit ();
