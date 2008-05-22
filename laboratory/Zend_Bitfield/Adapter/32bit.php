<?

class Zend_Bitfield_Adapter_32bit {
  private $_bitmap = array();
  private $_curbit = 1;

  function createBit($key) {
    $this->_bitmap[$key] = $this->_curbit;
    $this->_curbit = $this->_curbit * 2;
    return $this->_bitmap[$key];
  }

  function getBits() {
    return $this->_bitmap;
  }

  function loadBits($bits) {
    foreach($bits as $key => $value) {
      $this->_bitmap[$key] = $value;
      if($value > $this->_curbit) {
        $this->_curbit = $value;
      }
    }
    $this->_curbit = $this->_curbit * 2;
  }

  function compareBit($key, $value) {
    if(isset($this->_bitmap[$key])) {
      if($this->_bitmap[$key] & $value) {
        return true;
      }
    }
    return false;
  }


}

?>