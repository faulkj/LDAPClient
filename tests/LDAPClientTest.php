<?php

   use PHPUnit\Framework\TestCase,
       FaulkJ\LDAPClient;

   class LDAPClientTest extends TestCase {

      private $client;

      protected function setUp(): void {
         $this->client = new LDAPClient(
            "ldap.forumsys.com",
            "cn=read-only-admin,dc=example,dc=com",
            "password",
            "dc=example,dc=com",
            [
               "id" => "uid"
            ]
         );
      }

      public function testIsThereAnySyntaxError() {
         $this->assertTrue(is_object($this->client));
      }

      public function testLogin() {
         $user = $this->client->login("riemann", "password", ["fullname" => "cn", "mail"]);
         //var_dump($user);

         $this->assertTrue(isset($user->fullname) && $user->fullname == "Bernhard Riemann");
      }

      public function testSearch() {
         $res = $this->client->search("(ou=chemist*)", ["cn", "members" => "uniquemember"], null, true);
         //var_dump($res);

         $this->assertTrue(isset($res->members) && in_array("curie", $res->members));
      }

      public function testGetJSON() {
         $res = $this->client->getJSON("(ou=chemist*)", ["name" => "cn"]);
         //var_dump($res);

         $this->assertTrue($res && $res = '{"dn":"ou=chemists,dc=example,dc=com","name":"Chemists"}');
      }

      public function testMember() {
         $res = $this->client->member("mathematicians", "gauss", [
            "id"     => "ou",
            "member" => "uniquemember"
         ]);
         //var_dump($res);

         $this->assertTrue($res);
      }

   }

?>