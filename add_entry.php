<?php
/* $Id$ */
include_once 'includes/init.php';

$error = '';
// Only proceed if id was passed
if ( $id > 0 ) {
  // double check to make sure user doesn't already have the event
  $is_my_event = false;
  $res = dbi_execute ( 'SELECT cal_id FROM webcal_entry_user
    WHERE cal_login = ? AND cal_id = ?', array ( $login, $id ) );
  if ( $res ) {
    $row = dbi_fetch_row ( $res );
    if ( $row[0] == $id ) {
      $is_my_event = true;
      echo ucfirst ( translate ( 'event' ) ) . " # $id"
       . translate ( 'is already on your calendar.' );
      exit;
    }
    dbi_free_result ( $res );
  }
  // Now lets make sure the user is allowed to add the event (not private)
  $res = dbi_execute ( 'SELECT cal_access FROM webcal_entry WHERE cal_id = ?',
    array ( $id ) );
  if ( ! $res ) {
    echo translate ( 'Invalid entry id' ) . ": $id";
    exit;
  }
  $mayNotAddStr =
  translate ( 'This is a XXX event and may not be added to your calendar.' );
  $row = dbi_fetch_row ( $res );

  if ( ! $is_my_event ) {
    if ( $row[0] == 'R' ) {
      $is_private = true;
      echo str_replace ( 'XXX', $translations['private'], $mayNotAddStr );
      exit;
    } else
    if ( $row[0] == 'C' && ! $is_assistant && ! $is_nonuser_admin ) {
      // assistants are allowed to see confidential stuff
      $is_private = true;
      echo str_replace ( 'XXX', $translations['confidential'], $mayNotAddStr );
      exit;
    }
  } else
    $is_private = false;
  // add the event
  if ( $readonly == 'N' && ! $is_my_event && ! $is_private ) {
    if ( ! dbi_execute ( 'INSERT INTO webcal_entry_user ( cal_id, cal_login,
      cal_status ) VALUES ( ?, ?, ? )', array ( $id, $login, 'A' ) ) )
      $error = translate ( 'Error adding event' ) . ': ' . dbi_error ();
  }
}

send_to_preferred_view ();
exit;

?>
