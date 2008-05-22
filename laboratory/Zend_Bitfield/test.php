<?php
  require_once('Bitfield.php');
  Zend_Bitfield::initBitfield();
  $security = array();
  $security['admin'] = Zend_Bitfield::createBit('admin');
  $security['moderator'] = Zend_Bitfield::createBit('moderator');
  $security['user'] = Zend_Bitfield::createBit('user');
  $security['banned'] = Zend_Bitfield::createBit('banned');

  Zend_Bitfield::initBitfield('32bit','testgroup');
  $test = array();
  $test['group1'] = Zend_Bitfield::createBit('group1', 'testgroup');
  $test['group2'] = Zend_Bitfield::createBit('group2', 'testgroup');


  Zend_Bitfield::initBitfield('32bit','loadedin');
  Zend_Bitfield::loadBits($security, 'loadedin');
  $loadedin = Zend_Bitfield::getBits('loadedin');
  $loadedin['addedon'] = Zend_Bitfield::createBit('addedon');

  print_r($security);
  print_r($test);
  print_r($loadedin);
  echo 'Is the number 8 a banned bit '.Zend_Bitfield::checkBit('banned', 8)."\n";
?>