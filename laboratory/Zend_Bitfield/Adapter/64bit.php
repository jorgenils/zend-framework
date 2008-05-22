<?

class Zend_Bitfield_Adapter_64bit {
  private $_bitmap = array();
  private $_curbit0 = 1;
  private $_curbit1 = 1;

  function createbit($key) {
    $this->_bitmap[$key][0] = $this->_curbit0;
    $this->_bitmap[$key][1] = $this->_curbit1;
    if($this->_curbit0 < $this->_curbit1) {
      $this->_curbit0 = $this->_curbit0 * 2;
    } else {
      $this->_curbit1 = $this->_curbit1 * 2;
    }
    return $this->_curbit0 . '|'. $this->_curbit1;
  }

  function getbits() {
    $barray = array();
    foreach($_bitmap as $key => $value) {
      $barry[$key] = $value[0] . '|' . $value[1];
    }
    return $barray;
  }

  function loadbits($input) {
    foreach($input as $key => $value) {
      $bits = explode('|', $value);
      $this->_bitmap[$key][0] = $bits[0];
      $this->_bitmap[$key][1] = $bits[1];
      if($bits[0] > $this->_curbit0) {
        $this->_curbit0 = $bits[0];
      }
      if($bits[1] > $this->_curbit1) {
        $this->_curbit1 = $bits[1];
      }
    }
  }

  function comparebit($key, $value) {
    $bits = explode('|', $value);
    if(isset($this->_bitmap[$key])) {
      if(($this->_bitmap[$key][0] & $bits[0]) AND ($this->_bitmap[$key][1] & $bits[1])) {
        return true;
      }
    }
    return false;
  }


}

?>