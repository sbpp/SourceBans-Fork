<?php
require_once 'init.php';
require_once READERS_DIR . 'admins.php';
require_once READERS_DIR . 'bans.php';
require_once READERS_DIR . 'servers.php';

$config   = Env::get('config');
$phrases  = Env::get('phrases');
$userbank = Env::get('userbank');
$page     = new Page(ucwords($phrases['ban_list']));

try
{
  $admins_reader      = new AdminsReader();
  $bans_reader        = new BansReader();
  $servers_reader     = new ServersReader();
  
  $limit              = $config['banlist.bansperpage'];
  $bans_reader->limit = $limit;
  
  if(isset($_GET['hideinactive']))
    $bans_reader->hideinactive = true;
  if(isset($_GET['page'])  && is_numeric($_GET['page']) && $_GET['page'] > 1)
    $bans_reader->page   = $_GET['page'];
  if(isset($_GET['search']))
    $bans_reader->search = $_GET['search'];
  if(isset($_GET['sort'])  && is_string($_GET['sort']))
    $bans_reader->sort   = $_GET['sort'];
  if(isset($_GET['order']) && is_string($_GET['order']))
    $bans_reader->order  = strtoupper($_GET['order']);
  if(isset($_GET['type']))
    $bans_reader->type   = $_GET['type'];
  
  $admins             = $admins_reader->executeCached(ONE_MINUTE  * 5);
  $bans               = $bans_reader->executeCached(ONE_MINUTE    * 5);
  $servers            = $servers_reader->executeCached(ONE_MINUTE * 5);
  
  $bans_start         = ($bans_reader->page - 1) * $limit;
  $bans_end           = $bans_start              + $limit;
  $pages              = ceil($bans['count']      / $limit);
  if($bans_end > $bans['count'])
    $bans_end = $bans['count'];
  
  $page->assign('permission_add_bans',         $userbank->HasAccess(array('OWNER', 'ADD_BANS')));
  $page->assign('permission_bans',             $userbank->HasAccess(array('OWNER', 'DELETE_BANS', 'EDIT_ALL_BANS', 'EDIT_GROUP_BANS', 'EDIT_OWN_BANS', 'UNBAN_ALL_BANS', 'UNBAN_GROUP_BANS', 'UNBAN_OWN_BANS')));
  $page->assign('permission_delete_bans',      $userbank->HasAccess(array('OWNER', 'DELETE_BANS')));
  $page->assign('permission_edit_all_bans',    $userbank->HasAccess(array('OWNER', 'EDIT_ALL_BANS')));
  $page->assign('permission_edit_group_bans',  $userbank->HasAccess(array('OWNER', 'EDIT_GROUP_BANS')));
  $page->assign('permission_edit_own_bans',    $userbank->HasAccess(array('OWNER', 'EDIT_OWN_BANS')));
  $page->assign('permission_export_bans',      $userbank->HasAccess(array('OWNER')) || $config['config.exportpublic']);
  $page->assign('permission_list_admins',      $userbank->HasAccess(array('OWNER', 'LIST_ADMINS')));
  $page->assign('permission_unban_all_bans',   $userbank->HasAccess(array('OWNER', 'UNBAN_ALL_BANS')));
  $page->assign('permission_unban_group_bans', $userbank->HasAccess(array('OWNER', 'UNBAN_GROUP_BANS')));
  $page->assign('permission_unban_own_bans',   $userbank->HasAccess(array('OWNER', 'UNBAN_OWN_BANS')));
  $page->assign('permission_edit_comments',    $userbank->HasAccess(array('OWNER')));
  $page->assign('permission_list_comments',    $userbank->is_admin());
  $page->assign('hide_adminname',              $config['banlist.hideadminname']);
  $page->assign('admins',                      $admins);
  $page->assign('bans',                        $bans['list']);
  $page->assign('servers',                     $servers);
  $page->assign('end',                         $bans_end);
  $page->assign('order',                       strtolower($bans_reader->order));
  $page->assign('sort',                        $bans_reader->sort);
  $page->assign('start',                       $bans_start);
  $page->assign('total',                       $bans['count']);
  $page->assign('total_pages',                 $pages);
  $page->display('page_banlist');
}
catch(Exception $e)
{
  $page->assign('error', $e->getMessage());
  $page->display('page_error');
}
?>