<?php namespace FaulkJ\LDAPClient;
   /*
    * LDAP Record Class for LDAPClient v1.5.4
    *
    * Kopimi 2023 Joshua Faulkenberry
    * Unlicensed under The Unlicense
    * http://unlicense.org/
    */

   use \JsonSerializable;

   class LDAPRecord implements JsonSerializable {

      private $_data = [];

      public function __construct(array $r) {
         ksort($r);
         foreach($r as $k => $v) $this->_data[$k] = $v;
      }

      public function __get($prop) {
         return array_key_exists($prop, $this->_data) ? $this->_data[$prop] : null;
      }

      public function __isset($prop) {
         return array_key_exists($prop, $this->_data);
      }

      public function __set($prop, $value) {
         trigger_error("Can't modify a record");
      }

      public function jsonSerialize(): mixed {
         return $this->_data;
      }

   }

?>