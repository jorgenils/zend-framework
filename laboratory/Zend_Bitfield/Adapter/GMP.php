<?

class Zend_Bitfield_Adapter_GMP {
  private $_bitmap = array();
  private $_curbit = 1;

  function createbit($key) {
    $this->_bitmap[$key] = $this->_curbit;
    $this->_curbit = gmp_mul($this->_curbit,2);
    return $this->_bitmap[$key];
  }

  function getbits() {
    return $this->_bitmap;
  }

  function loadbits($bits) {
    foreach($bits as $key => $value) {
      $this->_bitmap[$key] = $value;
      if($value > $this->_curbit) {
        $this->_curbit = $value;
      }
    }
    $this->_curbit = gmp_mul($this->_curbit,2);
  }

  function comparebit($key, $value) {
    if(isset($this->_bitmap[$key1])) {
      if(gmp_and($this->_bitmap[$key1], $value)) {
        return true;
      }
    }
    return false;
  }


}

?>