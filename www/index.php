<?php 
require __DIR__ .'/config.php'; 


$context->title = $langs->trans('Welcome');
$context->desc = $langs->trans('WelcomeDesc');

$context->menu_active[] = 'invoices';






/*
 * View 
 */
include __DIR__ .'/tpl/header.tpl.php';
?>

<section id="section-invoice">
      <div class="container">
        <div class="row">
        
          <div class="col-lg-3 col-md-4 ">
			<?php  include __DIR__ .'/tpl/menu.left.tpl.php'; ?>
          </div>
          <div class="col-lg-9 col-md-8">
<?php 

$sql = 'SELECT rowid ';
$sql.= ' FROM `'.MAIN_DB_PREFIX.'facture` f';
$sql.= ' WHERE fk_soc = '. intval($context->user->societe_id);
$sql.= ' AND fk_statut > 1';
$sql.= ' ORDER BY f.datef DESC';

$tableItems = $context->dbTool->executeS($sql);

if(!empty($tableItems))
{
    
    print '<table>';
    
    print '<thead>';
    
    print '<tr>';
    print ' <th>'.$langs->trans('Ref').'</th>';
    print ' <th>'.$langs->trans('Date').'</th>';
    print ' <th></th>';
    print '</tr>';
    
    print '<thead>';
    
    print '<tbody>';
    foreach ($tableItems as $item)
    {
        $facture = new Facture($db);
        $facture->fetch($item->rowid);
        
        print '<tr>';
        print ' <td>'.$facture->ref.'</td>';
        print ' <td>'.$facture->datef.'</td>';
        print ' <td></td>';
        print '</tr>';
        
    }
    print '</tbody>';
    
    print '</table>';
}
else {
    print '<div class="info clearboth" >';
    print  $langs->trans('Nothing');
    print '</div>';
}

?>
          </div>
          
    </div>
  </div>
</section>


<?php 
include __DIR__ .'/tpl/footer.tpl.php';