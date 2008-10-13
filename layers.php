<?php
/* $Id$ */
include_once 'includes/init.php';
send_no_cache_header ();

$layer_user = $login;
$u_url = '';
$updating_public = false;

$public = getValue ( 'public' );

if ( $is_admin && ! empty ( $public ) && $PUBLIC_ACCESS == 'Y' ) {
  $layer_user = '__public__';
  $u_url = '&amp;public=1';
  $updating_public = true;
}

load_user_layers ( $layer_user, 1 );

$layers_enabled = 0;
$res = dbi_execute ( 'SELECT cal_value FROM webcal_user_pref
  WHERE cal_setting = \'LAYERS_STATUS\' AND cal_login = ?',
  array ( $layer_user ) );
if ( $res ) {
  $row = dbi_fetch_row ( $res );
  $layers_enabled = ( $row[0] == 'Y' ? 1 : 0 );
  dbi_free_result ( $res );
}

$layerStr = translate ( 'Layer' );
$editLayerStr = translate ( 'Edit layer' );
$editStr = translate ( 'Edit' );
$deleteStr = translate ( 'Delete' );
$deleteLayerStr = translate ( 'Delete layer' );
$areYouSureStr = translate ( 'Are you sure you want to delete this layer?' );
$sourceStr = translate ( 'Source' );
$colorStr = translate ( 'Color' );
$duplicatesStr = translate ( 'Duplicates' );
$noStr = translate ( 'No' );
$yesStr = translate ( 'Yes' );
$disabledStr = translate ( 'Disabled' );
$enableLayersStr = translate ( 'Enable layers' );

print_header ();

ob_start ();

if ( $ALLOW_VIEW_OTHER != 'Y' )
  echo print_not_auth ();
else {
  echo '
    <h2>' . ( $updating_public
    ? translate ( $PUBLIC_ACCESS_FULLNAME ) . '&nbsp;' : '' )
   . translate ( 'Layers' ) . '&nbsp;<img src="images/help.gif" alt="'
   . translate ( 'Help' ) . '" class="help" onclick="window.open( '
   . '\'help_layers.php\', \'cal_help\', \'dependent,menubar,scrollbars,'
   . 'height=400,width=400,innerHeight=420,outerWidth=420\' );" /></h2>
    ' . display_admin_link();

  if ( $layers_enabled ) {
    echo translate( 'Layers are currently enabled.' )
     . ' (<a class="nav" '
     . 'href="layers_toggle.php?status=off' . $u_url . '">'
     . translate ( 'Disable Layers' ) . '</a>)<br />'
     . ( $is_admin && empty ( $public ) &&
      ( ! empty ( $PUBLIC_ACCESS ) && $PUBLIC_ACCESS == 'Y' ) ? '
    <blockquote>
      <a href="layers.php?public=1">'
       . str_replace( 'XXX', $PUBLIC_ACCESS_FULLNAME,
         translate( 'Click here to modify the layers settings for the XXX calendar.' ) )
       . '</a>
    </blockquote>' : '' ) . '
    <a href="edit_layer.php' . ( $updating_public ? '?public=1' : '' )
     . '">' . translate ( 'Add layer') . '</a><br />';

    $layer_count = 1;
    if ( $layers ) {
      foreach ( $layers as $layer ) {
        user_load_variables ( $layer['cal_layeruser'], 'layer' );

        echo '
    <div class="layers" style="color: ' . $layer['cal_color'] . '">
      <h4>' . $layerStr . '&nbsp;' . $layer_count . '
        (<a title="' . $editLayerStr
         . '" href="edit_layer.php?id=' . $layer['cal_layerid'] . $u_url . '">'
         . $editStr . '</a> /
        <a title="' . $deleteLayerStr
         . '" href="del_layer.php?id=' . $layer['cal_layerid'] . $u_url
         . '" onclick="return confirm( \''
         . $areYouSureStr
         . '\' );">' . $deleteStr . '</a>)</h4>
      <p><label>' . $sourceStr . ': </label>' . $layerfullname
         . '</p>
      <p><label>' . $colorStr . ': </label>'
         . $layer['cal_color'] . ')</p>
      <p><label>' . $duplicatesStr . ': </label>'
         . ( $layer['cal_dups'] == 'N'
          ? $noStr : $yesStr ) . '</p>
    </div>';

        $layer_count++;
      }
    }
  } else
    echo translate( 'Layers are currently disabled.' )
     . ' (<a class="nav" '
     . 'href="layers_toggle.php?status=on' . $u_url . '">'
     . $enableLayersStr . '</a>)<br />';
}

ob_end_flush ();

echo print_trailer ();

?>
