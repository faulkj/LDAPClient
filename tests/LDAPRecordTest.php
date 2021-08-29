<?php

   use PHPUnit\Framework\TestCase,
       FaulkJ\LDAPClient\LDAPRecord;

   class LDAPRecordTest extends TestCase {

      private $res;

      protected function setUp(): void {
         $this->res = new LDAPRecord([
            "id" => "testing",
            "dn" => "cn=testing,dc=test,dc=com"
         ]);
      }

      public function testIsThereAnySyntaxError() {
         $this->assertTrue(is_object($this->res));
      }

      public function testValid() {
         $this->assertTrue(isset($this->res->id) && $this->res->id == "testing");
      }

   }

?>